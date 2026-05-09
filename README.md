# Kids Store — PHP / MySQL eCommerce

A complete, production-ready eCommerce website for **children's clothing &
baby products**, built with **pure PHP 8**, **MySQL**, **vanilla JavaScript**
and **HTML5 / CSS3** — no frameworks, no build step.

| | |
| --- | --- |
| Frontend | HTML5 · CSS3 (responsive, RTL-ready) · Vanilla JS |
| Backend  | PHP 8 (PDO, prepared statements) |
| Database | MySQL / MariaDB (utf8mb4) |
| Languages | English (LTR) + العربية (RTL) — full bilingual UI |
| License | MIT |

## ✨ Features

### Storefront
- Hero slider with auto-play, mega-menu navigation, sticky header
- 7 sections, 20+ categories, 24 sample products
- Product grid with hover effects, sale / featured badges, lazy-loaded images
- Full product page with **4-image gallery**, mouse-driven zoom, size & color
  pickers, quantity stepper, related products
- Search with **AJAX live suggestions**, sort, filter by price / category / on-sale
- Cart (works for both guests **and** logged-in users), wishlist, checkout
- COD / card / wallet payment selection
- User register / login / account page with order history
- Contact form, About page, social links in footer
- **Toast notifications** on every async action
- Fully responsive (mobile / tablet / desktop)
- Accessibility-friendly markup (landmarks, labels, focus states)

### Admin dashboard (`/admin`)
- Modern sidebar layout with logo / brand
- Live **statistics cards** + **canvas line chart** of last 14 days of sales
- Order status breakdown bars
- Full CRUD for: sections, categories, products, offers
- 4-image upload per product (with previews, primary image flag, delete)
- Toggle product **active** / **featured** instantly
- Orders manager with status workflow + line-item view
- Customer manager + new contact-message inbox
- Image-upload security (mime sniff, size cap, randomized name, no script execution in `/uploads/`)
- Pagination, search & filter on every list page

### Security
- PDO with **prepared statements everywhere** (no string interpolation)
- All output `htmlspecialchars()`-escaped via the `e()` helper (XSS-safe)
- **CSRF tokens** on every form and AJAX write
- Sessions configured with `httponly` + `samesite=lax`
- Bcrypt password hashing (`password_hash` / `password_verify`)
- File-upload validation by mime type + `getimagesize()`
- `.htaccess` blocks PHP execution inside `/uploads/`
- `.htaccess` blocks direct access to `/includes/` and `/database/`

---

## 📁 Project structure

```
kids-store/
├── admin/              # Admin panel (auth-protected)
│   ├── includes/       # Bootstrap + admin layout (header/footer)
│   ├── login.php       # Admin login
│   ├── index.php       # Dashboard
│   ├── sections.php
│   ├── categories.php
│   ├── products.php
│   ├── product_form.php
│   ├── orders.php
│   ├── users.php
│   ├── offers.php
│   └── messages.php
├── ajax/               # JSON endpoints (cart, wishlist, search…)
├── assets/
│   ├── css/{style.css, admin.css}
│   ├── js/{main.js, admin.js}
│   └── images/         # SVG placeholders + favicon
├── database/
│   ├── schema.sql      # CREATE TABLEs (10 tables)
│   └── seed.sql        # Dummy data: 7 sections, 20 categories,
│                       #             24 products w/ 4 images each
├── includes/           # Shared PHP bootstrap (db, auth, csrf, helpers)
│   ├── config.php      # ✏️  edit DB credentials here (or use .env)
│   ├── init.php
│   ├── db.php
│   ├── functions.php
│   ├── auth.php
│   ├── csrf.php
│   ├── header.php      # site header + nav + mega menu
│   ├── footer.php
│   ├── product_card.php
│   └── listing.php     # shop / section / category / search shared layout
├── uploads/            # Run-time uploads (htaccess-protected)
│   ├── products/  categories/  sections/  offers/
├── index.php           # Homepage
├── shop.php            # Generic listing
├── section.php         # /section.php?slug=baby-clothing
├── category.php        # /category.php?slug=baby-pajamas
├── product.php         # /product.php?slug=...
├── search.php
├── cart.php
├── wishlist.php
├── checkout.php
├── order_success.php
├── login.php · register.php · logout.php · account.php
├── about.php · contact.php
├── .htaccess           # security, caching
├── .env.example
└── README.md
```

