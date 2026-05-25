# AGENTS.md - Sweets Website

This document is the working guide for agentic coding in this repo.
Follow it before making architectural or style decisions.

## Project overview
- Sweets Website is a PHP-based ecommerce site with a small custom service/repository layer.
- Pages are PHP templates that pull data from services and render HTML directly.
- Visual fidelity is a priority; keep the premium/luxury aesthetic intact.

## Tech stack
- Backend: PHP 7.4+ (procedural pages with light OOP services/repositories)
- Database: MySQL via PDO singleton
- Frontend: HTML5, vanilla JS, modular CSS
- UI libs: Bootstrap grid/utilities, Bootstrap Icons, Swiper

## Local setup
- Primary runtime is XAMPP/WAMP: http://localhost/Sweets-Website/
- Config and constants live in `config/config.php`.
- DB schema and seed data are in `database/*.sql`.

## Build, lint, and test commands
There is no build pipeline or automated test suite in this repo.
Use the following manual commands and checks:

### Run the site
- Use XAMPP Apache + MySQL and navigate to http://localhost/Sweets-Website/.
- Optional PHP built-in server:
  - `php -S localhost:8000 -t C:\xampp\htdocs\Sweets-Website`

### Lint
- PHP single file lint: `php -l path\to\file.php`
- There is no JS/CSS linter configured.

### Tests
- There is no PHPUnit/Jest suite.
- Single test equivalent: open the specific page in the browser
  - Example: http://localhost/Sweets-Website/cart.php

### Database setup
- Import schema: `mysql -u root -p sweets_db < database\schema.sql`
- Seed data is optional; see `database/seed*.sql`.

## Repository map
- `config/`: global constants, paths, DB singleton
- `services/`: business logic and fallbacks
- `repositories/`: raw SQL queries, PDO usage only
- `sections/`: reusable UI blocks included by pages
- `includes/`: header/footer and shared layout fragments
- `assets/`: CSS/JS/images
- `database/`: schema and seed files

## PHP conventions
- Indentation: 4 spaces, braces on the same line.
- Files start with a docblock header matching existing files.
- Classes are PascalCase in files named `ClassName.php`.
- Functions and variables are camelCase.
- Use `require_once` with `ROOT_PATH` or path constants.
- Prefer `declare(strict_types=1);` for new class/service files if safe.

### Imports and includes
- Pages usually start with `require_once 'config/config.php';`.
- Use `SERVICES_PATH` and `REPOS_PATH` for layer includes.
- Shared UI is pulled from `includes/` and `sections/`.

### Types
- Services and repositories use typed properties and return types.
- Use `?array` and `array` consistently based on nullability.
- Cast request inputs defensively (e.g., `(int)($_POST['quantity'] ?? 1)`).

### Error handling
- Use try/catch in services and log errors via `error_log`.
- Services should return fallbacks (empty arrays/null) rather than raw errors.
- Repositories should throw PDO exceptions and not swallow errors.

### Security
- Never place `$_GET`/`$_POST` directly in SQL.
- Use prepared statements with bound params.
- Escape output with `htmlspecialchars` when rendering user-controlled values.
- Session handling is centralized in `config/config.php` and `CartService`.

## Database and repository rules
- SQL lives only in `repositories/`.
- Use parameterized queries with named params.
- Table/column naming is `snake_case`.
- For numeric limits, use `bindValue` with `PDO::PARAM_INT`.

## Service layer rules
- Services are the only layer that knows about repositories.
- Provide fallback data when DB is unavailable (see `ProductService`).
- Keep business logic out of templates when possible.

## Page/template conventions
- Pages are mostly procedural PHP with embedded HTML.
- Use `BASE_URL` for asset URLs inside shared includes.
- Page-specific CSS is linked near the top of the page.
- Use Bootstrap grid classes (`container`, `row`, `col-*`).

## JavaScript conventions
- Use `DOMContentLoaded` as the main entry point.
- Prefer `const` and `let`, avoid globals.
- Keep feature code in `assets/js/sections/` or `assets/js/pages/`.
- Use the `initSwiper` helper from `assets/js/main.js`.
- Favor defensive checks: query elements, return early if missing.

## CSS conventions
- CSS is modular and imported in `assets/css/main.css`.
- Add new styles to a relevant file in `assets/css/sections/`, `pages/`, or `components/`.
- Use CSS variables from `assets/css/base/variables.css`.
- Follow existing BEM-like naming: `c-` components, `u-` utilities, `p-` pages.
- Keep typography consistent with the font tokens and `clamp()` where used.

## Visual and UX guidelines
- Maintain premium, warm color palette; avoid introducing clashing colors.
- Keep section backgrounds and reveal animations consistent with `main.css`.
- Ensure mobile layouts and breakpoints stay intact.

## Data fallbacks
- Several services return static arrays when DB is missing.
- Do not remove fallback data unless explicitly requested.
- Keep fallback image paths aligned with assets in `assets/images/`.

## Files to check when changing features
- Product data: `services/ProductService.php`, `repositories/ProductRepository.php`.
- Promotions: `services/PromotionService.php`, `repositories/PromotionRepository.php`.
- Header/footer: `includes/header.php`, `includes/footer.php`.
- Swiper or animations: `assets/js/main.js` and related section CSS.

## Cursor/Copilot rules
- No `.cursor/rules/` or `.cursorrules` found.
- No `.github/copilot-instructions.md` found.

## Verification checklist
- Run `php -l` on edited PHP files.
- Open the changed page in the browser and click through key interactions.
- Confirm layout on mobile and desktop breakpoints.
- Verify Swiper sliders and reveal animations still work.

## Updating this file
- Keep this guide up to date if new tools or scripts are added.
- When adding lint/test tools, include single-test commands.
