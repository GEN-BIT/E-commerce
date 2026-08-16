# Migrations

The full schema (including settings and password_resets) now lives in
`database/ecommerce.sql` — for a fresh install, just import that file
followed by `database/seed.sql`, and you're done.

This folder is for *incremental* changes going forward, once the site
already has real data you don't want to lose (e.g. `004_add_wishlist_table.sql`).
