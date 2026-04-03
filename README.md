# University App — Backend API

Backend është ndërtuar me **Laravel 12** dhe ofron një REST API të sigurt me autentikim bazuar në token (Laravel Sanctum).

---

## Cfare duhet te kesh parasysh

Para se të nisësh, sigurohu që ke të instaluar:

- **PHP** v8.2 ose më i ri (une kam perdorur XAMPP)
- **Composer** v2 ose më i ri
- **MySQL** (vjen me XAMPP)
- **Git**

---

##  Klono repon

```bash
git clone https://github.com/kristopapallazo/university-api.git
cd university-api
```

ME PAS:
Krijo database-in me emrin university_db në phpMyAdmin
Kopjo .env.example → .env dhe plotëso kredencialet
Ekzekuto php artisan migrate


#. Nis serverin e zhvillimit

```bash
php artisan serve
```

API është e disponueshme në `http://localhost:8000`

---

## Komandat e dobishme

```bash
php artisan serve          # Nis serverin lokal
php artisan migrate        # Ekzekuto migrations
php artisan migrate:fresh  # Fshi dhe rikrijoj të gjitha tabelat
php artisan route:list     # Shiko të gjitha routes
php artisan make:model X   # Krijo model të ri
php artisan make:controller XController  # Krijo controller të ri
```

---

## Rolet

Aplikacioni ka tre role: `admin`, `pedagog`, `student`.  
Çdo kërkesë e mbrojtur kërkon token të vlefshëm në header:

```
Authorization: Bearer {token}
```

Tokeni merret pas login-it ose regjistrit.

---

## Endpoints kryesore

### Publike (nuk kërkojnë login)

Metoda  URL  Përshkrim 

 POST  `/api/register`  Regjistrim i ri 
 POST  `/api/login`  Kyçje dhe marrje token 

### Të mbrojtura (kërkojnë token)

| Metoda | URL | Përshkrim |
|--------|-----|-----------|
| POST | `/api/logout` | Dalje |
| GET | `/api/students` | Lista e studentëve |
| GET | `/api/pedagogues` | Lista e pedagogëve |
| GET | `/api/courses` | Lista e lëndëve |
| GET | `/api/schedules` | Orari |
| GET | `/api/grades` | Notat |

Çdo resource mbështet: `GET`, `POST`, `PUT/PATCH`, `DELETE`.

---

## Struktura e projektit

```
app/
├── Http/
│   └── Controllers/      # Logjika e API
│       ├── AuthController.php
│       ├── StudentController.php
│       ├── PedagogueController.php
│       ├── CourseController.php
│       ├── ScheduleController.php
│       └── GradeController.php
├── Models/               # Modelet e database-it
│   ├── User.php
│   ├── Student.php
│   ├── Pedagogue.php
│   ├── Course.php
│   ├── Schedule.php
│   └── Grade.php
database/
└── migrations/           # Struktura e tabelave
routes/
└── api.php               # Të gjitha routes e API
```

---

## Thirrjet API

Bëhen vetëm nëpërmjet skedarëve në `src/services/` nga frontend.  
Gjithmonë dërgo header:

```
Content-Type: application/json
Accept: application/json
```

---

## Commit-et

Çdo commit duhet të ndjekë formatin:

```
feat(scope): përshkrim
fix(scope): përshkrim
docs(scope): përshkrim
```

**Shembuj:**
```
feat(auth): shto endpoint për login
fix(grades): korrigjo validimin e notës
docs(readme): përditëso udhëzimet e instalimit
```

---

## Shënime të rëndësishme

- Kurrë mos bej commit skedarin `.env` — ai është në `.gitignore`
- Gjithmonë ekzekuto `php artisan migrate` pas pull-it nëse ka migration të reja
- E gjithë UI dhe mesazhet e API janë në **shqip**
