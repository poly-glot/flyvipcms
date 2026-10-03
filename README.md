# FlyVIP CMS

Private-jet membership CMS built on CodeIgniter 4.7 (PHP 8.4, MySQL 8.4, Shield for auth). Deploys to Cloud Run behind Firebase Hosting with the shared OCI HeatWave MySQL, following the `firebase-cloud` .

## Run it locally (devcontainer)

```bash
devcontainer up --workspace-folder .        # builds PHP 8.4 + MySQL 8.4, installs deps, migrates, seeds demo data
open http://localhost:8090
```

Inside the container: `php spark serve --host 0.0.0.0 --port 8080` is started by `post-start.sh`; the compose file publishes it on `127.0.0.1:8090`.

### Demo accounts (local only — `DemoSeeder` refuses to run in production)

| Role | Sign in with (email) | Password | What it shows |
|---|---|---|---|
| Admin | `admin@flyvip.test` | `FlyVIP-Admin-2026!` | Whole CMS under `/admin` |
| Personal member | `ana.personal@flyvip.test` | `FlyVIP-Personal-2026!` | Active, 12,000 pts, a pending seat request on a shared flight |
| Family member | `carlos.family@flyvip.test` | `FlyVIP-Family-2026!` | Active, owns the shared flight, has a sub-member |
| Sub-member | `lucia.family@flyvip.test` | `FlyVIP-Submember-2026!` | Read-only view of the family account |
| Corporate member | `acme.corporate@flyvip.test` | `FlyVIP-Corporate-2026!` | Paid two quarters, 24,000 pts |
| Prospect | `new.prospect@flyvip.test` | `FlyVIP-Prospect-2026!` | Joining fee unpaid: can only be charged the joining fee |
| Banned member | `banned.member@flyvip.test` | `FlyVIP-Banned-2026!` | Login is refused |

## Quality gates

```bash
composer quality     # php-cs-fixer → PHPStan level 8 → Rector → PHPUnit → coverage floor → frontend lint
composer cs-fix      # apply PHP formatting
composer rector      # apply Rector
npm run lint         # Prettier, ESLint, Stylelint, template checks (also part of composer quality)
npm run lint:fix     # auto-fix what the linters can
npm run test:e2e     # Playwright: sign-in/out, seat map, dialogs, axe WCAG AA in light + dark (starts its own server on :8081)
npm run test:visual  # report-only: screenshots of every page, 3 widths × light/dark → test-results/visual/
tests/smoke.sh       # HTTP smoke test of every page for every role against a running server
```

Frontend checks inside the devcontainer use their own `node_modules` volume (the host's `node_modules` is never shared with the container). From the host, `npm run test:e2e:running` targets an already running app at `E2E_BASE_URL` (default `http://localhost:8090`).

CI (`.github/workflows/ci.yml`) runs `composer audit`, `composer validate --strict` and `composer quality` against a MySQL 8.4 service; then, in parallel, the blocking `e2e` job (Playwright + axe), the non-blocking `visual` job (screenshots uploaded as an artifact) and the production image build.

## Areas

- `/admin` — members & sub-members, payments, points ledger and transfers, reservations, aircraft, aircraft types, maintenance, airports, routes, pilots (+ documents, certifications, flight schedule). Admin only.
- `/portal` — members see balance, points history, reservations (book, cancel, confirm seat requests) and profile; sub-members get a read-only view of the primary member's account.
- `/account/password` — any signed-in user changes their password.

## Deploy

1. Apply `infra/firebase-cloud.patch` in the `firebase-cloud` repo (`git apply`) and run its Terraform workflow: creates the identity, `flyvipcms` MySQL secret shells (filled by `personal-cloud` from the `mysql-app-catalog`), Cloud Run service, Hosting site and `flyvipcms.junaid.guru`.
2. Set the repo secrets from the Terraform outputs: `WIF_PROVIDER` ← `flyvipcms_wif_provider`, `GCP_SA_EMAIL` ← `flyvipcms_gcp_sa_email`.
3. Push to `main`: `deploy.yml` builds the image, pushes to Artifact Registry and runs `gcloud run deploy`. The container applies migrations on boot (`php spark migrate --all`); set `SKIP_MIGRATIONS=1` to skip.
4. Create the first admin once the service is up:
   `gcloud run jobs` / `docker exec` → `ADMIN_PASSWORD=… php spark app:create-admin <username> <email>`.

Runtime configuration (env): `CI_ENVIRONMENT`, `APP_FULL_BASE_URL`, `COOKIE_SECURE`, `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_SSL`, `DB_PORT`.

See `CLAUDE.md` for the domain rules and the reasoning behind the non-obvious choices.
