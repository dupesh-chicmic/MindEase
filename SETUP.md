# MindEase — Backend setup

Laravel **11** REST API with **JWT** (`tymon/jwt-auth`), **single active session** (`current_session_token` + `session.token` middleware), and optional integrations for **mail (OTP)**, **Anthropic Claude (chat)**, **Google Gemini (mood suggestions)**, and **Firebase (FCM)**.

---

## Prerequisites

- **PHP** `>= 8.2`
- **Composer** 2.x
- **Database**: SQLite (repo default) or **MySQL/MariaDB** (common with XAMPP)
- **PHP extensions**: `bcmath`, `ctype`, `curl`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_sqlite` and/or `pdo_mysql`, `tokenizer`, `xml`

---

## 1. Install dependencies

From the project root (`MindEase/`):

```bash
composer install
```

---

## 2. Environment file

```bash
cp .env.example .env
```

### Application & JWT

```bash
php artisan key:generate
php artisan jwt:secret
```

- `APP_KEY` — from `key:generate`
- `JWT_SECRET` — from `jwt:secret` (required for login/register tokens)

Set `APP_URL` to the URL clients use (e.g. `http://127.0.0.1:8000` or your **ngrok** HTTPS URL) so generated links and callbacks stay correct.

### Database

**SQLite (default in `.env.example`)**

```bash
touch database/database.sqlite
```

In `.env`:

```env
DB_CONNECTION=sqlite
# Leave DB_DATABASE empty to use database/database.sqlite, or set an absolute path:
# DB_DATABASE=/absolute/path/to/MindEase/database/database.sqlite
```

**MySQL / MariaDB (e.g. XAMPP)**

Create a database, then in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mindease
DB_USERNAME=root
DB_PASSWORD=
```

This project sets MySQL collation to **`utf8mb4_unicode_ci`** in `config/database.php` to avoid emoji/Unicode issues on older XAMPP MySQL defaults.

### Mail (password reset OTP)

Forgot-password flows send OTP email. For local dev, `MAIL_MAILER=log` writes messages to the log. For real delivery, configure SMTP (e.g. Mailtrap) in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=2525
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Optional: Claude (in-app chat)

```env
CLAUDE_API_KEY=sk-ant-api03-...
```

Without a key, chat responses fall back to an error/service-unavailable style behavior (see `ChatService`).

### Optional: Gemini (GET `/api/v1/suggestions`)

```env
GEMINI_API_KEY=
# GEMINI_MODEL=gemini-2.5-flash
# GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
# GEMINI_TIMEOUT=45
# GEMINI_VERIFY_SSL=true
```

If `GEMINI_API_KEY` is empty, the API still works using a **static fallback** payload from `AISuggestionService`.

### Optional: Firebase (FCM device tokens)

Used by FCM routes when configured:

```env
FIREBASE_PROJECT_ID=
GOOGLE_APPLICATION_CREDENTIALS=/absolute/path/to/service-account.json
```

---

## 3. Migrations & seeders

```bash
php artisan migrate --seed
```

`DatabaseSeeder` runs:

- `QuoteSeeder` — dashboard quotes
- `DashboardSampleSeeder` — sample mood rows for demo user scenarios
- `SuggestionSeeder` — static suggestion lines in `suggestions` table
- Creates a **Test User** (`test@example.com`) via the factory (adjust password in factory/seeder if needed)

### If `migrate` fails on duplicate `moods` table

An older migration may conflict if `moods` was already created by a newer migration. The repo guards `2026_03_28_000003_create_moods_table` with `Schema::hasTable('moods')`. If your DB is in a bad state, fix the `migrations` table or run targeted migrations after a clean DB.

### User profile mood columns

`2026_03_28_160000_add_mood_fields_to_users_table` adds `mood_score`, `mood_label`, `emoji` on `users` for `PATCH /api/v1/mood`.

---

## 4. Run the application

**Artisan (simplest for API testing)**

```bash
php artisan serve
```

Base URL: `http://127.0.0.1:8000`  
API prefix: **`/api`** (Laravel default) → e.g. `http://127.0.0.1:8000/api/v1/auth/login`

