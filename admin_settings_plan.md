# Implementation Plan: Dynamic Settings Management System

This document outlines the architecture and implementation of the "Amazon/Flipkart Style" dynamic settings system for the Sweets Website admin panel.

## 1. Objective
To create a high-fidelity, production-grade settings management experience where:
- UI state is perfectly synchronized with the database.
- Changes are tracked in real-time without page reloads.
- Complex data types (text, toggles, images, colors) are handled via a unified API.
- Visual feedback (floating bars, toasts) mimics premium e-commerce platforms.

## 2. System Architecture

### 2.1 Backend Hydration (PHP)
Instead of hardcoded values, `admin/settings.php` uses a repository-to-template hydration pattern.
- **Service Layer**: `SettingService.php` fetches all key-value pairs from the `settings` table into a single `$settings` associative array.
- **Template Logic**: Every input uses null-coalescing operators to set initial values:
  - **Text/Number**: `value="<?= htmlspecialchars($settings['key'] ?? 'default') ?>"`
  - **Toggles**: `<?= ($settings['key'] ?? '1') == '1' ? 'checked' : '' ?>`
  - **Selects**: `<?= ($settings['key'] ?? '') == 'value' ? 'selected' : '' ?>`

### 2.2 Frontend State Engine (JavaScript)
Located in `assets/js/admin/pages/settings.js`, the engine manages the lifecycle of a change:
- **Initialization**: Captures the "Source of Truth" from the DOM immediately on load into `state.initialValues`.
- **Change Tracking**: Listens for `input` and `change` events across all `.form-control`, `.form-control-maroon`, and `.form-check-input` elements.
- **Diff Calculation**: Compares `currentValues` against `initialValues`. If a difference is detected:
  - The **Floating Action Bar** is revealed.
  - The **Unsaved Changes Chip** updates the field count.

### 2.3 File & Asset Persistence
- **State Tracking**: File inputs track the actual `File` object in the state.
- **FormData Integration**: The save process uses `FormData` instead of JSON to allow seamless multi-part uploads (Logos, Favicons, QR Codes) alongside text data.
- **FileService**: A dedicated PHP service handles secure storage in `assets/images/settings/` with MIME-type verification.

## 3. Key Components

| Component | Responsibility |
| :--- | :--- |
| `SettingRepository.php` | Raw SQL execution with `ON DUPLICATE KEY UPDATE` for atomic writes. |
| `SettingService.php` | Business logic, grouping settings, and providing fallbacks. |
| `settings.php` (API) | Unified endpoint for handling `multipart/form-data` requests. |
| `settings.js` | UI logic, state management, and real-time visual feedback. |

## 4. Business Logic Rules
1. **Persistence**: Every toggle must reflect its database state on refresh. No hardcoded "checked" attributes.
2. **Atomic Updates**: Settings are updated individually or in batches to prevent data loss.
3. **Security**: CSRF tokens must be included in the API request (Production Phase).
4. **Validation**: Numeric inputs (shipping rates, COD charges) are cast to integers/floats before storage.

## 5. Verification Checklist
- [x] Initial state hydration from DB.
- [x] Toggle state persistence (checked/unchecked).
- [x] Real-time "Unsaved Changes" bar.
- [x] Multi-file upload support (Store Logo, UI Logo, Favicon, QR).
- [x] Color palette synchronization (Hex code <-> Color picker).
- [x] Toast notifications for success/error feedback.
