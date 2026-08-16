# E-commerce Project

PHP / MySQL e-commerce site (customer + admin) built on XAMPP/LAMPP.

## Setup
1. Copy this folder to `/opt/lampp/htdocs/ecommerce`.
2. Start Apache + MySQL from the LAMPP control panel (or `sudo /opt/lampp/lampp start`).
3. In phpMyAdmin: Import `database/ecommerce.sql`, then Import `database/seed.sql`.
4. Visit `http://localhost/ecommerce/`.
5. Log in via the "Sign In" button — admin: `admin@example.com` / `admin123`, demo customer: `customer@example.com` / `admin123`. Change these after your first login.
6. **For real email delivery** (password reset, order confirmations): edit `config/mail.php` with real SMTP credentials. Until you do, password reset falls back to showing the link on screen so it stays testable.

`config/database.php` already matches LAMPP's defaults (`root` user, no password).

## Catalog
12 categories (Tech & Gadgets, Fashion & Apparel, Home & Living, Beauty & Wellness,
Sports & Fitness, Entertainment & Media, Office Essentials, Health & Nutrition,
Auto & Vehicles, Pets & Animals, Garden & Outdoor, Food & Beverages), 120 products
(10 per category), each with a unique description and a unique generated image
(`uploads/products/seed-*.svg`) in the site's visual style. ~18% are marked with a
sale price, feeding the Deals page.

## What's included
- **Customer**: browse/search/filter/paginate products, image galleries, star ratings
  & reviews, wishlist, sale pricing, related products, Deals + Best Sellers pages,
  cart, checkout, order history + email confirmation, unified Sign In (login/register/
  forgot-password as animated tabs in one page), real password-reset email
- **Admin**: dashboard with live stats, full CRUD on products (incl. gallery images
  & sale price) / categories / users, order management with status updates, customer
  list, sales/product/customer reports, site settings, activity log (`logs/app.log`)
- **Security**: password_hash/password_verify, PDO prepared statements everywhere,
  CSRF tokens on all state-changing forms, session hardening, upload validation,
  .htaccess hardening on config/includes/database/uploads/vendor
- A small JSON API layer under `/api/`

## Folder structure
`account/`, `admin/`, `api/`, `assets/`, `auth/`, `cart/`, `checkout/`, `config/`,
`database/`, `includes/`, `products/`, `wishlist/`, `uploads/`, `vendor/` (bundled
PHPMailer, MIT licensed).

## Explicitly out of scope for now
These are real gaps for a production store but are each substantial projects on
their own — ask if you want to tackle any of them next:
- Payment gateway integration (Stripe/PayPal/M-Pesa)
- Product variants (size/color)
- Coupon/discount codes
- Return/refund workflow
- Multi-language support
- Automated backups / monitoring / rate-limiting / CAPTCHA
