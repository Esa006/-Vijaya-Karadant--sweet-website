# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

- **Build the project**: None (PHP backend + Vanilla JS/CSS frontend)
- **Lint PHP files**: `php -l path\to\file.php`
- **Run the site (XAMPP)**: Ensure Apache/MySQL are running and visit `http://localhost/Sweets-Website/`
- **Run the site (PHP Server)**: `php -S localhost:8000 -t C:\xampp\htdocs\Sweets-Website`
- **Manual Verification**: Open specific page in browser (e.g., `http://localhost/Sweets-Website/cart.php`)

## High-Level Code Architecture

Sweets Website is a PHP-based ecommerce platform focusing on a premium/luxury aesthetic.
- **Backend**: Procedural PHP pages (`index.php`, `cart.php`, etc.) that act as controllers.
- **Service Layer (`services/`)**: Contains business logic (`ProductService`, `CartService`, etc.) and handles fallback data if the database is disconnected.
- **Repository Layer (`repositories/`)**: Handles raw SQL queries using PDO.
- **Data Access**: Centralized DB singleton in `config/config.php`.
- **Frontend**: Modular CSS in `assets/css/`, Vanilla JS in `assets/js/`, and Bootstrap grid/utilities for layout.
- **Reusable UI**: Fragments stored in `includes/` (header/footer) and `sections/` (components like product sliders).

## Code Style & Conventions

- **PHP**: 4 spaces, camelCase functions/variables, PascalCase classes. Strict types for new files. Prepared statements for all SQL.
- **JS**: Modular code wrapped in `DOMContentLoaded`, no globals, uses `initSwiper` from `main.js`.
- **CSS**: BEM-like naming (`c-` components, `u-` utilities), standard variables from `base/variables.css`.
- **Database**: `snake_case` table and column names. SQL stays only in `repositories/`.
- **Security**: Escape output with `htmlspecialchars`.

## Visual & UX Guidelines

- **Premium Luxury**: Maintain warm, high-end color palette and high-fidelity animations.
- **Mobile First**: All pages must be responsive down to 320px.
- **Micro-animations**: Use smooth reveal animations and transitions defined in `main.css`.
