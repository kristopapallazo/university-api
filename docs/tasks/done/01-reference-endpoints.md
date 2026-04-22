# Detyrat për Zhvilluesit e Rinj

> Para se të filloni, sigurohuni që projekti raporton mirë lokalisht:
>
> ```bash
> make setup
> make dev   # → http://localhost:8000
> ```
>
> Login admin: `admin@uamd.edu.al` / `change-me-immediately`

Secila detyrë ndjek të njëjtin pattern si `Faculty` dhe `Department` — shikoni:

- `app/Models/Faculty.php`
- `app/Http/Resources/FacultyResource.php`
- `app/Http/Controllers/FacultyController.php`
- `routes/api.php` (seksioni Protected)

---

## Detyrë 1 — Programet e Studimit

**Endpoint-et:** `GET /api/v1/programs` dhe `GET /api/v1/programs/{id}`

**Tabela:** `PROGRAM_STUDIM` | PK: `PROG_ID`

| Kolona     | Tipi   | Shënim                                      |
| ---------- | ------ | ------------------------------------------- |
| `PROG_ID`  | int    | Primary key                                 |
| `PROG_EM`  | string | Emri i programit                            |
| `PROG_NIV` | string | Niveli: `Bachelor`, `Master`, `Doktorature` |
| `PROG_KRD` | int    | Kredite (> 0)                               |
| `DEP_ID`   | int    | FK → `DEPARTAMENT`                          |

**Krijo këto skedarë:**

1. `app/Models/ProgramStudim.php`
2. `app/Http/Resources/ProgramStudimResource.php` — ekspozo: `id`, `name`, `level`, `credits`, `departmentId`
3. `app/Http/Controllers/ProgramStudimController.php` — `index()` me filter opsional `?department_id=` dhe `show($id)`
4. Route-et nën `auth:sanctum` në `routes/api.php`

---

## Detyrë 2 — Lëndët

**Endpoint-et:** `GET /api/v1/courses` dhe `GET /api/v1/courses/{id}`

**Tabela:** `LENDA` | PK: `LEND_ID`

| Kolona      | Tipi   | Shënim             |
| ----------- | ------ | ------------------ |
| `LEND_ID`   | int    | Primary key        |
| `LEND_EMER` | string | Emri i lëndës      |
| `LEND_KOD`  | string | Kodi unik i lëndës |
| `DEP_ID`    | int    | FK → `DEPARTAMENT` |

**Krijo këto skedarë:**

1. `app/Models/Lenda.php`
2. `app/Http/Resources/LendaResource.php` — ekspozo: `id`, `name`, `code`, `departmentId`
3. `app/Http/Controllers/LendaController.php` — `index()` me filter opsional `?department_id=` dhe `show($id)`
4. Route-et nën `auth:sanctum` në `routes/api.php`

---

## Detyrë 3 — Pedagogët

**Endpoint-et:** `GET /api/v1/pedagogues` dhe `GET /api/v1/pedagogues/{id}`

**Tabela:** `PEDAGOG` | PK: `PED_ID`

| Kolona        | Tipi   | Shënim                                              |
| ------------- | ------ | --------------------------------------------------- |
| `PED_ID`      | int    | Primary key                                         |
| `PED_EM`      | string | Emri                                                |
| `PED_MB`      | string | Mbiemri                                             |
| `PED_GJINI`   | char   | `M` ose `F`                                         |
| `PED_TITULLI` | string | `Prof. Dr.`, `Dr.`, `Msc.`, `Doc.`, `Prof. As. Dr.` |
| `PED_EMAIL`   | string | Email (unik, mbaron me `@uamd.edu.al`)              |
| `DEP_ID`      | int    | FK → `DEPARTAMENT`                                  |

**Krijo këto skedarë:**

1. `app/Models/Pedagog.php`
2. `app/Http/Resources/PedagogResource.php` — ekspozo: `id`, `firstName`, `lastName`, `title`, `email`, `gender`, `departmentId`
    - **Mos ekspozo** `PED_DTL` dhe `PED_DT_PUNESIM` — të dhëna private
3. `app/Http/Controllers/PedagogController.php` — `index()` dhe `show($id)`
4. Route-et nën `auth:sanctum` në `routes/api.php`

---

## Rregulla për të tre detyrat

- Cakto `protected $table` dhe `protected $primaryKey` te çdo model — shiko `Faculty.php` si shembull
- Nëse tabelat përdorin `CREATED_AT`/`UPDATED_AT` (UPPERCASE), shto konstanten te modeli:
    ```php
    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';
    ```
- Nëse `show($id)` nuk gjen rekordin, kthe `404`:
    ```php
    return $this->error('Rekordi nuk u gjet.', 404);
    ```
- Testo me Postman — login tek `POST /api/v1/auth/login`, merr token-in dhe dërgoje si header:
    ```
    Authorization: Bearer {token}
    ```
- Para çdo commit-i ekzekuto `make fix` dhe `make analyse`
