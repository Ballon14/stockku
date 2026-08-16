# StockKu - AI Agent Instructions (OpenCode)

This file contains the core context, technology stack, and architectural guidelines for AI coding agents contributing to the **StockKu** repository.

## 🎯 Project Overview
StockKu is a modern, responsive Web-based Point of Sale (POS) and Inventory Management System. It is designed to be visually premium, fast, and easy to use.

**Key Features:**
- **POS / Kasir**: Real-time cart management using Livewire.
- **Inventory Management**: Stock mutations, low-stock alerts.
- **Attendance**: Clock in/out and leave requests for employees.
- **Reporting**: Sales, Profit/Loss, Stock Mutations, and Attendance reports.
- **Role-Based Access Control**: Admin, Manager, Kasir, Karyawan.

## 🛠️ Technology Stack
- **Backend**: Laravel 12 (LTS) / PHP 8.2+
- **Frontend**: Blade + Tailwind CSS (v4 via CDN)
- **Interactivity**: Livewire 4 (No full page reloads for dynamic components)
- **Database**: MySQL
- **Authentication & Authorization**: Laravel Breeze + Spatie Laravel Permission
- **PDF Generation**: `barryvdh/laravel-dompdf`
- **Excel Export**: `openspout/openspout`

## 🧱 Data Integrity Rules (MUST follow)
- **Stock guards are server-side**: `SaleService::createSale()` re-fetches products with `lockForUpdate()` and validates `qty <= stok` inside `DB::transaction`. Never bypass with raw `decrement()`.
- **`StockService::recordMovement()` throws** when `type = 'out'` would drive stock below zero, and throws `InvalidArgumentException` for unknown types.
- **Returns are capped**: `sale_items.returned_qty` tracks cumulative returns; `SaleService::processReturn()` rejects over-return and sets sale status to `partial_return` / `returned`.
- **Money validation**: header `diskon` is clamped to `[0, subtotal]`, item discount cannot make item subtotal negative, and `bayar >= grand_total` is enforced in the service (never trust client-side checks).
- **Tests**: run `php artisan test` before finishing work on services. New behavior on sales/stock/returns must come with Feature tests under `tests/Feature/`.

## 🏗️ Architectural Guidelines
Please adhere to the following conventions when making changes or adding features:

### 1. Separation of Concerns (Service Pattern)
- Keep Controllers skinny. They should only handle HTTP requests, input validation, and returning views/responses.
- Complex business logic, database transactions, and data formatting MUST reside in `app/Services/` (e.g., `SaleService`, `StockService`, `ReportService`).
- Use `Form Requests` (`app/Http/Requests`) for all incoming data validation.

### 2. Livewire 3 Best Practices
- Use Livewire **only** when interactivity is required without reloading the page (e.g., POS terminal, search filters, dynamic charts).
- In Blade templates, always access component properties via `$this->propertyName` to avoid `PropertyNotFoundException` edge cases, especially when dealing with empty string inputs that are typed loosely.
- Use `wire:confirm` natively for sensitive actions (e.g., deleting data, processing payments).

### 3. Spatie Permissions
- In `bootstrap/app.php`, Spatie's middleware aliases are registered as:
  - `role`
  - `permission`
  - `role_or_permission`
- Use these directly in `routes/web.php` routes. Example: `->middleware(['auth', 'role:admin'])`
- Hide unauthorized UI elements using `@if($user->hasRole('admin'))` or `@role('admin')` in Blade views.

### 4. UI / UX & Aesthetics
- This app prioritizes a premium, modern aesthetic. Do NOT use generic red/blue/green colors. 
- Stick to the curated Tailwind color palettes used in the project:
  - Primary accents: `indigo-500`, `purple-600`
  - Success/Money: `emerald-500`, `teal-600`
  - Warnings: `amber-500`
  - Backgrounds: `slate-50`, `slate-900`
- Utilize soft shadows (`shadow-sm`, `shadow-lg shadow-indigo-500/30`), rounded corners (`rounded-xl`, `rounded-2xl`), and glassmorphism where appropriate.
- Ensure all forms and inputs look clean and responsive.

### 5. Error Handling & Transactions
- Always wrap complex database operations (e.g., Sales checkout, Stock deductions) inside `DB::transaction()`.
- Use `session()->flash()` for success/error messages, which are caught and displayed nicely by `flash-message` components in `layouts/app.blade.php`.

## 🚦 Important Notes for Agents
- The application uses `created_at` timestamps based on `Asia/Jakarta` (WIB) timezone as defined in `config/app.php`.
- Tailwind is compiled via Vite (`npm run build`); any change to `tailwind.config.js` or Blade class names requires rebuilding the assets before the UI updates.
- When generating new features, ensure migrations and seeders are created to supply dummy data for easier testing.

## 🔄 Development Workflow (MUST follow, point by point)
1. **Understand the task** — read the relevant files first, then implement changes following the conventions above.
2. **Verify changes** — run `php artisan test` for backend changes and `npm run build` for any Tailwind/Blade changes, then fix any errors found.
3. **Check the diff** — run `git status` and `git diff`; make sure no secrets (tokens, passwords, `.env`) are staged.
4. **Commit changes** — write a concise commit message in the repo's existing style (e.g., `git add -A && git commit -m "..."`).
5. **Push to GitHub** — ALWAYS push the commit to the `main` branch after finishing work: `git push origin main`. If authentication fails, notify the user that a manual push is required.
6. **Report back** — summarize what was changed point by point (file → what changed → why), and confirm the push status.
