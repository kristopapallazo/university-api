# University App — Backend API

REST API për **Portalin e Universitetit Aleksander Moisiu Durrës (UAMD)**, ndërtuar me **Laravel 12** dhe me autentikim me token (Laravel Sanctum + Google OAuth për studentët).

> **Status:** Phase 2 në zhvillim. Backend-i është i deploy-uar në **Railway** (shih `railway.json`). E gjithë logjika e dizajnit (auth, konvencionet, plani i PR-ve) është në repon e workspace-it kryesor te `docs/backend/phase-2-plan.md`. Lexoje atë para se të hapësh një PR.

---

## Çfarë duhet të kesh të instaluar

| Tool     | Versioni  | I detyrueshëm? |
| -------- | --------- | -------------- |
| PHP      | 8.3+      | po             |
| Composer | 2+        | po             |
| Git      | latest    | po             |
| MySQL    | 8         | **jo** — vetëm nëse do të punosh me schema lokale |

> **MySQL nuk nevojitet për punë normale backend-i.** Si parazgjedhje, projekti lidhet me DB-në e prodhimit (Railway). Vetëm lead-i menaxhon migrations/schema. Juniorët shkruajnë controllers/resources/requests dhe lexojnë/shkruajnë te e njëjta DB e përbashkët.

---

## Setup-i i parë (njëherësh)

```bash
git clone https://github.com/kristopapallazo/university-api.git
cd university-api
make setup
```

`make setup` bën: `composer install`, kopjon `.env` nga `.env.example`, gjeneron `APP_KEY`, lidh git hooks. Migrations ekzekutohen **vetëm nëse je në DB lokale** (përndryshe anashkalohen automatikisht).

### Kredencialet (kërko nga lead-i)

`.env.example` përmban vetëm placeholder. Vlerat reale të prodhimit (DB host, user, password, Google OAuth keys) **nuk janë në git**. Pyet Kriston dhe i fut në `.env`-in tënd lokal:

- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (Railway)
- `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET`

⚠️ **Mos bëj kurrë commit `.env`.** Është në `.gitignore`.

---

## Pas çdo `git pull`

```bash
git pull
composer install      # no-op nëse asgjë s'ka ndryshuar — i sigurt ta ekzekutosh gjithmonë
make dev
```

Nëse diçka duket çuditshëm pas pull-it (ndryshime që nuk shfaqen, gabime "class not found"):

```bash
php artisan config:clear
php artisan cache:clear
```

**Mos ekzekuto `make migrate` ose `make fresh`.** Skema e DB-së menaxhohet vetëm nga lead-i. Nëse pas një pull-i sheh gabime tipi "table doesn't exist" ose "column not found", kjo do të thotë që migration-i i ri nuk është aplikuar ende në prodhim — njofto Kriston, mos provo ta rregullosh vetë.

---

## Komandat ditore (Makefile)

| Komanda        | Çfarë bën                                              |
| -------------- | ------------------------------------------------------ |
| `make dev`     | Nis API-n në `http://localhost:8000`                   |
| `make migrate` | Ekzekuto migrations e reja                             |
| `make fresh`   | Fshi DB-në dhe rifresko tabelat + seeders              |
| `make lint`    | Pint check (formatter, vetëm raporton)                 |
| `make fix`     | Pint apply (auto-format)                               |
| `make analyse` | Larastan static analysis                               |
| `make test`    | Ekzekuto PHPUnit                                       |
| `make ci`      | lint + analyse + test (çfarë ekzekuton CI)             |
| `make docs`    | Rigjenero dokumentet e API-t (Scribe)                  |
| `make env-local` | Kalo te `.env` lokal (DB lokal)                      |
| `make env-prod`  | Kalo te `.env.production` (⚠️ DB e prodhimit)        |

Pre-commit hook (lidhet automatikisht nga `make setup`) ekzekuton `pint --test` dhe `phpstan` para çdo commit-i. Nëse dështon, ekzekuto `make fix` dhe bëj commit përsëri.

---

## Endpoint-et aktuale

Të gjitha route-et janë nën `/api/v1/`. I gjen tek [routes/api.php](routes/api.php).

### Publike (pa token)

| Metoda | URL                          | Përshkrim                                                       |
| ------ | ---------------------------- | --------------------------------------------------------------- |
| POST   | `/api/v1/auth/login`         | Login pedagog/admin me email + password (rate-limit 6/min)      |
| GET    | `/api/v1/auth/google/redirect` | Kthen URL-në e konsentit të Google                            |
| GET    | `/api/v1/auth/google/callback` | Verifikon `@students.uamd.edu.al`, krijon token Sanctum       |

### Të mbrojtura (kërkojnë `Authorization: Bearer {token}`)

