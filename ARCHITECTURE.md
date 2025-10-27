# Chat-as-a-Service Architecture

## Overview
This document describes the architecture for transforming Lara Chat into a multi-tenant Chat-as-a-Service platform similar to Crisp.chat or RightChat.

## Core Concepts

### 1. Multi-Tenancy Model
Each customer (tenant) gets their own isolated workspace called a **Project**:
- Projects are completely isolated from each other
- Each project has its own users, chat groups, and messages
- Data isolation is enforced at the database and application level

### 2. User Types

#### Admin Users (table: `users`)
- Platform administrators and project owners
- Can create and manage projects
- Can generate API keys
- Can access admin dashboard

#### End Users (table: `end_users`)
- The actual chat participants (customers of your clients)
- Created via API by your clients
- Belong to a specific project
- Can be anonymous or registered

### 3. Authentication Methods

#### For Admin Users
- Laravel Sanctum with email/password
- Access to admin dashboard

#### For API Clients
- API Key authentication
- Each project has one or more API keys
- API keys can have different scopes (read, write, admin)

#### For End Users
- JWT tokens issued via API
- Can be anonymous (guest) or authenticated
- Session-based for widget users

## Database Schema

### New Tables

#### `projects`
```sql
- id (PK)
- uuid (unique)
- name
- owner_id (FK to users)
- plan (free, starter, pro, enterprise)
- status (active, suspended, cancelled)
- settings (json - theme, widget config, etc.)
- created_at
- updated_at
```

#### `api_keys`
```sql
- id (PK)
- project_id (FK to projects)
- name (descriptive name)
- key (hashed API key)
- prefix (visible prefix like "pk_live_")
- scopes (json - permissions array)
- last_used_at
- expires_at
- created_at
- updated_at
```

#### `end_users`
```sql
- id (PK)
- project_id (FK to projects)
- uuid (unique per project)
- name
- email (nullable)
- avatar (nullable)
- user_identifier (client's user ID)
- metadata (json - custom fields)
- is_anonymous (boolean)
- last_seen_at
- created_at
- updated_at
```

### Modified Tables

All chat-related tables need `project_id`:
- `chat_groups` - add `project_id`
- `chat_messages` - add `project_id`
- `chat_group_members` - add `project_id`

## API Structure

### Admin API (`/api/admin/*`)
For platform administrators:
```
POST   /api/admin/projects                    - Create project
GET    /api/admin/projects                    - List projects
GET    /api/admin/projects/{uuid}             - Get project details
PATCH  /api/admin/projects/{uuid}             - Update project
DELETE /api/admin/projects/{uuid}             - Delete project

POST   /api/admin/projects/{uuid}/api-keys    - Generate API key
GET    /api/admin/projects/{uuid}/api-keys    - List API keys
DELETE /api/admin/api-keys/{id}               - Revoke API key

GET    /api/admin/projects/{uuid}/analytics   - Get usage stats
```

### Client API (`/api/v1/*`)
For your customers (requires API key):
```
# End User Management
POST   /api/v1/users                          - Create/identify user
GET    /api/v1/users/{uuid}                   - Get user
PATCH  /api/v1/users/{uuid}                   - Update user
DELETE /api/v1/users/{uuid}                   - Delete user

# Conversations (Chat Groups)
POST   /api/v1/conversations                  - Start conversation
GET    /api/v1/conversations                  - List conversations
GET    /api/v1/conversations/{uuid}           - Get conversation
PATCH  /api/v1/conversations/{uuid}           - Update conversation
DELETE /api/v1/conversations/{uuid}           - Close conversation

# Messages
POST   /api/v1/conversations/{uuid}/messages  - Send message
GET    /api/v1/conversations/{uuid}/messages  - Get messages
POST   /api/v1/conversations/{uuid}/read      - Mark as read
POST   /api/v1/conversations/{uuid}/typing    - Send typing indicator

# Webhooks
POST   /api/v1/webhooks                       - Register webhook
GET    /api/v1/webhooks                       - List webhooks
DELETE /api/v1/webhooks/{id}                  - Delete webhook
```

### Widget API (`/widget/v1/*`)
Public endpoints for embedded widget:
```
POST   /widget/v1/init                        - Initialize widget session
POST   /widget/v1/messages                    - Send message from widget
GET    /widget/v1/messages                    - Get messages for widget
POST   /widget/v1/events                      - Track events (typing, etc.)
```

## WebSocket Channels

### Private Channels (require authentication)
```
project.{project_uuid}.conversation.{conversation_uuid}
  - Events: MessageSent, MessageRead, UserTyping

project.{project_uuid}.user.{user_uuid}
  - Events: NewConversation, ConversationUpdated
```

### Presence Channels
```
project.{project_uuid}.online-users
  - Track online end users
```

## Widget Integration

### Embedded Widget
Clients add a script to their website:

```html
<script>
  window.LaraChatConfig = {
    apiKey: 'pk_live_xxxxxxxxxxxxxxxx',
    user: {
      identifier: 'user-123',
      name: 'John Doe',
      email: 'john@example.com'
    },
    settings: {
      position: 'bottom-right',
      theme: 'light',
      locale: 'en'
    }
  };
</script>
<script src="https://your-domain.com/widget/loader.js" async></script>
```

### Widget Features
- Auto-identify users
- Persist conversation history
- Real-time message updates
- Typing indicators
- Read receipts
- File attachments (future)
- Emoji support
- Mobile responsive

