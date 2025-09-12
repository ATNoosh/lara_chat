# Lara Chat

یک اپلیکیشن چت ساده با Laravel و Vue.js که از Laravel Reverb برای WebSocket استفاده می‌کند.

## ویژگی‌ها

- ✅ چت دوبه دو (Direct Messages)
- ✅ چت گروهی (Group Chat)
- ✅ پیام‌رسانی بلادرنگ با Laravel Reverb
- ✅ احراز هویت با Laravel Sanctum
- ✅ رابط کاربری مدرن با Vue.js و Tailwind CSS
- ✅ API کامل برای تمام عملیات چت

## نصب و راه‌اندازی

### پیش‌نیازها

- PHP 8.2+
- Composer
- Node.js و npm
- SQLite

### مراحل نصب

1. **کلون کردن پروژه:**
```bash
git clone <repository-url>
cd lara_chat
```

2. **نصب وابستگی‌های PHP:**
```bash
composer install
```

3. **نصب وابستگی‌های Node.js:**
```bash
npm install
```

4. **کپی کردن فایل محیط:**
```bash
cp .env.example .env
```

5. **تولید کلید اپلیکیشن:**
```bash
php artisan key:generate
```

6. **اجرای migration ها:**
```bash
php artisan migrate:fresh --seed
```

7. **Build کردن frontend:**
```bash
npm run build
```

### راه‌اندازی سرورها

1. **سرور Laravel:**
```bash
php artisan serve
```

2. **سرور Reverb (در ترمینال جداگانه):**
```bash
php artisan reverb:start
```

3. **سرور Vite برای development (اختیاری):**
```bash
npm run dev
```

## استفاده

1. به آدرس `http://localhost:8000` بروید
2. یک حساب کاربری جدید ایجاد کنید یا وارد شوید
3. از دکمه "Start New Chat" برای شروع چت جدید استفاده کنید
4. کاربران موجود را انتخاب کنید تا چت دوبه دو شروع شود

## API Endpoints

### احراز هویت
- `POST /api/auth/login` - ورود
- `POST /api/auth/register` - ثبت‌نام

### چت گروه‌ها
- `GET /api/chat_groups` - لیست گروه‌های چت
- `POST /api/chat_groups` - ایجاد گروه چت جدید
- `GET /api/chat_groups/{id}` - جزئیات گروه چت
- `PUT /api/chat_groups/{id}` - ویرایش گروه چت
- `DELETE /api/chat_groups/{id}` - حذف گروه چت

### پیام‌ها
- `GET /api/chat_groups/{groupId}/messages` - لیست پیام‌های گروه
- `POST /api/chat_groups/{groupId}/messages` - ارسال پیام جدید
- `GET /api/chat_messages/{id}` - جزئیات پیام
- `PUT /api/chat_messages/{id}` - ویرایش پیام
- `DELETE /api/chat_messages/{id}` - حذف پیام

### کاربران
- `GET /api/users` - لیست کاربران موجود

## ساختار پروژه

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/AuthenticateController.php
│   │   ├── ChatGroupController.php
│   │   └── ChatMessageController.php
│   └── Requests/
├── Models/
│   ├── ChatGroup.php
│   ├── ChatMessage.php
│   ├── ChatGroupMember.php
│   └── User.php
├── Events/
│   └── MessageSent.php
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

## تنظیمات Reverb

برای استفاده از WebSocket، مطمئن شوید که تنظیمات زیر در فایل `.env` موجود است:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST="localhost"
REVERB_PORT=8081
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## تست

برای تست عملکرد:

1. دو کاربر مختلف ایجاد کنید
2. با هر کاربر در مرورگر جداگانه وارد شوید
3. چت جدیدی بین آن‌ها ایجاد کنید
4. پیام‌ها باید بلادرنگ نمایش داده شوند

## نکات مهم

- برای production، حتماً تنظیمات امنیتی را بررسی کنید
- از Redis یا database برای queue استفاده کنید
- SSL/TLS را برای WebSocket فعال کنید
- تنظیمات CORS را برای API بررسی کنید

## مشارکت

برای مشارکت در پروژه، لطفاً pull request ارسال کنید.

## مجوز

این پروژه تحت مجوز MIT منتشر شده است.