| Metoda | URL                       | Përshkrim                          |
| ------ | ------------------------- | ---------------------------------- |
| GET    | `/api/v1/auth/me`         | Useri aktual + roli                |
| POST   | `/api/v1/auth/logout`     | Anulo token-in aktual              |
| GET    | `/api/v1/faculties`       | Lista e fakulteteve                |
| GET    | `/api/v1/faculties/{id}`  | Një fakultet i vetëm               |
| GET    | `/api/v1/departments`     | Lista e departamenteve             |
| GET    | `/api/v1/departments/{id}`| Një departament i vetëm            |

> **Nuk ka endpoint publik `/register`** — është hequr me qëllim. Studentët regjistrohen automatikisht nëpërmjet Google OAuth callback-ut. Pedagogët krijohen nga admin. Admin-i seedohet manualisht (shih `database/seeders/AdminSeeder.php`).

Për shemat e plota request/response shih Scribe te `http://localhost:8000/docs` (pas `make docs`).

---

## Rolet

| Rol      | Si autentikohet                     |
| -------- | ----------------------------------- |
| `student`  | Vetëm me Google OAuth (`@students.uamd.edu.al`) |
| `pedagog`  | Email + password (krijuar nga admin)            |
| `admin`    | Email + password (i seedosur manualisht)        |

Roli ruhet në kolonën `users.role`. **Mos prano kurrë `role` nga klienti** — kjo do të lejojë këdo të bëhet admin.

---

## Format i përgjigjes (response envelope)

Çdo përgjigje — sukses ose gabim — ndjek këtë format:

```json
{
  "data": {},
  "message": "OK",
  "status": 200
}
```

**Mos kthe kurrë modele Eloquent direkt.** Gjithmonë kalo nëpër një API Resource në `app/Http/Resources/`.

---

## Konvencionet (të detyrueshme)

- **Validimi** jeton në klasa `FormRequest` nën `app/Http/Requests/`. Asnjë `$request->validate()` brenda kontrollerave.
- **Transformimi** jeton në API Resources nën `app/Http/Resources/`.
- Route-et grupohen sipas auth state-it në `routes/api.php`. Prefiksi `/api/v1/` është caktuar globalisht në `bootstrap/app.php`.
- Rolet ruhen tek `users.role` (`student` | `pedagog` | `admin`).
- Migrations ndjekin emrat e tabelave me UPPERCASE (p.sh. `FAKULTET`) — cakto `protected $table` te modeli në mënyrë eksplicite.

Nëse i thyen këto konvencione, **dokumentet e API-t bëhen të padobishme** (shih më poshtë).

---

## Struktura e projektit

```
app/
├── Http/
│   ├── Controllers/         # AuthController, SocialAuthController, FacultyController, DepartmentController
│   ├── Requests/Auth/       # LoginRequest (FormRequest për validim)
│   └── Resources/           # UserResource, FacultyResource, DepartmentResource
└── Models/                  # User, Faculty, Department
database/
├── migrations/              # users, fakultet, departament, sanctum tokens, ...
└── seeders/                 # FacultySeeder, DepartmentSeeder, AdminSeeder
routes/
└── api.php                  # Të gjitha route-et (me prefiks /api/v1/)
config/
├── cors.php                 # CORS për frontend
├── sanctum.php              # Konfigurimi i tokenave
├── scribe.php               # Konfigurimi i dokumenteve auto
└── services.php             # Kredencialet Google OAuth
```

---

## Dokumentimi i API-t (Scribe) — LEXOJE

