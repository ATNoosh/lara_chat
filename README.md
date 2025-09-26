# Lara Chat

A simple chat application built with Laravel and Vue.js that uses Laravel Reverb for WebSockets.

## Features

- ✅ One-to-one chat (Direct Messages)
- ✅ Group chat
- ✅ Real‑time messaging via Laravel Reverb
- ✅ Authentication with Laravel Sanctum
- ✅ Modern UI with Vue.js and Tailwind CSS
- ✅ Full API surface for chat operations

## Installation

### Prerequisites

- PHP 8.2+
- Composer
- Node.js and npm
- SQLite (default) or another supported database

### Setup Steps

1. Clone the repository:
```bash
git clone https://github.com/ATNoosh/lara_chat
cd lara_chat
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Create your environment file:
```bash
cp .env.example .env
```

5. Generate the application key:
```bash
php artisan key:generate
```

6. Run database migrations and seeders:
```bash
php artisan migrate:fresh --seed
```

7. Build frontend assets (for production):
```bash
npm run build
```

## Running the app

Open two terminals (or use separate tabs):

1. Start Laravel HTTP server:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

2. Start Reverb (WebSocket) server:
```bash
php artisan reverb:start --host=127.0.0.1 --port=8080
```

3. Optional: start Vite dev server (for hot reload during development):
```bash
npm run dev
```

Notes for Windows PowerShell:
- Use `;` between commands instead of `&&`.
- If a port is already in use, change the `--port` value and update the matching `.env` values accordingly.

## Usage

1. Navigate to `http://127.0.0.1:8000`
2. Register a new account or log in
3. Click "Start New Chat" to create a conversation
4. Pick an existing user to start a direct chat, or create a group chat

## API Endpoints

### Authentication
- `POST /api/auth/login` — Log in
- `POST /api/auth/register` — Register

### Chat Groups
- `GET /api/chat_groups` — List chat groups
- `POST /api/chat_groups` — Create a new chat group (DM or group)
- `GET /api/chat_groups/{uuid}` — Show a chat group
- `PUT /api/chat_groups/{uuid}` — Update a chat group
- `DELETE /api/chat_groups/{uuid}` — Delete a chat group

### Messages
- `GET /api/chat_groups/{groupUuid}/messages` — List messages in a group
- `POST /api/chat_groups/{groupUuid}/messages` — Send a new message
- `POST /api/chat_groups/{groupUuid}/messages/read` — Mark all as read (for member)
- `POST /api/chat_groups/{groupUuid}/messages/{message}/read` — Mark one message as read
- `GET /api/chat_messages/{id}` — Show a message
- `PUT /api/chat_messages/{id}` — Update a message
- `DELETE /api/chat_messages/{id}` — Delete a message

### Users
- `GET /api/users` — List available users (excluding current user)

## Project Structure

```
app/
├── Events/
│   ├── MessageSent.php
│   ├── MessagesRead.php
│   └── UserTyping.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/AuthenticateController.php
│   │   ├── ChatGroupController.php
│   │   └── ChatMessageController.php
│   └── Requests/
├── Models/
│   ├── ChatGroup.php
│   ├── ChatGroupMember.php
│   ├── ChatMessage.php
│   └── User.php
└── Repositories/

resources/js/
├── Pages/
│   ├── Chat/ChatApp.vue
│   └── Auth/
│       ├── Login.vue
│       └── Register.vue
├── app.js
├── bootstrap.js
└── echo.js
```

## Reverb Configuration

To use WebSockets with Reverb, ensure these environment variables are set in your `.env` (adjust host/ports if needed):

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY=${REVERB_APP_KEY}
VITE_REVERB_HOST=${REVERB_HOST}
VITE_REVERB_PORT=${REVERB_PORT}
VITE_REVERB_SCHEME=${REVERB_SCHEME}
```

## Testing

To validate real‑time behavior:

1. Create two different users
2. Log in with each user in separate browsers or private windows
3. Start a direct message or a group chat between them
4. Messages, read receipts, and typing indicators should update in real time

## Important Notes

- For production, review and harden security settings
- Use queues (Redis or database) for broadcasting if needed
- Enable SSL/TLS for WebSockets in production
- Review CORS settings for your API and WebSockets

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

This project is released under the MIT License.