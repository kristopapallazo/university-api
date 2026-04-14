# Backend Onboarding — Guida për Zhvilluesit e Rinj

> Ky dokument shpjegon gjithçka që duhet të dish për të punuar në `university-api/`.
> Lexoje nga fillimi deri në fund para se të prekësh kodin.

---

## Përmbajtja

1. [Stack-u](#1-stack-u)
2. [Setup i parë](#2-setup-i-parë)
3. [Struktura e projektit](#3-struktura-e-projektit)
4. [Arkitektura & Konvencionet](#4-arkitektura--konvencionet)
5. [Autentikimi](#5-autentikimi)
6. [Database & Migrime](#6-database--migrime)
7. [Si të shtosh një endpoint të ri](#7-si-të-shtosh-një-endpoint-të-ri)
8. [Komandat ditore](#8-komandat-ditore)
9. [Code quality — Pint, Larastan, Git hooks](#9-code-quality)
10. [Testing](#10-testing)
11. [API Docs (Scribe)](#11-api-docs-scribe)
12. [Gabimet e zakonshme](#12-gabimet-e-zakonshme)
13. [Rregullat e ekipit](#13-rregullat-e-ekipit)

---

## 1. Stack-u

| Teknologjia       | Versioni | Përdorimi                         |
| ----------------- | -------- | --------------------------------- |
| PHP               | 8.3+     | Gjuha kryesore                    |
| Laravel           | 12       | Framework-u backend               |
| MySQL             | 8        | Databaza                          |
| Laravel Sanctum   | 4.x      | Token-based auth (SPA)            |
| Laravel Socialite | 5.x      | Google OAuth (vetëm studentë)     |
| Scribe            | 5.x      | Auto-generated API docs (`/docs`) |
| Laravel Pint      | 1.x      | Code formatter (PSR-12 + Laravel) |
| Larastan          | 3.x      | Static analysis (PHPStan level 5) |
| PHPUnit           | 11.x     | Testing                           |

---

## 2. Setup i parë

### Çfarë duhet të kesh instaluar

- **PHP 8.3+** — `php -v` për të konfirmuar
- **Composer 2+** — `composer -V`
- **MySQL 8** — XAMPP funksionon mirë në Windows
- **Git** — versioni i fundit

### Hapat

```bash
# 1. Klono repon
git clone https://github.com/kristopapallazo/university-api.git
cd university-api

# 2. Krijo database-in në MySQL / phpMyAdmin
#    CREATE DATABASE university_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 3. Ekzekuto setup-in (bën gjithçka njëherësh)
make setup

# 4. Hap .env dhe plotëso:
#    DB_PASSWORD=fjalëkalimi_yt_lokal
#    (opsionale: GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET nëse teston OAuth)

# 5. Nis serverin
make dev
# API gati në http://localhost:8000
# Docs gati në http://localhost:8000/docs
```

`make setup` bën automatikisht: `composer install` → kopjon `.env.example` → gjeneron `APP_KEY` → lidh git hooks → ekzekuton migrimet.

---

## 3. Struktura e projektit

```
university-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/          ← Logjika e endpoint-eve
│   │   │   ├── AuthController.php
│   │   │   ├── SocialAuthController.php
│   │   │   ├── FacultyController.php
│   │   │   └── DepartmentController.php
│   │   ├── Requests/             ← Validimi i inputit (FormRequest)
│   │   │   └── Auth/
│   │   │       └── LoginRequest.php
│   │   ├── Resources/            ← Transformimi i output-it (API Resource)
│   │   │   ├── UserResource.php
│   │   │   ├── FacultyResource.php
│   │   │   └── DepartmentResource.php
│   │   └── Traits/
│   │       └── ApiResponse.php   ← Helpera: success() / error()
│   ├── Models/                   ← Eloquent models
│   │   ├── User.php
│   │   ├── Faculty.php
│   │   └── Department.php
│   └── Providers/
├── bootstrap/
│   └── app.php                   ← Routing, middleware, exception handling
├── config/                       ← Konfigurime (cors, sanctum, database, etj.)
├── database/
│   ├── migrations/               ← Skemat e tabelave
│   ├── seeders/                  ← Të dhëna fillestare
│   └── factories/                ← Factories për testime
├── routes/
│   ├── api.php                   ← Të gjitha endpoint-et API
│   └── web.php                   ← Vetëm redirect-on në /docs
├── tests/
│   ├── Feature/                  ← Teste integrimi
│   └── Unit/                     ← Teste njësi
├── Makefile                      ← Shkurtesa komandash
├── .githooks/pre-commit          ← Kontrollon kodin para çdo commit-i
├── phpstan.neon                  ← Config i Larastan
├── pint.json                     ← Config i Pint
└── .env.example                  ← Template i variablave mjedisore
```

### Skedarë që **NUK** duhet të modifikosh

- `vendor/` — gjenerohet nga Composer, është në `.gitignore`
- `bootstrap/cache/` — cache i frameworkut
- `storage/` — log-e, cache, skedarë të gjeneruar
- `public/vendor/` — asete të Scribe, rigjenerohen me `make docs`

---

## 4. Arkitektura & Konvencionet

### Formati i përgjigjes (GJITHMONË)

Çdo endpoint kthen këtë strukturë:

```json
{
  "data": { ... },
  "message": "Operacioni u krye me sukses.",
  "status": 200
}
```

Përdor trait-in `ApiResponse` në controller:

```php
use App\Http\Traits\ApiResponse;

class MyController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $items = Item::all();
        return $this->success(ItemResource::collection($items));
    }

    public function store(StoreItemRequest $request)
    {
        // ... nëse dështon:
        return $this->error('Mesazhi i gabimit.', 422);
    }
}
```

### Rregullat kryesore

| Rregull                                  | Shpjegim                                             |
| ---------------------------------------- | ---------------------------------------------------- |
| **Asnjëherë kthe modelin direkt**        | Gjithmonë përdor API Resource (`UserResource`, etj.) |
| **Asnjëherë valido në controller**       | Gjithmonë krijo FormRequest (`app/Http/Requests/`)   |
| **Asnjëherë pranoje `role` nga klienti** | Roli vendoset server-side, asnjëherë nga input-i     |
| **Gjithmonë endpoint-e nën `/api/v1/`**  | Prefiksi konfigurohet në `bootstrap/app.php`         |
| **Mesazhet në shqip**                    | UI është vetëm shqip — mesazhet e API-t gjithashtu   |
| **Emrat e tabelave UPPERCASE**           | P.sh. `FAKULTET`, `DEPARTAMENT` — sipas ERD-së       |

### Naming conventions në tabela

Modelet përdorin emra të veçantë nga Laravel defaults:

```php
class Faculty extends Model
{
    protected $table = 'FAKULTET';       // emri i tabelës UPPERCASE
    protected $primaryKey = 'FAK_ID';    // primary key e personalizuar
    public $timestamps = false;          // colonat janë CREATED_AT / UPDATED_AT
    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
}
```

Kontrolloje ERD-në (`docs/images/DB ERD.jpeg`) gjithmonë para se të krijosh model/migrim të ri.

---

## 5. Autentikimi

### 3 rolet

| Roli      | Si logohet         | Si krijohet llogaria          |
| --------- | ------------------ | ----------------------------- |
| `student` | Google OAuth vetëm | Auto-krijohet në login Google |
| `pedagog` | Email + fjalëkalim | Admin-i e krijon              |
| `admin`   | Email + fjalëkalim | Seeder (artisan)              |

### Si funksionon

1. **Pedagog/Admin:** POST `/api/v1/auth/login` me email + password → merr Sanctum token
2. **Student:** GET `/api/v1/auth/google/redirect` → Google consent → callback → Sanctum token
3. **Për çdo request tjetër:** Dërgo token-in si header:
   ```
   Authorization: Bearer {token}
   ```
4. **Logout:** POST `/api/v1/auth/logout` (revokon token-in)
5. **Profili im:** GET `/api/v1/auth/me`

### Token-i skadon pas **24 orësh** (konfigurueshëm nga `SANCTUM_TOKEN_EXPIRATION` në `.env`).

### Asnjëherë:

- Mos krijo endpoint publik `/register` — nuk duhet të ekzistojë
- Mos prano `role` si input nga klienti
- Mos beso fushën `hd` të Google — gjithmonë verifiko domain-in server-side

---

## 6. Database & Migrime

### Si të krijosh një tabelë të re

```bash
php artisan make:migration create_lenda_table
```

Kjo krijon skedar në `database/migrations/`. Hape dhe shkruaj skemën:

```php
Schema::create('LENDA', function (Blueprint $table) {
    $table->id('LEN_ID');
    $table->string('LEN_EM');
    $table->unsignedBigInteger('DEP_ID');
    $table->foreign('DEP_ID')->references('DEP_ID')->on('DEPARTAMENT');
    $table->timestamp('CREATED_AT')->nullable();
    $table->timestamp('UPDATED_AT')->nullable();
});
```

**Rregulla:**

- Emrat e tabelave: **UPPERCASE** (si në ERD)
- Primary key: `EMRI_ID` (p.sh. `FAK_ID`, `LEN_ID`, `DEP_ID`)
- Foreign key: gjithmonë shto `->foreign()->references()->on()`
- Kontrollo `docs/db/schemas/final-schema.sql` për skemën e plotë
- Asnjëherë mos modifiko migrime ekzistuese që janë push-uar — krijo migrim të ri

```bash
# Ekzekuto migrimet
make migrate

# Fshi gjithçka dhe rifillo (zhvillim lokal vetëm!)
make fresh
```

### Seeders

Seeders mbushin tabelat me të dhëna fillestare. Shiko `database/seeders/` për shembuj.

```bash
# Krijo seeder
php artisan make:seeder LendaSeeder

# Shtoje në DatabaseSeeder.php
$this->call([
    FacultySeeder::class,
    DepartmentSeeder::class,
    LendaSeeder::class,  // ← e re
]);
```

---

## 7. Si të shtosh një endpoint të ri

Le të themi se duam CRUD për "Lëndë" (courses):

### Hapi 1 — Migrime & Model

```bash
php artisan make:migration create_lenda_table
php artisan make:model Lenda
```

Plotëso modelin:

```php
class Lenda extends Model
{
    protected $table = 'LENDA';
    protected $primaryKey = 'LEN_ID';
    public $timestamps = false;
    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = ['LEN_EM', 'DEP_ID'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'DEP_ID', 'DEP_ID');
    }
}
```

### Hapi 2 — API Resource

```bash
php artisan make:resource LendaResource
```

```php
class LendaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->LEN_ID,
            'name'         => $this->LEN_EM,
            'departmentId' => $this->DEP_ID,
        ];
    }
}
```

### Hapi 3 — FormRequest (nëse ka input)

```bash
php artisan make:request StoreLendaRequest
```

```php
class StoreLendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth kontrollohet nga middleware
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:DEPARTAMENT,DEP_ID'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'Emri i lëndës është i detyrueshëm.',
            'department_id.required' => 'Departamenti është i detyrueshëm.',
        ];
    }
}
```

### Hapi 4 — Controller

```bash
php artisan make:controller LendaController
```

```php
class LendaController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $lende = Lenda::all();
        return $this->success(LendaResource::collection($lende));
    }

    public function show(int $id)
    {
        $lenda = Lenda::findOrFail($id);
        return $this->success(new LendaResource($lenda));
    }

    public function store(StoreLendaRequest $request)
    {
        $lenda = Lenda::create($request->validated());
        return $this->success(new LendaResource($lenda), 'Lënda u krijua.', 201);
    }
}
```

### Hapi 5 — Rruget (Routes)

Në `routes/api.php`:

```php
Route::middleware('auth:sanctum')->group(function () {
    // ... endpoint-et ekzistues ...

    Route::get('/lende', [LendaController::class, 'index']);
    Route::get('/lende/{id}', [LendaController::class, 'show']);
    Route::post('/lende', [LendaController::class, 'store']);
});
```

### Hapi 6 — Teste

Testo endpoint-in me:

- Scribe docs (`make docs` → `http://localhost:8000/docs`)
- Postman / cURL
- PHPUnit (shiko seksionin [Testing](#10-testing))

---

## 8. Komandat ditore

```bash
# Nis API-n
make dev                    # http://localhost:8000

# Database
make migrate                # ekzekuto migrimet e reja
make fresh                  # fshi DB + ri-migro + seed

# Cilësia e kodit
make lint                   # kontrollo formatimin (nuk ndryshon asgjë)
make fix                    # auto-formato me Pint
make analyse                # Larastan static analysis

# Testime
make test                   # ekzekuto PHPUnit
make ci                     # lint + analyse + test (si CI)

# Dokumentim
make docs                   # rigjenero API docs (Scribe)
```

---

## 9. Code quality

### Laravel Pint (Formatter)

Kontrollon që kodi ndjek stilin Laravel (PSR-12 + regulla laravel). Konfigurimi: `pint.json`.

```bash
make lint    # vetëm raporton gabime
make fix     # auto-korrekton
```

### Larastan (Static Analysis)

Kontrollon tipet, metoda të padëfinuara, gabime logjike. Konfigurimi: `phpstan.neon` (Level 5).

```bash
make analyse
```

### Pre-commit hook

Kur bën `make setup`, lidhet automatikisht një git hook që ekzekuton Pint + Larastan para çdo commit-i.

Nëse commit-i dështon:

```bash
make fix           # korrekto formatimin
make analyse       # shiko gabimet e tipeve
git add .
git commit -m "mesazhi"   # provo përsëri
```

**Mos e injoro hook-un me `--no-verify`.** CI do ta refuzojë PR-në gjithsesi.

---

## 10. Testing

### Konfiguracioni

- PHPUnit përdor **SQLite in-memory** (`:memory:`) — nuk prek database-in tënd lokal
- Bcrypt rounds: 4 (shpejton testet)
- Cache/session/queue: çaktivizuar në testime

### Si ta ekzekutosh

```bash
make test
# ose
php artisan test
# ose për një test specifik
php artisan test --filter=LoginTest
```

### Si të shkruash një test

```bash
php artisan make:test Auth/LoginTest           # Feature test
php artisan make:test Models/UserTest --unit    # Unit test
```

Shembull Feature test:

```php
class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_pedagog_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'role'     => 'pedagog',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['token', 'user'],
                     'message',
                     'status',
                 ]);
    }
}
```

---

## 11. API Docs (Scribe)

Scribe lexon kodin tënd (routes, FormRequests, Resources) dhe gjeneron automatikisht:

- UI interaktive në **`/docs`** — me butonin "Try it out"
- Koleksion Postman
- OpenAPI 3 spec në **`/docs.openapi`**

```bash
make docs    # rigjenero dokumentet
```

Për të parë endpoint-et e tua pas shtimit:

1. `make docs`
2. Hap `http://localhost:8000/docs`

Scribe përdor Scribe annotations (`@group`, `@authenticated`, etj.) nëse dëshiron ti kontrollo manualisht — por FormRequests + Resources mjaftojnë për shumicën e rasteve.

---

## 12. Gabimet e zakonshme

### "SQLSTATE[42S02]: Base table or view not found"

Nuk ke ekzekutuar migrimet:

```bash
make migrate
```

### "SQLSTATE[HY000] [2002] Connection refused"

MySQL nuk është hapur. Kontrollo XAMPP → MySQL → Start.

### "Class 'App\Models\XYZ' not found"

Ekzekuto `composer dump-autoload`.

### Pre-commit hook dështon me Pint

```bash
make fix      # korrekto formatimin
git add .
git commit    # provo përsëri
```

### Pre-commit hook dështon me Larastan

Lexo mesazhin e gabimit — zakonisht:

- Tip i gabuar (type mismatch)
- Metod e padëfinuar
- Property që mungon

Korrigjo gabimin, pastaj bëj commit përsëri.

### "419 Page Expired" ose "CSRF token mismatch"

Kjo ndodh në endpoint-e web. API-ja jonë nuk ka CSRF — sigurohu që po godet `/api/v1/...` dhe jo rrugë web.

### CORS error në browser

Kontrollo `CORS_ALLOWED_ORIGINS` në `.env`. Duhet të përmbajë URL-në e frontend-it:

```
CORS_ALLOWED_ORIGINS=http://localhost:5173
```

---

## 13. Rregullat e ekipit

### Para se të nisësh punë

1. Lexo task-un/issue-n mirë
2. Krijo branch nga `master`: `git checkout -b feature/emri-i-veçorisë`
3. Kontrollo `docs/backend/phase-2-plan.md` për vendime të marra

### Kur po punon

1. Ndiq konvencionet e lartpërmendura (FormRequest, Resource, ApiResponse)
2. Shkruaj mesazhe gabimi **në shqip**
3. Ekzekuto `make ci` para se të push-osh

### Para se të hapësh PR

- [ ] `make ci` kalon pa gabime (lint + analyse + test)
- [ ] Endpoint-et kthejnë formatin `{ data, message, status }`
- [ ] Nuk ka `dd()`, `dump()` ose `var_dump()` në kod
- [ ] Nuk ka kredenciale/sekreti hardkoduar
- [ ] Migrimet e reja funksionojnë me `make fresh`
- [ ] API docs janë rigjeneruar (`make docs`) nëse ka endpoint-e të reja

### Commit messages

Shkruaj mesazhe commit të qarta:

```
feat: add lenda CRUD endpoints
fix: correct department foreign key
chore: update seeders with new faculties
```

---

## Kontakte & Burime

| Burim                        | Ku e gjen                             |
| ---------------------------- | ------------------------------------- |
| ERD (schema vizuale)         | `docs/images/DB ERD.jpeg`             |
| Organograma e faqes          | `docs/images/Organograma.jpeg`        |
| Plani i Phase 2              | `docs/backend/phase-2-plan.md`        |
| Schema SQL finale            | `docs/db/schemas/final-schema.sql`    |
| API docs (live)              | `http://localhost:8000/docs`          |
| Frontend repo                | `university-app/`                     |
| CLAUDE.md (kontekst i plotë) | `CLAUDE.md` në rrënjën e workspace-it |
