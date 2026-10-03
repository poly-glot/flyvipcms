# FlyVIP CMS — working agreement

CodeIgniter 4.7 / PHP 8.4 / MySQL 8.4 / Shield. Rebuild of the 2015 CI3 FlyVIP membership CMS.

## Commands

- `composer quality` — the gate: cs-check → phpstan (level 8) → rector-dry → phpunit → coverage floor (55%).
- `php spark migrate --all` — applies app **and** vendor (Shield, Settings) migrations; always `--all`.
- `php spark db:seed DemoSeeder` — local demo data. Throws when `ENVIRONMENT === 'production'`.
- `php spark app:create-admin <username> <email>` — password from `ADMIN_PASSWORD` or prompt (min 12 chars).
- `npm run lint` — Prettier (`printWidth 120`, single quotes, 4 spaces), ESLint, Stylelint, `tools/check-templates.php`. Part of `composer quality`.
- `npm run test:e2e` / `npm run test:visual` — Playwright; `E2E_SERVE=1` makes it start its own `php spark serve` on :8081 so the app's base URL matches the browser's.
- `tests/smoke.sh` — walks every page as every role against `BASE_URL` (default `http://127.0.0.1:8090`).

## Code rules

- No comments of any kind. Names carry intent; WHY lives in this file.
- Shared types are declared once; services take and return scalars/arrays, money is a `string` (see below).
- Property order in CSS is alphabetical. Styles live in `public/css/*.css` (tokens, ui, shell, then one file per feature: auth, people, dashboard, flights, ledger); no CDN assets, no build step. Views share the `layouts/shell` app shell and the `ui_*` helpers in `app/Helpers/ui_helper.php`; flash messages render as toasts, validation errors inline next to fields.
- Controllers stay thin: validation → one service call → redirect. Business rules live in `app/Services` and `app/Domain`.
- Every admin write is a POST behind the CSRF filter; deletes are POSTs too (legacy used GET).

## Frontend rules and how they are enforced

| Rule | Enforced by |
|---|---|
| No comments in CSS, JS, TS | Stylelint plugin `tools/stylelint-no-comments.mjs`; ESLint rule `flyvip/no-comments` in `eslint.config.js` |
| No comments in PHP or views (PHP, docblocks, HTML `<!-- -->`) | `tools/check-templates.php` (PHP tokenizer). Exempt: `app/Config`, `app/Language`, `app/Views/errors`, `app/Common.php` — framework-generated |
| CSS properties alphabetical | `order/properties-alphabetical-order` |
| Colours only from tokens (`public/css/tokens.css`) | `color-no-hex` everywhere except `tokens.css`; `tools/check-templates.php` bans hex in templates |
| BEM kebab-case classes, no `!important` | `selector-class-pattern`, `declaration-no-important` (the `[hidden]` and reduced-motion `!important`s in `tokens.css` are the only exemption) |
| No inline `style=` or `on*=` handlers in templates | `tools/check-templates.php` (inline `style` allowed only in `partials/icons.php`) |
| Escape output | `tools/check-templates.php` flags a bare `<?= $var ?>`; use `esc()` or a `ui_*` helper |
| `$this->include()` never takes data (CI4 ignores it) | `tools/check-templates.php`; use `view('partial', $data)` |
| No `alert`/`console`/`eval`/`innerHTML =` in JS | ESLint |
| WCAG 2.2 AA on every page, every role, light and dark | `tests/E2E/a11y.spec.ts` (axe-core). Automated axe catches only part of AA; keyboard and screen-reader checks stay manual |
| Layout, nav, seat map, dialogs, sign-in/out work in a real browser | `tests/E2E/flows.spec.ts` |
| Visual regressions | `tests/E2E/visual.spec.ts` — **report-only**: captures 3 widths × light/dark of every page as artifacts, never fails the build. Pixel baselines are not committed because renders differ across machines |

Nav highlighting (`ui_is_current`) matches exact paths for the `admin` and `portal` roots and prefixes for everything else; a prefix match on the roots marks Dashboard as current on every admin page (`NavigationTest` and the E2E sidebar test guard this).

The devcontainer mounts a named volume over `node_modules`. Sharing it with the host through the bind mount corrupted installs (Linux and macOS binaries, and stale bind-mount files).

## Architecture

- `app/Domain` — pure: `Money` (bcmath, 2dp strings), `QuarterCalendar`, `FlightPricing`.
- `app/Services` — `PointsService` (ledger), `PaymentService`, `MemberService`, `ReservationService`. Registered in `Config\Services`, used via `service('points')` etc. All multi-write operations run inside `Atomic::atomic()`.
- `app/Controllers/Admin/CrudController` — one generic CRUD for the nine fleet/pilot resources; a resource is a model + a `fields()` list. Routes are generated from one array in `Config/Routes.php`.
- Auth is Shield session auth, login by **email**. Groups: `admin`, `member`, `submember`. Registration and magic links are disabled; "remember me" is disabled.

## Domain rules and why

**Points are a signed ledger.** `points_ledger.amount` is signed (negative = spend); `balance_after` is stored per row; the balance is the last row. Legacy kept four overlapping quarter-bucket tables plus a `members_points` table fed by an unauthenticated `/cron` URL, and booking/refund labels were inverted between modules (`credit`/`Debit`). Here a payment credits points immediately, so there is nothing to "convert" and nothing to cron. Each post locks the user row (`SELECT … FOR UPDATE`) before reading the balance so concurrent bookings cannot overdraw. Overdraft is refused except the forced re-charge when a seat-joiner cancels (see below).