**XAMPP / Apache**

Point the vhost or document root to `MindEase/public` (or use `http://localhost/MindEase/public/...` depending on your layout). Ensure `mod_rewrite` allows the front controller.

---

## 5. API overview

All JSON routes below are under **`/api/...`** (unless you changed the API prefix in `bootstrap/app.php`).

### Authentication

| Method | Path | Middleware | Notes |
|--------|------|------------|--------|
| POST | `/api/v1/auth/register` | — | Returns JWT + user |
| POST | `/api/v1/auth/login` | — | Returns JWT + user |
| POST | `/api/v1/auth/forgot-password` | — | Sends OTP email |
| POST | `/api/v1/auth/verify-otp` | — | Verifies OTP |
| POST | `/api/v1/auth/update-password` | — | After verified OTP |
| POST | `/api/v1/auth/logout` | `session.token` | |
| GET | `/api/v1/auth/me` | `session.token` | Profile + stats |
| PUT | `/api/v1/auth/profile/update` | `session.token` | |

**Headers for protected routes:** `Authorization: Bearer <token>` and `Accept: application/json`.

**`session.token`**: JWT must match `users.current_session_token` (new login invalidates old tokens).

### Chat, dashboard, calendar, insights, FCM, mood log, suggestions

| Method | Path | Middleware |
|--------|------|------------|
| POST | `/api/v1/chat/thread` | `session.token` |
| POST | `/api/v1/chat/send` | `session.token` |
| GET | `/api/v1/chat/history` | `session.token` |
| GET | `/api/v1/chat/threads` | `session.token` |
| DELETE | `/api/v1/chat/thread/{id}` | `session.token` |
| GET | `/api/v1/dashboard` | `session.token` |
| GET | `/api/v1/calendar?month=&year=` | `session.token` |
| GET | `/api/v1/insights` | `session.token` |
| GET | `/api/v1/suggestions?mood=` | `session.token` |
| POST | `/api/v1/mood/log` | `session.token` |
| POST | `/api/v1/fcm/token` | `session.token` |
| DELETE | `/api/v1/fcm/token` | `session.token` |

### User table mood (emoji picker)

| Method | Path | Middleware | Notes |
|--------|------|------------|--------|
| PATCH | `/api/v1/mood` | **`auth:api`** | Updates only `mood_score`, `mood_label`, `emoji` on `users` — **does not** use `session.token` |

List registered routes:

```bash
php artisan route:list --path=api
```

---

## 6. Web / static pages (Blade)

These are **web** routes (no `/api` prefix):

| URL | View |
|-----|------|
| `/` | `welcome` |
| `/about` | `static.about` |
| `/help` | `static.help` |
| `/privacy` | `static.privacy` |

The static pages use Tailwind via CDN in `layouts/mindease-static.blade.php`.

---

## 7. Scheduled commands

`SendMoodReminder` is registered in `routes/console.php`. In production, add Laravel’s scheduler to cron:

```cron
* * * * * cd /path/to/MindEase && php artisan schedule:run >> /dev/null 2>&1
```

---

## 8. Troubleshooting

| Issue | What to check |
|--------|----------------|
| `401 Session expired` on protected API | New login elsewhere; use latest token from `login` / `register`. |
| `Column not found` on PATCH mood | Run migrations including `2026_03_28_160000_add_mood_fields_to_users_table`. |
| OTP email not received | `MAIL_*` in `.env`, queue if using `queue` driver, and `storage/logs/laravel.log` when `MAIL_MAILER=log`. |
| Chat always errors | `CLAUDE_API_KEY` in `.env` and outbound HTTPS from the server. |
| Suggestions always fallback | Expected without `GEMINI_API_KEY`; optional for dev. |

---

## 9. Code quality (optional)

```bash
./vendor/bin/pint
```

---

You now have a full local backend: auth with JWT + session pinning, mood logging, dashboard/calendar, AI chat and suggestions (when keys are set), FCM hooks, and public About/Help/Privacy pages.
