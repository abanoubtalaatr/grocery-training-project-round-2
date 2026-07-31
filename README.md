# SmartLearn LMS — Authentication Learning Project

Laravel **12** training app that demonstrates four authentication methods in one codebase:

| Phase | Method | Audience |
|------|--------|----------|
| 1 | **Session** (Breeze) | Blade website |
| 2 | **Sanctum** | Own Flutter / SPA |
| 3 | **Passport** (OAuth2) | Third-party apps |
| 4 | **JWT** | Microservices |

Interactive presentation: **[/learn](/learn)**

---

## Quick start

```bash
composer install
cp .env.example .env   # or use the provided .env
php artisan key:generate
php artisan jwt:secret
php artisan passport:keys

# MySQL database: smartlearn_lms
php artisan migrate --seed

npm install && npm run build
php artisan serve
```

Open:

- Presentation: http://127.0.0.1:8000/learn
- Session login: http://127.0.0.1:8000/login

### Demo users (password: `password`)

- `admin@smartlearn.test`
- `instructor@smartlearn.test`
- `student@smartlearn.test`

OAuth client IDs/secrets are printed during seeding and saved to `storage/oauth-demo-clients.json`.

---

## API map

### Sanctum

- `POST /api/sanctum/login`
- `POST /api/sanctum/logout`
- `GET /api/sanctum/profile`
- `GET /api/sanctum/courses`

### Passport

- `POST /oauth/token`
- `GET /oauth/authorize`
- `GET /api/oauth-demo/courses` — scope `courses.read`
- `GET /api/oauth-demo/students` — scope `students.read`
- `GET /api/oauth-demo/grades` — scope `grades.read`
- `GET /api/oauth-demo/meetings` — scope `meetings.read`
- `POST /api/oauth-demo/certificates` — scope `certificates.write`
- `DELETE /api/oauth-demo/courses/{id}` — scope `courses.write`

### JWT

- `POST /api/jwt/login`
- `POST /api/jwt/refresh`
- `GET /api/jwt/profile`
- `POST /api/jwt/logout`

Postman collection: `postman/SmartLearn-Auth.postman_collection.json`

---

## Learning notes

- **Session**: cookie + server session (stateful website).
- **Sanctum**: personal access tokens for first-party apps.
- **Passport**: Client ID/Secret + scopes for third parties.
- **JWT**: signed, mostly-stateless Bearer tokens.

Sanctum and Passport both want a `HasApiTokens` trait. This project keeps **Passport’s trait** on `User` and uses a custom Sanctum token helper + `sanctum.auth` middleware so both demos work side by side.