**Money is a string.** MySQL `DECIMAL(12,2)` ↔ PHP `string`, arithmetic through `Money` (bcmath). Never cast to float for arithmetic.

**Membership payments** (`PaymentService`):
- A member must pay the `joining` fee first; until then it is the only allowed type.
- Joining fee activates the membership and sets `next_due_on` to the 5th day after the end of the next calendar quarter (legacy rule: Jan–Mar → Apr 05, … Oct–Dec → Jan 05).
- Amounts for `joining`, `yearly`, `quarterly` are computed server-side from the member's stored fees; the client value is ignored (legacy trusted the browser). `bonus` and `points` take a free amount and credit points (legacy recorded bonus without crediting anything).
- `yearly`: 4 quarter rows, the whole yearly amount credited at once (legacy put it all in Q1). If a previous term exists the new term starts the day after it ends (prepay); otherwise on the current or next calendar quarter.
- `quarterly`: pays the next *n* open quarters (1–4), crediting `yearly_fee / 4` each, continuing the open term or opening a new one. Yearly is refused while a term is partly paid.
- `next_due_on` = end of last paid period + 5 days (`QuarterCalendar::GRACE_DAYS`).
- Only primary members (`members.plan_id IS NOT NULL`) are payable; sub-members have no fees and no points of their own — they see their primary member's account.

**Reservations** (`ReservationService`): price per seat = `air_routes.cost + aircrafts.total_cost`.
- `complete`: charges `seat price × capacity` and takes every seat.
- `partial`, first booker (flight owner): charged `seat price × capacity` up front for the whole aircraft, holds the chosen seats.
- `partial`, joiner: charged `seat price × seats`, status `pending`; the owner is refunded the same share immediately and the owner's reservation `amount` drops by it. The owner (or an admin) confirms.
- A flight is identified by `(aircraft_id, flight_date)`. `reservation_seats` has a unique key on `(aircraft_id, flight_date, seat_number)` so double booking is impossible even under concurrency; cancelling deletes the seat rows.
- Owner cancels → owner and every joiner are refunded their current amounts and all become `cancelled`. Joiner cancels → joiner refunded, owner re-charged the share with an **allowed overdraft** (a member must always be able to leave a flight, so the owner's balance may go negative). Legacy refunded both sides on a joiner cancel and never refunded joiners when the owner cancelled.
- Cancelled reservations are kept (status `cancelled`); legacy hard-deleted them. History also lives in `reservation_activities` and the ledger.
- Only active primary members can book; dates must be today or later; the route's aircraft must match. Editing a reservation is cancel + rebook (legacy edit double-charged).

**Sessions and Firebase Hosting.** Hosting forwards only the `__session` cookie to Cloud Run, so `Config\Session::$cookieName = '__session'`. Cloud Run is stateless and multi-instance, so sessions use `DatabaseHandler` (`ci_sessions`). CSRF protection is `session`-based (no CSRF cookie, which Hosting would strip). Remember-me is off for the same reason.

**Boot and migrations.** The production image runs `php spark migrate --all` before Caddy starts (same contract as `cms`), so `/health` only answers once the schema is current. Startup probe allows ~100 s.

**Config contract.** Same names as `cms`/`careerpost`: `DB_HOST/USER/PASS/NAME/SSL`, plus `APP_FULL_BASE_URL`, `COOKIE_SECURE`. `DB_SSL=1` enables TLS to HeatWave without certificate verification (`ssl_verify=false`), matching the existing apps.

**Static analysis level.** PHPStan runs at level 8 with zero errors and no baseline. Level 9 (`mixed` strictness) would flag every raw DB row access (`$row['id']`); getting there means row DTOs, which was judged not worth it yet. `app/Config` is excluded from PHPStan and Rector: the files are framework-shaped and Rector "simplifies" `ENVIRONMENT` checks into always-true branches that break the test DB group. The `ResultInterface|false` ignore exists because `DBDebug = true` throws on query failure, so `false` is unreachable.

**Tests.** `DatabaseTestCase` truncates every non-reference table in `setUp` (not a wrapping transaction: services own their transactions, and a wrapper would hide rollback bugs). Password hashing cost is 4 under `ENVIRONMENT=testing` (12 otherwise); with 12 the suite took over a minute.

## Legacy mapping and what was deliberately not ported

Ported: members/sub-members (ID formats `PO-/FO-/CO-NNN`, sub-members `{plan letter}{M|A|X}-NNN`), payments, points history/add/transfer, reservations (complete/partial/seat requests/confirm/cancel), pilots (+ documents, certifications, flight schedule), aircraft, types, maintenance, airports (server-computed `total_cost`), routes, countries/zones, demo dashboard.

Not ported: public self-registration wizard (needs mail infrastructure; admin creates members as in the legacy admin), friends/groups (template leftovers, unused by any flow), destinations-of-interest AJAX, round-trip booking (legacy `exit()`ed), the unauthenticated `/cron` endpoint, avatar uploads (so no bucket in Terraform), `pilots_assign` crew table.

## Security notes about the legacy app

The legacy `config/database.php` contained a live DB host and password in plaintext, and the CI `encryption_key` was committed. Both are treated as compromised: rotate that MySQL password and never reuse the key. The rebuild reads every secret from the environment / Secret Manager.
