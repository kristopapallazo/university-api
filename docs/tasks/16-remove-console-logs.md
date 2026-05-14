# 16 — Remove Console Logs

> **Backlog ref:** nouncheck — cleanup debug output
> **Priority:** P3 — nice-to-have, no dependencies
> **Effort:** ~1h
> **Stack:** Laravel 11, no new dependencies
> **Branch:** `<yourname>/remove-console-logs` (example: `ornela/remove-console-logs`)
> **Before you start:** This is a cleanup task. No code dependencies — can start immediately.

---

## Goal

Remove all debug output statements from the backend codebase:

- Remove all `console.log()` statements (JavaScript — if any in Laravel blade templates)
- Remove all `dd()` (die and dump) statements
- Remove all `dump()` statements
- Remove all `var_dump()` statements
- Remove all `print_r()` statements left in for debugging

When this task is done, the codebase is production-ready with no leaking debug output.

**Note:** Legitimate logging via Laravel's `Log` facade (`Log::info()`, `Log::error()`, etc.) should be kept — this task removes only **debug output**, not logging.

---

## Workflow

1. `git checkout main && git pull`
2. `git checkout -b <yourname>/remove-console-logs`
3. Search for debug statements
4. Remove each one
5. Commit with a clear message
6. `make ci` before pushing (to ensure tests still pass)
7. Open PR against `main`

---

## Step 1 — Search for debug statements

Use grep to find all debug output in the codebase:

```bash
# Find all dd() statements
grep -r "dd(" app/ routes/ --include="*.php"

# Find all dump() statements
grep -r "dump(" app/ routes/ --include="*.php"

# Find all var_dump() statements
grep -r "var_dump(" app/ routes/ --include="*.php"

# Find all print_r() statements
grep -r "print_r(" app/ routes/ --include="*.php"

# Find all console.log() in PHP files (unlikely, but check)
grep -r "console\.log" app/ routes/ --include="*.php"
```

Record the file paths and line numbers for each occurrence.

---

## Step 2 — Remove each debug statement

For each debug statement found:

1. Open the file
2. Examine the context — is it truly debug output, or is it part of legitimate code?
3. If it's debug output:
   - **If it's the only statement on a line:** Delete the entire line
   - **If it's part of a line:** Remove just the statement
   - **If it's in a conditional:** Consider whether the conditional is still needed after removal
4. Save the file

**Example:**

Before:
```php
public function store(Request $request)
{
    $data = $request->validate([...]);
    dd($data);  // ← remove this
    
    $model = Model::create($data);
    return $this->success($model);
}
```

After:
```php
public function store(Request $request)
{
    $data = $request->validate([...]);
    
    $model = Model::create($data);
    return $this->success($model);
}
```

---

## Step 3 — Distinguish between logging and debug output

**Keep these** (legitimate logging):
```php
Log::info('User logged in', ['user_id' => $user->id]);
Log::error('Failed to fetch API', ['error' => $e->getMessage()]);
logger()->debug('Processing batch', ['count' => count($items)]);
```

**Remove these** (debug output):
```php
dd($variable);
dump($variable);
var_dump($variable);
print_r($variable);
```

---

## Step 4 — Commit

After removing all debug statements:

```bash
git add -A
git commit -m "Remove debug output statements (dd, dump, var_dump)"
```

---

## Manual smoke test

After removing debug statements, run the test suite to ensure nothing broke:

```bash
make ci
```

All tests should pass. If any fail, investigate whether the debug statement was doing something unintended.

---

## Acceptance criteria

- [ ] All `dd()` statements removed
- [ ] All `dump()` statements removed
- [ ] All `var_dump()` statements removed
- [ ] All `print_r()` statements removed
- [ ] No `console.log()` found in PHP files
- [ ] All legitimate logging (Log::*, logger()->*) is preserved
- [ ] `make ci` passes
- [ ] No functionality changed — tests still pass

---

## Optional: Consider adding a pre-commit hook

If you want to prevent future `dd()` and `dump()` statements from being committed, add to `.git/hooks/pre-commit`:

```bash
#!/bin/bash
if grep -r "^\s*dd(" app/ routes/ --include="*.php" >/dev/null 2>&1; then
    echo "❌ Error: dd() statements found. Remove before committing."
    exit 1
fi
if grep -r "^\s*dump(" app/ routes/ --include="*.php" >/dev/null 2>&1; then
    echo "❌ Error: dump() statements found. Remove before committing."
    exit 1
fi
exit 0
```

Make it executable: `chmod +x .git/hooks/pre-commit`

This is optional but recommended for teams.
