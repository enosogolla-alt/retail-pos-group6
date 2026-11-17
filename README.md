# Retail POS

A web-based point-of-sale system for a small retail shop: product lookup by barcode or name, cart and checkout, Cash and M-Pesa payments, automatic stock deduction, printable receipts, low-stock alerts, sales reports and an audit trail.

Software Engineering · Group 6

## Team

| # | Member | Module | Role |
|---|---|---|---|
| M1 | Xavier Biko | 1. Authentication & Inventory Control | Interface |
| M2 | Kyle Tarus | 1. Authentication & Inventory Control | Logic |
| M3 | Stacy Kwamboka | 2. Sales & Checkout Terminal | Interface |
| M4 | Winston Enos | 2. Sales & Checkout Terminal | Logic |
| M5 | Andria Shikwe | 3. Stock Sync & Digital Receipts | Interface |
| M6 | David Muturi | 3. Stock Sync & Digital Receipts | Logic |

## Technology

PHP 8.2+, MySQL (InnoDB), HTML5, CSS3 and JavaScript, served by Apache through XAMPP. No frameworks. PHPUnit for unit tests.

## Requirements

- XAMPP with PHP 8.2 or newer
- Git
- Composer (for running tests)

## Getting started

1. Clone the repository into the XAMPP web folder:

   ```bash
   cd /c/xampp/htdocs
   git clone https://github.com/<owner>/retail-pos-group6.git
   cd retail-pos-group6
   ```

2. Create your local configuration:

   ```bash
   cp config/config.example.php config/config.php
   ```

   The defaults match a fresh XAMPP install (user `root`, empty password). `config/config.php` is never committed.

3. Start **Apache** and **MySQL** from the XAMPP Control Panel.

4. Create the database. In phpMyAdmin (`http://localhost/phpmyadmin`), open **Import** and run `database/schema.sql`, then `database/seed.sql`.

5. Open `http://localhost/retail-pos-group6/login` once the front controller (task 2.3) and sign-in page (task 1.1) are merged.

## Running tests

```bash
composer install
composer test
```

## Project structure

| Folder | Purpose |
|---|---|
| `config/` | Application settings and the database connection |
| `core/` | Router, authentication, CSRF protection and shared helpers |
| `controllers/` | Handle requests: check access, read input, call a service, return a page or JSON |
| `services/` | Business rules and database transactions |
| `models/` | Database access, one class per table, prepared statements only |
| `views/` | Page templates |
| `assets/` | Stylesheet, scripts and images |
| `database/` | Schema and seed data |
| `tests/` | PHPUnit tests |
| `docs/` | Project documentation |

## Workflow

- `main` is protected. All changes go through a pull request approved by one other member.
- Create one branch per task from an up-to-date `main`: `feature/<task>-<short-name>`, for example `feature/4.5-checkout-service`.
- Commit messages: `<task>: <summary>`, for example `2.6: add stock adjustment transaction`.
- Fill in the pull request template, including how the change was tested.
- Commit from your own GitHub account, with `git config user.email` set to your GitHub email.