---

## 🚀 Quick start (local development)

> Tested with **PHP 8.1+** and **MySQL 8 / MariaDB 10.4+**.

### 1. Clone / copy the project

```bash
git clone <repo-url> kids-store
cd kids-store
```

### 2. Create the database

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

This creates a database named **`kids_store`** with all tables and the
sample data.

### 3. Configure credentials

Either edit `includes/config.php` directly, **or** copy `.env.example` to a
real `.env` and load it via your web server / OS:

```env
DB_HOST=127.0.0.1
DB_NAME=kids_store
DB_USER=root
DB_PASS=
```

PHP reads `getenv('DB_*')` automatically.

### 4. Run a server

The simplest way is the PHP built-in server:

```bash
php -S localhost:8000
```

Then open:

| URL | Purpose |
| --- | --- |
| <http://localhost:8000>            | Storefront homepage |
| <http://localhost:8000/admin/>     | Admin panel (auto-redirects to login) |
| <http://localhost:8000/?lang=ar>   | Switch the storefront to Arabic / RTL |

For production, deploy under any LAMP/LEMP stack (Apache + mod_rewrite or
Nginx). Make sure `/uploads/` is **writable** by the PHP process.

### 5. Default credentials

| Role     | Username / Email      | Password      |
| -------- | --------------------- | ------------- |
| Admin    | `admin`               | `Admin@12345` |
| Customer | `user@example.com`    | `User@12345`  |

> Change them immediately for production: regenerate hashes with
> `php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_BCRYPT);"` and
> `UPDATE admins SET password_hash = '...' WHERE username='admin';`

---

## 🌐 Bilingual (English / Arabic)

Every text-bearing column has both `_en` and `_ar` variants, and the active
language is stored in the session. The storefront flips between LTR and RTL
automatically using the `dir` attribute on `<html>` and a `.rtl` body class.

To switch languages, click the language toggle in the top bar, or pass
`?lang=ar` / `?lang=en` once.

---

## 🧱 Database schema (10 tables)

`admins`, `users`, `sections`, `categories`, `products`, `product_images`,
`offers`, `orders`, `order_items`, `cart`, `wishlist`, `contact_messages`,
`settings`.

All foreign keys are `ON DELETE CASCADE` / `ON DELETE SET NULL` so deletes
cascade safely. The seed file provides 24 sample products with 4 images each
(SVG placeholders so the project ships without binary assets).

---

## 🛠 Customisation tips

- **Branding**: edit the SVG inside `includes/header.php` and the colours in
  the `:root` block of `assets/css/style.css`.
- **Currency / shipping**: change rows in the `settings` table or via
  the admin → it controls the "free shipping over X" banner and flat
  shipping fee used at checkout.
- **Adding payment gateways**: extend `checkout.php` and add the gateway
  redirect inside the `payment_method` switch.
- **Admin permissions**: the `admins` table supports multiple admins out of
  the box; just `INSERT` more rows with `password_hash`.

---

## 🔒 Production checklist

- [ ] Set `APP_DEBUG=0` so error messages aren't shown to users.
- [ ] Force HTTPS — uncomment the redirect in `.htaccess`.
- [ ] Change the default admin & demo customer passwords.
- [ ] `chmod 755` the project, `chmod 775` (or 770 with the right group) on
  `uploads/` so PHP can write but the world cannot.
- [ ] Enable backups of the `kids_store` database.
- [ ] Optionally configure a real SMTP service for contact / order emails.

---

## 📄 License

MIT — do anything you want, attribution appreciated.

Built with ❤️ for the next generation.