## Middleware Stack

### For Admin API
```
api
├── auth:sanctum
├── admin (check if user is admin)
└── throttle:api
```

### For Client API
```
api
├── api.key (verify API key)
├── tenant.scope (inject project context)
├── throttle:api
└── log.api.usage (for billing)
```

### For Widget API
```
api
├── widget.auth (verify widget session)
├── tenant.scope.widget
├── throttle:widget
└── cors
```

## Security Considerations

### Data Isolation
- Global scopes on all tenant-aware models
- Middleware ensures `project_id` is always filtered
- Database indexes on `project_id` for performance

### API Key Security
- Keys are hashed before storage (like passwords)
- Prefix system for easy identification
- Scope-based permissions
- Rate limiting per project
- Expiration dates

### CORS Configuration
- Whitelist client domains per project
- Dynamic CORS based on project settings
- Secure WebSocket connections

## Billing & Usage Tracking

### Metrics to Track
- Number of end users (monthly active)
- Number of conversations
- Number of messages sent
- Storage used (for file attachments)
- API requests count
- WebSocket connections (concurrent)

### Plans
```
Free:
  - 100 end users
  - 1,000 messages/month
  - 1 project
  - Email support

Starter:
  - 1,000 end users
  - 10,000 messages/month
  - 3 projects
  - Email support

Pro:
  - 10,000 end users
  - 100,000 messages/month
  - 10 projects
  - Priority support
  - Custom branding

Enterprise:
  - Unlimited
  - Custom contracts
  - Dedicated support
  - SLA guarantees
```

## Event System & Webhooks

### Webhook Events
Clients can subscribe to events:
```
- conversation.created
- conversation.updated
- conversation.closed
- message.sent
- message.read
- user.created
- user.updated
```

### Webhook Payload Example
```json
{
  "event": "message.sent",
  "timestamp": "2025-10-28T12:34:56Z",
  "data": {
    "conversation_id": "uuid",
    "message_id": "uuid",
    "sender": {
      "id": "uuid",
      "name": "John Doe",
      "type": "end_user"
    },
    "text": "Hello world",
    "created_at": "2025-10-28T12:34:56Z"
  }
}
```

## Admin Dashboard Pages

### Project Management
- List all projects
- Create new project
- Configure project settings
- View project analytics

### API Keys
- Generate new keys
- View/revoke existing keys
- Set key permissions/scopes

### Analytics Dashboard
- Total messages (chart)
- Active conversations
- Online users
- Response time metrics
- Usage by plan

### Conversations View
- Real-time conversation list
- Filter by status (open, closed)
- Quick reply interface
- Assign to team members (future)

## Migration Strategy

### Phase 1: Database
1. Create new tables (projects, api_keys, end_users)
2. Add project_id to existing tables
3. Create a default project for existing data
4. Migrate existing users to end_users

### Phase 2: Code
1. Create models and relationships
2. Implement middleware
3. Add global scopes
4. Update controllers

### Phase 3: API
1. Version existing API (v1)
2. Create admin API routes
3. Create widget API routes
4. Update authentication

### Phase 4: Frontend
1. Build admin dashboard
2. Create widget
3. Update existing chat UI (optional)

### Phase 5: Testing
1. Unit tests for isolation
2. Integration tests
3. Performance testing
4. Security audit

### Phase 6: Documentation
1. API documentation (OpenAPI/Swagger)
2. Integration guides
3. SDK documentation
4. Deployment guide

## Technology Stack

### Backend
- Laravel 11
- Laravel Sanctum (admin auth)
- Custom API key authentication
- Laravel Reverb (WebSockets)
- Queue workers for webhooks
- Redis (caching, sessions, queues)

### Frontend
- Vue 3 (admin dashboard)
- Vanilla JS + Vue (widget)
- Tailwind CSS
- Inertia.js (admin panel)

### Database
- MySQL/PostgreSQL (recommended for production)
- Redis (caching & queues)

### Infrastructure
- Load balancer (for scaling)
- CDN for widget assets
- Dedicated Reverb servers
- Background job workers

## Deployment Considerations

### Environment Variables
```env
# Multi-tenancy
ENABLE_MULTITENANCY=true
DEFAULT_PROJECT_PLAN=free

# Widget
WIDGET_CDN_URL=https://cdn.your-domain.com
WIDGET_ALLOWED_ORIGINS=*

# Billing (future)
STRIPE_KEY=xxx
STRIPE_SECRET=xxx

# Webhooks
WEBHOOK_SIGNATURE_SECRET=xxx
WEBHOOK_RETRY_ATTEMPTS=3
```

### Scaling Strategy
1. Separate servers for API and Reverb
2. Database read replicas
3. Queue workers on separate instances
4. CDN for static assets
5. Redis cluster for high availability

## Future Enhancements

1. **Team Collaboration**
   - Multiple admin users per project
   - Role-based permissions
   - Team inbox

2. **Advanced Features**
   - File attachments
   - Voice/video calls
   - Chatbots integration
   - AI-powered responses
   - Translation

3. **Analytics**
   - Conversation insights
   - Customer satisfaction (CSAT)
   - Response time tracking
   - Custom reports

4. **Integrations**
   - Slack
   - Email
   - CRM systems
   - Help desk software

5. **Mobile SDK**
   - iOS SDK
   - Android SDK
   - React Native SDK
   - Flutter SDK