Ne e dokumentojmë API-n **automatikisht** me [Scribe](https://scribe.knuckles.wtf/). **Nuk shkruajmë annotation Swagger me dorë.** Scribe lexon kodin tënd dhe i gjeneron dokumentet vetë.

### Çfarë gjenerohet

Kur ekzekuton `make docs`, merr tre gjëra njëherësh:

1. **Dokumente HTML interaktive** në `http://localhost:8000/docs` — me buton "Try it out" për çdo endpoint
2. **Një spec OpenAPI 3 / Swagger** në `storage/app/private/scribe/openapi.yaml`
3. **Një koleksion Postman** që ekipi i frontend-it mund ta importojë

> "OpenAPI" dhe "Swagger" janë i njëjti format me dy emra. Çdo viewer Swagger lexon skedarin që Scribe gjeneron.

### Si i di Scribe çfarë të dokumentojë

Scribe nis Laravel-in dhe inspekton kodin tënd:

| Çfarë lexon Scribe                          | Çfarë bëhet në dokument                       |
| ------------------------------------------- | --------------------------------------------- |
| `routes/api.php`                            | URL-ja dhe metoda e endpoint-it               |
| `FormRequest`-i që type-hint në kontroller  | Skema e body-t + rregullat e validimit        |
| `Resource`-i që metoda kthen                | Skema e response-it                           |
| Docblock-u i metodës së kontrollerit        | Titulli + përshkrimi i endpoint-it            |

**Domethënë:** nëse ndjek konvencionet (FormRequest për input, Resource për output, docblock 1-rreshtësh), dokumenti shkruhet vetë. Nëse i anashkalon dhe valido inline me `$request->validate(...)`, **Scribe nuk e sheh dot dhe dokumenti për atë endpoint do të jetë bosh ose i gabuar.** Ky është një arsye më shumë pse konvencionet janë të detyrueshme.

### Workflow-i kur shton një endpoint të ri

1. Shto një route në `routes/api.php`
2. Krijo një `FormRequest` për input-in (në `app/Http/Requests/`)
3. Krijo një `Resource` për output-in (në `app/Http/Resources/`)
4. Shkruaj metodën e kontrollerit me një docblock 1-rreshtësh që e përshkruan
5. Ekzekuto `make docs`
6. Hap `http://localhost:8000/docs` dhe **kontrollo që endpoint-i yt është aty dhe duket si duhet**
7. Bëj commit edhe skedarëve të rigjeneruar nën `.scribe/` dhe `storage/app/private/scribe/`

**Mos e modifiko kurrë `openapi.yaml` me dorë.** Është një build artifact. Nëse diçka duket gabim te dokumenti, rregullimi është te `FormRequest`, te `Resource`, ose te route — jo te YAML-i.

### Pse e bëjmë kështu

- Dokumentet e shkruara me dorë gjithmonë largohen nga kodi me kalimin e kohës. Dokumentet e gjeneruara nuk mund të largohen.
- Ekipi i frontend-it punon në një repo tjetër. Marrin një URL të vetme (`/docs`) dhe i shërbejnë vetes gjithçka.
- E shpërblen konvencionin që po zbatojmë tashmë. Një rregull, dy fitime: kod më i pastër DHE dokumente falas.

---

## Commit-et (Conventional Commits)

Pre-commit hook + commitlint detyrojnë formatin:

```
type(scope): përshkrim
```

Tipet e lejuara: `feat`, `fix`, `chore`, `docs`, `refactor`, `test`, `ci`, `style`, `perf`.

**Shembuj:**
```
feat(auth): shto endpoint për Google OAuth callback
fix(faculty): korrigjo validimin e ID-së
docs(readme): përditëso udhëzimet e setup-it
chore(ci): shto Larastan në pipeline
```

---

## Prodhimi (Railway) & siguria e DB-së

Backend-i ekzekutohet në Railway. Variablat e mjedisit në prodhim menaxhohen nga paneli i Railway. Lokalisht, parazgjedhja është të lidhesh me të njëjtën DB të prodhimit — kjo do të thotë **një burim i vetëm i të dhënave** për të gjithë ekipin.

### Safeguards (mbrojtjet)

`make migrate` dhe `make fresh` **refuzojnë të ekzekutohen** nëse `DB_HOST` nuk është `127.0.0.1` ose `localhost`. Mesazhi që do të shohësh:

```
❌ REFUSING: DB_HOST=... is not local.
   This command would touch a remote database (likely PRODUCTION).
```

Kjo do të thotë që nuk mund të fshish ose modifikosh aksidentalisht skemën e prodhimit nga komandat e Makefile.

⚠️ **Por kujdes:** mbrojtja është vetëm te Makefile. `php artisan migrate` direkt, `php artisan tinker` me `User::truncate()`, ose SQL i papërpunuar **nuk** ndalohen. Mos ekzekuto komanda që modifikojnë DB-në po se nuk e di saktësisht çfarë po bën.

### Punë me schema (vetëm lead-i)

Ndryshimet e skemës bëhen lokalisht kundër MySQL-së lokale, pastaj aplikohen në prodhim:

```bash
make env-local     # kalon te .env me DB lokale
make migrate       # ose make fresh — i sigurt, je në lokale
# ... krijo migration, testo ...
make env-prod      # kalon te .env.production (nëse e ke)
# aplikon migration në prod manualisht (Railway CLI ose dashboard)
make env-local     # KTHEHU
```

---

## Shënime të rëndësishme

- **Mos bëj commit `.env`** — është në `.gitignore`.
- **Mos prano kurrë `role` nga input-i i klientit.** Çdo endpoint që krijon user duhet ta fiksojë rolin në backend.
- **Pas `git pull`**, ekzekuto `make migrate` (ose `make fresh` nëse pranon humbjen e të dhënave lokale).
- **Të gjitha mesazhet e API-t janë në shqip.**
- **Nëse `make ci` dështon lokalisht, edhe CI në GitHub do dështojë.** Rregulloje para se të bësh push.
