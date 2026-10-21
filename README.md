# Unit Testing Exercise

A deliberately imperfect login/registration app, built for students to run
a manual test script against. React + Vite frontend, PHP + MariaDB/MySQL
backend. Live at https://unittest.toolsforteaching.co.uk; runs locally on
Laragon (Apache on :80 for the API, Vite on :5173 for the frontend).

The student-facing test script lives at
[`docs/Unit_Testing_Student_Test_Script.docx`](docs/Unit_Testing_Student_Test_Script.docx).

## Stack

- `src/` - React (JS, not TS) frontend, React Router, Sonner for toasts
- `api/` - PHP REST endpoints (no framework, PDO + prepared statements)
- `database/` - schema and a seed/reset script

## Local setup

1. Laragon's Apache document root must point at this project folder (already
   done for this checkout - see `laragon.ini` / `00-default.conf` if it
   ever needs repointing).
2. Database: a local MariaDB database and user already exist, mirroring the
   production credentials exactly (see `api/.config.json`, which is
   git-ignored - recreate it from the credentials you were given if you're
   setting this up fresh). Apply the schema and seed the fixed test account:

   ```
   mysql -u u240325118_unittest -p u240325118_unittest < database/schema.sql
   php database/seed.php
   ```

3. Install frontend dependencies and start Vite:

   ```
   npm install
   npm run dev
   ```

4. Visit http://localhost:5173.

### Resetting between test runs

Test 4 in the student script deliberately locks the `test@test.com`
account. Run `php database/seed.php` before each fresh run of the test
script to put that account back into a known-good state (validated,
unlocked, zero failed attempts) without disturbing any accounts students
have registered themselves.

## Deploying

1. Upload the contents of `api/` and `database/` to the server alongside
   the built frontend.
2. `npm run build` and upload the contents of `dist/` to the same web root
   (so `/` serves the SPA and `/api/...` serves the PHP endpoints, exactly
   as in local dev).
3. Make sure `api/.config.json` exists on the server with the real
   credentials - it is git-ignored and never uploaded automatically.
4. Run `database/schema.sql` against the production database, then
   `php database/seed.php` once to create the fixed test account.
5. Confirm `api/.htaccess` is in place (it should upload automatically) -
   it's what keeps the real `.config.json` from ever being served.

## Security notes

- `api/config.json` (no leading dot) is a **decoy**. It is intentionally
  world-readable and contains fake credentials, to give students something
  to find when they go looking for exposed configuration files. The real
  configuration is `api/.config.json`, which `api/.htaccess` blocks for
  every request regardless of origin.
- All database access goes through PDO prepared statements - no string
  concatenation of user input into SQL anywhere.
- Session tokens are opaque random values stored server-side (not JWTs),
  sent as `Authorization: Bearer` headers rather than cookies, so there's
  no ambient-credential CSRF surface to worry about.
- A couple of rows in the student test script are marked
  `[THIS SHOULD FAIL]` - those are intentional defects for the students to
  discover during testing, each flagged with a one-line comment at the
  point in the code where the defect lives (see `api/login.php` and
  `src/data/locales.js`).
