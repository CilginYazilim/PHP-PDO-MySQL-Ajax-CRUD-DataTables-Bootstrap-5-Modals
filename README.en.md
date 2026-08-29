<div align="center">

# PHP PDO MySQL Ajax CRUD

### DataTables · Bootstrap 5 · Modals · Çılgın Yazılım Design System

**A secure, thoroughly commented CRUD example that works the moment you download it.**

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.2-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![DataTables](https://img.shields.io/badge/DataTables-1.13-0f5499?style=flat-square)](https://datatables.net)
[![License](https://img.shields.io/badge/License-MIT-16a34a?style=flat-square)](LICENSE)

[🇹🇷 Türkçe](README.md) &nbsp;·&nbsp; **🇬🇧 English**

[**▶ Live Demo**](https://cilginyazilim.com/kutuphane/php-pdo-ajax-crud/calistir) &nbsp;·&nbsp; [Source Library](https://cilginyazilim.com/kutuphane/php-pdo-ajax-crud) &nbsp;·&nbsp; [cilginyazilim.com](https://cilginyazilim.com)

</div>

---

<div align="center">

## Live Demo

**No installation, no sign-up, no download — try it in your browser in 3 seconds.**

<a href="https://cilginyazilim.com/kutuphane/php-pdo-ajax-crud/calistir"><img src="https://img.shields.io/badge/OPEN_LIVE_DEMO-0b5cb5?style=for-the-badge&logo=googlechrome&logoColor=white&labelColor=061321" alt="Open Live Demo" height="42"></a>
&nbsp;
<a href="https://cilginyazilim.com/kutuphane/php-pdo-ajax-crud"><img src="https://img.shields.io/badge/BROWSE_SOURCE-0ea5e9?style=for-the-badge&logo=readthedocs&logoColor=white&labelColor=061321" alt="Browse Source" height="42"></a>
&nbsp;
<a href="https://github.com/CilginYazilim/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals/archive/refs/heads/main.zip"><img src="https://img.shields.io/badge/DOWNLOAD_ZIP-16a34a?style=for-the-badge&logo=github&logoColor=white&labelColor=061321" alt="Download ZIP" height="42"></a>

<br><br>

<a href="https://cilginyazilim.com/kutuphane/php-pdo-ajax-crud/calistir" title="Click to open the live demo">
  <img src="docs/screenshots/01-liste.png" alt="PHP PDO Ajax CRUD live demo preview" width="860">
</a>

<sub>▲ Click the screenshot to open the demo</sub>

</div>

<br>

### What to try in the first 60 seconds

| # | Try this | What happens behind the scenes |
|:-:|----------|--------------------------------|
| **1** | Type `a` into the search box | The **server** filters, not the browser: a `LIKE` query with `%` and `_` escaped, and `recordsFiltered` recomputed |
| **2** | Click the "Name" column header | The sort column goes through a **whitelist** — a client-supplied column name never reaches the query |
| **3** | Jump to page 2 | `LIMIT/OFFSET` runs on the server; only that page's 10 rows are sent to the browser |
| **4** | Press the 👁 **eye** button | `action=fetch` returns **raw JSON**, not HTML; the UI is filled with `.text()` → XSS is impossible |
| **5** | **New Record** → submit with empty fields | The server returns **HTTP 422** plus a field-keyed `errors` object, rendered under the matching input |
| **6** | Enter `<script>alert(1)</script>` as a name | Rejected by validation; even if stored, the list escapes every value through `e()` |
| **7** | Pick an image | Live preview **before** upload; on the server the type is verified from the **file contents** via `getimagesize()` |
| **8** | Rename a `.php` file to `.png` and upload it | Rejected — content is checked, not the extension, and `upload/.htaccess` disables PHP in that folder |
| **9** | Hit 🗑 **Delete** and confirm | The row **and** the image on disk go together; the filename is read from the **database**, never from the client |
| **10** | Switch your OS to dark mode | The UI follows **automatically** — pure CSS, not a single line of JS |

> **Tip:** Keep **F12 → Network** open while you use the demo. You can watch the `action` and `csrf_token` fields going to `ajax.php`, the JSON coming back, and the status codes (200 / 419 / 422) live. It is by far the fastest way to learn this stack.

### About the demo environment

| Topic | Status |
|-------|--------|
| **Data** | The **50 sample records** from `crud.sql`. No real personal data. |
| **Reset** | The demo database is **reset periodically**; records you add are not permanent. |
| **Authentication** | **None.** That is deliberate — the example focuses on CRUD and the security layer. Always add a login system in your own project (see [Going to Production](#going-to-production)). |
| **Upload limit** | **2 MB** per image; `jpg`, `png`, `gif`, `webp` only. |
| **`APP_DEBUG`** | **`false`** in the demo, exactly as it should be in production. Error details go to the log, not the screen. |
| **Dependencies** | **Zero.** No Composer, no npm, no CDN. The demo runs identically on an offline server. |

> If the demo is temporarily down, no problem: cloning the repo and importing `crud.sql` gets you the same screen locally in **two minutes** → [Installation](#installation)

---

## What Is This?

Search "PHP CRUD example" and most results share the same three mistakes: queries built by concatenating `$_POST`, output printed without escaping, uploads trusted by their extension. A beginner copies that code — and learns the mistake along with it.

This project exists to break that cycle: it's **the same CRUD, at the same level of simplicity, but actually written securely**. The difference isn't the line count, it's *why* each line is written the way it is. Why `getimagesize()` beats `explode('.', $name)`, what `EMULATE_PREPARES = false` actually changes, why a CSRF token is worthless without `hash_equals()` — all of it is explained right where it happens, inline in the comments. No separate book to read; just open the file.

> **Note:** The inline code comments are written in **Turkish**, since that is the primary audience of this teaching example. The code itself, variable names and this documentation are in English. Pull requests adding English comment translations are welcome.

**Who is this for?**

- Anyone learning the PHP + AJAX + DataTables combination **the right way**
- Anyone who needs a ready, secure CRUD skeleton for their own project
- Anyone looking for a reusable design system built on top of Bootstrap 5
- Anyone curious how mobile-friendly, accessible, and dependency-free a CRUD can actually be

> **Clone it, import `crud.sql`, run it.** There are no other setup steps. No Composer, no npm, not even an internet connection — every library ships inside the project. Want to try it with zero setup first? See [Live Demo](#live-demo).

This project is part of the **[Çılgın Yazılım Library](https://cilginyazilim.com/kutuphane)** — a collection of commented, production-ready examples built on the same design system. Check it out for more.

---

## Table of Contents

- [Live Demo](#live-demo)
- [Screenshots](#screenshots)
- [Features](#features)
- [Security: What Was Fixed, and How](#security-what-was-fixed-and-how)
- [Installation](#installation)
- [Configuration](#configuration)
- [Çılgın Yazılım Design System](#çılgın-yazılım-design-system)
- [Project Structure](#project-structure)
- [How It Works](#how-it-works)
- [AJAX API Reference](#ajax-api-reference)
- [Database Schema](#database-schema)
- [FAQ](#faq)
- [Going to Production](#going-to-production)
- [Troubleshooting](#troubleshooting)
- [Roadmap](#roadmap)
- [Contributing](#contributing)
- [License](#license)

---

## Screenshots

### Record list

Gradient brand header, live search, sortable columns and action buttons grouped into a single column. Records without an image get an automatically generated initial badge.

![Record list](docs/screenshots/01-liste.png)

### Detail modal

Opens when the eye button is clicked. Shows a large profile image, the record's fields, and a shortcut to jump straight into editing.

![Detail modal](docs/screenshots/02-detay-modali.png)

### Create / edit form

A single modal serves both creating and editing. Selecting an image shows a live preview, and validation errors appear directly under the relevant field.

![Form modal](docs/screenshots/03-form-modali.png)

### Server-side pagination

Pagination, search and sorting all happen on the server; only the visible page is ever sent to the browser.

![Pagination](docs/screenshots/04-sayfalama.png)

### Search

Searches across first name and last name. Because `recordsFiltered` is calculated correctly, pagination adapts to the filter.

![Search](docs/screenshots/05-arama.png)

**Three modals:**

| Modal | Opened by | Contents |
|-------|-----------|----------|
| 👁 **Detail** | Eye button | Large profile image, record ID, first name, last name, file name, date + "Edit This Record" shortcut |
| ✎ **Create / Edit** | New Record or pencil button | Form, live image preview, per-field error messages |
| 🗑 **Delete** | Trash button | Confirmation screen naming the record being deleted |

---

## Features

<table>
<tr><td width="50%" valign="top">

**Interface**
- Gradient brand header and modals
- Three separate modals (detail / form / delete confirmation)
- Toast notifications in the top-right corner
- Live image preview (before uploading)
- Initial badge for records without an image
- **Automatic dark mode** (follows the OS setting)
- **Tuned specifically for mobile** — ≥40px touch targets, table never forces horizontal scrolling, accessible (ARIA labelled)
- Fully localised in Turkish — no CDN, works offline

</td><td width="50%" valign="top">

**Architecture**
- Server-side DataTables processing
- Single AJAX endpoint with `action`-based routing
- Two-layer validation (client + server)
- Per-field error messages (HTTP 422)
- Automatic file cleanup (never leaves orphans)
- Environment variable support
- 50 ready-to-use sample records
- **Every line of code commented**

</td></tr>
</table>

---

## Security: What Was Fixed, and How

The vulnerabilities found in most similar examples, and how this project prevents them:

| Vulnerability | Typical broken code | In this project |
|---------------|---------------------|-----------------|
| **SQL Injection** | `"SELECT * FROM users WHERE id = '".$_POST['id']."'"` | Every query uses prepared statements. `EMULATE_PREPARES = false` forces genuine server-side prepares. The sort column and direction go through a **whitelist** — an `order[0][column]=0;DROP TABLE users--` attempt was tested and has no effect. |
| **XSS** | `$sub_array[] = $row["name"];` | Every value coming from the database is escaped with `e()` (htmlspecialchars). Toast messages are written with `.text()`, never `.html()`. |
| **CSRF** | *(usually absent entirely)* | A 32-byte session-bound token. Read from a `<meta>` tag and attached to **every** AJAX request, verified in constant time with `hash_equals()`. A request without a token returns **HTTP 419**. |
| **Malicious file upload** | `$ext = explode('.', $name)[1];` | The extension is never trusted: the type is detected from the **file's actual content** via `getimagesize()`. The new name is generated with `random_bytes()` and the extension is assigned from a MIME whitelist. A `shell.php.png` upload attempt was tested and rejected. |
| **Code execution in the upload folder** | *(unprotected folder)* | `upload/.htaccess` disables the PHP engine and denies access to executable extensions. |
| **Path traversal** | `unlink("../upload/".$_POST['hidden_image'])` | The file name to delete is read **from the database, not the client**, and additionally sanitised with `basename()`. |
| **Type confusion** | `WHERE id = '$id'` | IDs are validated with `filter_input(..., FILTER_VALIDATE_INT)`. |
| **Information disclosure** | MySQL errors printed to screen | With `APP_DEBUG = false`, error details are hidden and written to `error_log()` instead. |
| **LIKE wildcards** | `LIKE '%$search%'` | `%`, `_` and `\` in the search term are escaped — typing `%` no longer returns the entire table. |
| **Resource exhaustion** | `LIMIT $length` | Page size is capped at 500; `length=999999` cannot exhaust the server. |

---

## Installation

> Just want to see it? No installation needed → [**open the Live Demo**](https://cilginyazilim.com/kutuphane/php-pdo-ajax-crud/calistir). The steps below are for running it on your own machine (~2 minutes).

### Requirements

- PHP **8.0+** (with the `pdo_mysql` and `gd` extensions)
- MySQL **5.7+** or MariaDB **10.3+**
- Apache (XAMPP / WAMP / Laragon) — or PHP's built-in server

### Steps

**1 — Download the project**

```bash
git clone https://github.com/CilginYazilim/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals.git
cd PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals
```

**2 — Create the database**

`crud.sql` creates the database itself; you do not need to create a `crud` database beforehand.

```bash
mysql -u root -p < crud.sql
```

Using phpMyAdmin: **Import → Choose file → `crud.sql` → Go**

**3 — Run it**

```bash
php -S 127.0.0.1:8000
```

If you use XAMPP, drop the project into `htdocs` and open:
`http://localhost/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals/`

**4 — Open it in a browser** → `http://127.0.0.1:8000/`

You will be greeted by a working table populated with **50 sample records**.

> **Linux/macOS users:** the `upload/` folder needs write permission → `chmod 755 upload`

---

## Configuration

All settings live in [system/config.php](system/config.php), each with an explanatory comment:

| Constant | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `127.0.0.1` | Database host |
| `DB_NAME` | `crud` | Database name |
| `DB_USER` | `root` | Username |
| `DB_PASS` | *(empty)* | Password |
| `UPLOAD_MAX_BYTES` | `2 MB` | Maximum image size |
| `ALLOWED_IMAGE_TYPES` | jpg, png, gif, webp | Permitted MIME types |
| `NAME_MIN_LENGTH` / `NAME_MAX_LENGTH` | `2` / `150` | Name length limits |
| `APP_DEBUG` | `true` | **Set to `false` in production** |

### Do not hardcode your password

Every `DB_*` constant can be overridden by an environment variable, so your password never reaches GitHub:

```bash
# Linux / macOS
export DB_HOST=localhost DB_USER=appuser DB_PASS='strong-password'

# Windows (PowerShell)
$env:DB_USER = "appuser"; $env:DB_PASS = "strong-password"
```

For Apache, in `.htaccess` or `httpd.conf`: `SetEnv DB_PASS "strong-password"`

---

## Çılgın Yazılım Design System

[assets/css/cilginyazilim.css](assets/css/cilginyazilim.css) belongs to the **brand**, not to this project. It is kept as a separate file so the same visual language can be reused across other example projects.

### Using it in another project

```html
<!-- 1) Include it AFTER Bootstrap -->
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/cilginyazilim.css">

<!-- 2) Add the theme class to body -->
<body class="cy-app">
```

### Available components

| Class | Purpose |
|-------|---------|
| `.cy-card` / `.cy-card__header` / `.cy-card__body` / `.cy-card__footer` | Main card with gradient header |
| `.cy-brand` / `.cy-brand__mark` | Logo box + title block |
| `.cy-btn` + `.cy-btn--primary` \| `--onbrand` \| `--glass` | Brand buttons |
| `.cy-btn-icon` + `--view` \| `--edit` \| `--delete` | In-table icon buttons |
| `.cy-table` | Branded table |
| `.cy-avatar` / `.cy-avatar--initial` / `.cy-avatar--lg` | Profile image and initial badge |
| `.cy-badge` + `--glass` \| `--soft` | Badges |
| `.cy-modal` | Modal with gradient header |
| `.cy-detail` | Label/value list (detail modal) |
| `.cy-toast` + `--success` \| `--danger` \| `--info` | Notification toasts |

### Changing the colours

Every component is driven by CSS custom properties. Changing **one place** is enough:

```css
:root {
    --cy-brand-900: #061321;   /* Darkest navy from the logo */
    --cy-brand-600: #0b5cb5;   /* Primary brand blue         */
    --cy-accent:    #0ea5e9;   /* Accent colour              */
    --cy-gradient:  linear-gradient(135deg, #061321, #0b5cb5 45%, #0284c7);
}
```

> The palette is derived from the [cilginyazilim.com](https://cilginyazilim.com) logo: a sweep from deep navy to vivid blue. The logo lives in `assets/images/logo.png` and sits on a white circular chip in the header.

### Dark mode

Activates **automatically** when the visitor's operating system is set to dark mode. To force it:

```html
<html data-cy-theme="dark">   <!-- or "light" -->
```

---

## Project Structure

```
.
├── index.php                      # Interface + all JavaScript logic
├── crud.sql                       # Database schema + 50 sample records
├── README.md                      # Turkish documentation
├── README.en.md                   # English documentation
├── LICENSE                        # MIT license
├── .gitignore
│
├── docs/
│   └── screenshots/               # Screenshots used in the READMEs
│
├── system/
│   ├── config.php                 # Settings, session, PDO connection
│   ├── function.php               # Helper functions
│   └── ajax.php                   # AJAX endpoint / CRUD router
│
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── dataTables.bootstrap5.min.css
│   │   ├── cilginyazilim.css      # ★ BRAND DESIGN SYSTEM
│   │   └── style.css              # Page-specific tweaks only
│   ├── js/
│   │   ├── jquery-3.7.0.js
│   │   ├── bootstrap.bundle.js
│   │   ├── jquery.dataTables.min.js
│   │   └── dataTables.bootstrap5.min.js
│   └── images/
│       └── logo.png               # Brand logo
│
└── upload/
    ├── .htaccess                  # Blocks code execution in this folder
    └── *.png                      # Sample images
```

**Load order matters:**

```
CSS:  bootstrap → dataTables → cilginyazilim → style
JS:   jQuery → bootstrap.bundle → dataTables → dataTables.bootstrap5
```

Getting this wrong produces errors such as `$ is not defined`.

---

## How It Works

```
┌─────────────────────────────────────────────────────────────────────┐
│  BROWSER  (index.php)                                               │
│                                                                      │
│  DataTables ──┐                                                      │
│  Form submit ─┤                                                      │
│  Detail button┼──► jQuery AJAX ──► POST { action, csrf_token, ... }  │
│  Delete button┘                              │                       │
└──────────────────────────────────────────────┼───────────────────────┘
                                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│  SERVER  (system/ajax.php)                                          │
│                                                                      │
│   1. Is it POST?           → if not, 405                            │
│   2. require_csrf()        → if invalid, 419                        │
│   3. dispatch on action:   list │ add │ edit │ fetch │ delete       │
│   4. validate_name()       → if invalid, 422 + errors               │
│   5. upload_image()        → type detected from file content        │
│   6. PDO prepared query    → MySQL                                  │
│   7. json_response()       → single JSON exit point                 │
│                                                                      │
│   All of this runs inside try/catch: no raw PHP error ever leaks,   │
│   everything is converted into well-formed JSON.                    │
└─────────────────────────────────────────────────────────────────────┘
```

### Separation of concerns

| File | Responsibility |
|------|----------------|
| **[index.php](index.php)** | Presentation only. Generates the CSRF token, sets up DataTables, manages modals and toasts. Never touches the database. |
| **[system/ajax.php](system/ajax.php)** | The router. Dispatches to handlers based on `action`. All security checks and error handling live here, in one place. |
| **[system/function.php](system/function.php)** | Pure helper functions. Those needing the database receive the PDO instance **as a parameter** — no new connection per call. |
| **[system/config.php](system/config.php)** | Session, constants and a single PDO instance. |

### Concepts worth studying

Explained in detail in the code comments — these are the things beginners most often get stuck on:

- **What does `serverSide: true` mean?** — It makes no difference with 50 rows, but it stops you from shipping all 100,000 rows to the browser.
- **Event delegation** — why `$('.js-edit').click()` fails for buttons added via AJAX, and why `$('#user_data').on('click', '.js-edit', ...)` works.
- **The `EMULATE_PREPARES = false` trap** — why the same named placeholder (`:search`) cannot be used twice, and where `Invalid parameter number` comes from.
- **`contentType: false, processData: false`** — why both are mandatory when uploading files.
- **Why a column name cannot be bound** — and therefore why a whitelist is mandatory.

---

## AJAX API Reference

All requests are `POST`ed to [system/ajax.php](system/ajax.php) and must include a valid `csrf_token`. Responses are `application/json`.

<details>
<summary><b><code>action=list</code></b> — DataTables listing</summary>

**Request:** `draw`, `start`, `length`, `search[value]`, `order[0][column]`, `order[0][dir]`

**Response:**
```json
{
  "draw": 1,
  "recordsTotal": 50,
  "recordsFiltered": 50,
  "data": [[50, "<img …>", "Ozan", "TOPAL", "<span…>23.02.2025 23:28</span>", "<div class=\"cy-actions\">…</div>"]]
}
```

Sortable columns are restricted by a whitelist: `0 → id`, `2 → name`, `3 → surname`, `4 → tarih`.
The Photo (1) and Actions (5) columns are not sortable because they are not database columns.
</details>

<details>
<summary><b><code>action=add</code></b> — Create</summary>

**Request:** `name`, `surname`, `image_user` *(optional, multipart/form-data)*

**Success (200):**
```json
{ "success": true, "type": "success", "description": "Kayıt başarıyla eklendi.", "id": 51 }
```

**Validation error (422):**
```json
{
  "success": false,
  "type": "danger",
  "description": "Lütfen formdaki hataları düzeltin.",
  "errors": { "name": "Ad alanı boş bırakılamaz." }
}
```
The keys of the `errors` object match the form field `id`s exactly, so JavaScript can write each message directly beneath its field.
</details>

<details>
<summary><b><code>action=edit</code></b> — Update</summary>

**Request:** `user_id`, `name`, `surname`, `image_user` *(optional)*

If no new image is sent, the existing one is kept. If one is sent, it replaces the old file and **the old file is deleted from disk**.
</details>

<details>
<summary><b><code>action=fetch</code></b> — Single record (detail + edit)</summary>

**Request:** `id`

```json
{
  "success": true,
  "id": 1,
  "name": "Evren",
  "surname": "ÇILGIN",
  "image": "2090273627.png",
  "image_url": "upload/2090273627.png",
  "tarih": "06.01.2025 19:34"
}
```
Returns **raw data**, not pre-rendered HTML. Because JavaScript fills the screen using `.text()`, there is no XSS surface.
</details>

<details>
<summary><b><code>action=delete</code></b> — Delete</summary>

**Request:** `id`

The record and its associated image file are removed together. Returns `404` if the record does not exist.
</details>

### HTTP status codes

| Code | Meaning |
|------|---------|
| `200` | Success |
| `400` | Invalid parameter (e.g. malformed ID) |
| `404` | Record not found |
| `405` | Non-POST request |
| `419` | Invalid CSRF token or expired session |
| `422` | Validation error (includes an `errors` object) |
| `500` | Server / database error |

---

## Database Schema

```sql
CREATE TABLE `users` (
  `id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`    VARCHAR(150) NOT NULL,
  `surname` VARCHAR(150) NOT NULL,
  `image`   VARCHAR(191) NOT NULL DEFAULT '',   -- file name only, not a full path
  `tarih`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_users_name`    (`name`),             -- for search and sorting
  KEY `idx_users_surname` (`surname`),
  KEY `idx_users_tarih`   (`tarih`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> **Note:** the `tarih` column name is Turkish for "date". It is kept as-is for backward compatibility with the original example.

| Decision | Reason |
|----------|--------|
| **InnoDB** (not MyISAM) | Transaction and foreign key support, row-level locking |
| **utf8mb4** (not utf8mb3) | Full Unicode incl. emoji; the legacy `utf8` cannot store some characters |
| **Indexes** | On the columns used for search/sort; the gain compounds as the table grows |
| **File name only** | Changing the folder layout later requires no database migration |

To upgrade an existing (older) installation **without losing data**, use the `ALTER TABLE` statements at the end of `crud.sql`.

---

## FAQ

<details>
<summary><b>Can I use this code in my own project?</b></summary>

Yes. It is MIT licensed — you may use, modify and distribute it freely, including in commercial projects. Attribution is not required, but always appreciated.
</details>

<details>
<summary><b>I want to add a new column. What do I need to change?</b></summary>

Example: to add an `email` column, five places:

1. `crud.sql` → add the column to the table
2. `index.php` → add an input to the form and a `<th>` to the `<thead>`
3. `system/function.php` → add it to the `SELECT` list in `find_user()`
4. `system/ajax.php` → validation + `INSERT`/`UPDATE` in `handle_save()`, and a new cell in the `$data[]` array in `handle_list()`
5. `system/ajax.php` → if you inserted a column, remember to shift the `$sortableColumns` indexes

The number of `<th>` elements **must** equal the length of each `$data[]` row, otherwise DataTables throws an error.
</details>

<details>
<summary><b>How do I raise the image size limit?</b></summary>

Change `UPLOAD_MAX_BYTES` in `system/config.php`. Also raise `upload_max_filesize` and `post_max_size` in `php.ini` — if PHP's limit is lower than yours, the file never reaches your code.
</details>

<details>
<summary><b>Why is there validation on both the client and the server?</b></summary>

Client-side validation is for **user experience**: instant feedback without a round trip. But JavaScript can be disabled, and requests can be sent directly with `curl`. So the **real protection is always server-side**. You need both.
</details>

<details>
<summary><b>Can I turn off DataTables' `serverSide: true`?</b></summary>

You can, but then every record is sent to the browser at once. That is fine for 50 rows; at 50,000 the page will freeze. It is left enabled here to demonstrate the real-world scenario.
</details>

<details>
<summary><b>Can I upgrade the Bootstrap 5 version?</b></summary>

Yes, just replace the files under `assets/`. The design system layers on top of Bootstrap rather than modifying it, so version upgrades are painless.
</details>

<details>
<summary><b>The UI and code comments are in Turkish. Can I use it in English?</b></summary>

Absolutely. The interface strings live in two places: the HTML in `index.php` and the DataTables `language` block in the same file. Server-side messages are in `system/function.php` and `system/ajax.php`. Search-and-replace is straightforward, and a pull request adding proper i18n is very welcome.
</details>

---

## Going to Production

- [ ] Set `APP_DEBUG` to **`false`**
- [ ] Create a **least-privilege** database user instead of using `root`
- [ ] Provide credentials via **environment variables**, never hardcoded
- [ ] Use **HTTPS**; set `session.cookie_secure = 1` and `session.cookie_httponly = 1`
- [ ] If you run Nginx, `.htaccess` is ignored — disable PHP in the upload folder via server config:
  ```nginx
  location ^~ /upload/ {
      location ~ \.php$ { deny all; }
  }
  ```
- [ ] **Back up** the `upload/` folder regularly
- [ ] Add authentication — this example has **no login system**; anyone can edit every record

---

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| **"Veritabanına bağlanılamadı"** (cannot connect) | MySQL is not running, or the `DB_*` values are wrong. Start MySQL from the XAMPP panel. |
| **Table empty, stuck on "Yükleniyor…"** | Open the browser console (F12). Usually `system/ajax.php` is returning a PHP error; set `APP_DEBUG = true` and read the response in the Network tab. |
| **HTTP 419** | The session expired — reload the page. `session.save_path` must be writable on the server. |
| **`Invalid parameter number`** | You used the same named placeholder twice in one query. With `EMULATE_PREPARES = false` this is not allowed; use distinct names. |
| **`$ is not defined`** | The JavaScript load order is wrong. jQuery must **always** come first. |
| **"Görsel kaydedilemedi"** (cannot save image) | The `upload/` folder is missing or not writable → `chmod 755 upload` |
| **Turkish characters are mangled** | The database is not utf8mb4. Run the `CONVERT TO CHARACTER SET utf8mb4` statement at the end of `crud.sql`. |
| **Large files will not upload** | Raise `upload_max_filesize` and `post_max_size` in `php.ini`. |
| **DataTables "Requested unknown parameter"** | The number of `<th>` elements does not match the array length returned by the server. Make them equal. |

---

## Roadmap

- [ ] User login and role-based authorisation
- [ ] Bulk delete (multi-select with checkboxes)
- [ ] Excel / CSV / PDF export (DataTables Buttons)
- [ ] Server-side image resizing and thumbnails
- [ ] REST API layer (with JWT)
- [ ] Soft delete + audit log
- [ ] Unit tests with PHPUnit
- [ ] Manual dark mode toggle
- [ ] English translation of the inline code comments

---

## Contributing

**This project is open to everyone — contributions of any kind are welcome.**

📦 **Repository:** [github.com/CilginYazilim/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals](https://github.com/CilginYazilim/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals)

| How to contribute | Where |
|-------------------|-------|
| 🐛 Report a bug | [Issues](https://github.com/CilginYazilim/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals/issues) |
| 💡 Suggest a feature | [Issues](https://github.com/CilginYazilim/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals/issues) |
| 🔧 Submit code | [Pull Requests](https://github.com/CilginYazilim/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals/pulls) |
| ❓ Ask a question | [Discussions](https://github.com/CilginYazilim/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals/discussions) |

### Pull request steps

```bash
# 1) Fork the repo on GitHub, then clone your copy
git clone https://github.com/YOUR-USERNAME/PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals.git
cd PHP-PDO-MySQL-Ajax-CRUD-DataTables-Bootstrap-5-Modals

# 2) Create a branch for your change
git checkout -b feature/my-feature

# 3) Write your code, then commit
git add .
git commit -m "Add: a short, descriptive title"

# 4) Push to your fork
git push origin feature/my-feature

# 5) Click "Compare & pull request" on GitHub
```

### Contribution guidelines

- **Comment your code.** Teaching is the whole point of this project; uncommented pull requests will be sent back.
- **Do not skip the security checks.** Prepared statements, `e()` escaping and `require_csrf()` must be present in every new operation too.
- **Make design changes in `cilginyazilim.css`**, never with inline `style="..."`.
- **When adding a column**, keep the `<th>` count and the `$data[]` length in sync.
- Open an issue before adding a new third-party library — the project is deliberately dependency-free.

---

## License

[MIT](LICENSE) — free for any use, including commercial.

<div align="center">

### Try it first

<a href="https://cilginyazilim.com/kutuphane/php-pdo-ajax-crud/calistir"><img src="https://img.shields.io/badge/OPEN_LIVE_DEMO-0b5cb5?style=for-the-badge&logo=googlechrome&logoColor=white&labelColor=061321" alt="Open Live Demo" height="42"></a>
&nbsp;
<a href="https://cilginyazilim.com/kutuphane"><img src="https://img.shields.io/badge/MORE_EXAMPLES-061321?style=for-the-badge&logo=bookstack&logoColor=white&labelColor=061321" alt="More Examples" height="42"></a>

Built with ❤ by **[cilginyazilim.com](https://cilginyazilim.com)**

If you found this useful, please leave a ⭐.

</div>
