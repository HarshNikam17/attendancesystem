
# EduPro Enterprise — Smart Attendance System

Lightweight PHP + SQLite/MySQL attendance management web app designed for schools, coaching centres, corporates and events.

**What this repo contains**
- **Purpose:** Simple attendance management (groups/classes, members/students, basic reports, export, AI assistant).
- **Stack:** PHP (plain), minimal JavaScript, HTML/CSS. Meant to run on XAMPP / LAMP locally.

**Important files**
- `db.php`: [db.php](db.php#L1) — PDO DB helper (uses database name `edupro_attendance`).
- `index.html`: [index.html](index.html#L1) — Main UI shell and client-side assets.
- `login.php`: [login.php](login.php#L1) — JSON login endpoint (offline fallback available).
- `signup.php`: [signup.php](signup.php#L1) — Register new group/class endpoint.
- `students.php`: [students.php](students.php#L1) — CRUD for students (action via `action` param).
- `admin_data.php`: [admin_data.php](admin_data.php#L1) — Admin utilities (list/delete groups).
- `ai_chat.php`: [ai_chat.php](ai_chat.php#L1) — Built-in AI assistant fallback (optional OpenAI integration).
- `backup.php`: [backup.php](backup.php#L1) — Export SQL dump of all tables.
- `maintenance.php`: [maintenance.php](maintenance.php#L1) + [maintenance_status.json](maintenance_status.json#L1) — Toggle maintenance mode.

Quick note: several files reference database name `eduprom_db` while `db.php` uses `edupro_attendance`. Align the database name in one place (recommended: `eduprom_db`) before first run.

Getting started (local)
- Requirements: PHP 7.4+, MySQL (or MariaDB), a web server (XAMPP is recommended for Windows).
- Place the repository under your web root (for XAMPP: `C:\xampp\htdocs\attendancesystem`).
- Ensure MySQL is running and update credentials in `db.php` or other scripts if you changed them. Defaults in repo assume `root` / blank password.
- Create or align the database name (see note above). The `signup.php` will auto-create the database `eduprom_db` and `classes` table when run.

Example: start XAMPP, then open in browser:

```
http://localhost/attendancesystem/index.html
```

Database schema (observed/used by scripts)
- `classes` (created by `signup.php`): columns include `class_id`, `workspace`, `class_name`, `teacher_name`, `email`, `password`.
- `students` (used by `students.php`): expected columns include `id` (PK), `student_id`, `name`, `phone`, `class_id`, `status`, `remark`, `attendance_rate`.

API endpoints & usage (JSON / query params)
- `login.php` — POST JSON: `{ "workspace":"School", "username":"Class 10A", "password":"..."` }. Returns JSON success or error. See [login.php](login.php#L1).
- `signup.php` — POST JSON to register: `{ "workspace":"School","className":"Class1","teacherName":"...","email":"...","password":"..."` }.
- `students.php` — supports `action=get|save|delete|update_remark` via POST JSON or querystring. See [students.php](students.php#L1).
- `admin_data.php` — `action=get_all_groups` (GET) and `action=delete_group` (POST JSON with `admin_password`). See [admin_data.php](admin_data.php#L1).
- `ai_chat.php` — POST JSON `{ "message":"..." }`. Uses internal fallback responses unless you add an OpenAI API key in the file.
- `backup.php` — visit in browser to download SQL dump.
- `maintenance.php` — GET to read status, POST JSON `{ "maintenance": true }` to toggle.

Security & maintenance notes
- Default DB credentials in repo are `root` with blank password (XAMPP default). Change these in production.
- `admin_data.php` uses a hardcoded admin password `admin123` for delete operations — replace or remove before deploying.
- Passwords in the `classes` table are stored in plaintext. Consider hashing (password_hash / password_verify) before production use.
- Many scripts suppress PHP errors (`error_reporting(0)`); while convenient locally, enable proper error logging for development and never show raw errors in production.
- `ai_chat.php` contains placeholder `YOUR_OPENAI_API_KEY`; do not commit real API keys into the repo.

Suggested next steps
- Standardize the DB name across files (`eduprom_db` recommended) and centralize DB config in `db.php`.
- Switch to hashed passwords and add basic session-based auth for admin pages.
- Add a simple installation script to create required tables with safe defaults.

License & credits
- This repo is a small internal tool. Add a license file if you intend to publish.
