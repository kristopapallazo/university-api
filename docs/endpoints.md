# API Endpoints

Base URL: `/api/v1/`

## Status
- [ ] Auth endpoints
- [ ] Student endpoints
- [ ] Pedagog endpoints
- [ ] Faculty / Program endpoints
- [ ] Schedule endpoints
- [ ] Grades endpoints

## Response Format

All responses follow this structure:

```json
{
  "data": {},
  "message": "...",
  "status": 200
}
```

## Authentication

Laravel Sanctum — token-based SPA auth.

| Method | Endpoint         | Description       |
|--------|------------------|-------------------|
| POST   | /api/v1/login    | Login (all roles) |
| POST   | /api/v1/logout   | Logout            |
| GET    | /api/v1/me       | Current user info |

_Add endpoint documentation here as BE team builds them._
