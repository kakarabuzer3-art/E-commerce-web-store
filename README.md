# Sunday Club store

The storefront runs as a PHP/MySQL app under Laragon. PHP 8.1+ with PDO MySQL
enabled is required. No Composer packages are needed.

## Local setup

1. Copy `config.local.example.php` to `config.local.php` and set your MySQL
   credentials. Laragon's default local root account may have a blank password.
2. Create the database configured as `name` in `config.local.php` (the default
   is `sunday_club`).
3. From PowerShell, run:

   ```powershell
   & 'C:\laragon\bin\php\php-8.3.36-Win32-vs16-x64\php.exe' setup.php
   ```

4. Open `http://localhost/ecomerce%20store/` in your browser. Keep Laragon
   running while you shop. If you open `index.html` from File Explorer, it
   redirects to the Laragon URL; the PHP storefront cannot run from a `file://`
   address.

`setup.php` creates the store's `sc_products`, `sc_orders`, and
`sc_order_items` tables and inserts the 22 starter products. It preserves
inventory quantities when it is run again.

## Share the database with an existing admin app

Set `name` in `config.local.php` to the database already used by the admin
app, then run `setup.php`. The store uses the `sc_` prefix so the new product,
order, and order-item tables do not overwrite existing tables. The current
workspace contains no admin app or admin database configuration, so the
existing admin schema was not changed. The unrelated `hangout` database was
left untouched.

## Products, images, delivery, and orders

Products are served from MySQL, including their name, SKU, category,
description, image path, price in cents, visibility, and on-hand stock. The
locally stored product imagery in `assets/products/` loads without a
third-party image service at runtime; the captain's armband uses a custom
vector product illustration. Product names are sample store labels, and the
photos are illustrative rather than claims about exact branded models.
Anonymous baskets are stored in a server-side PHP session. Checkout is limited to Pakistan,
validates the selected country, locks inventory while
placing an order, snapshots item prices, and records the delivery address and
cash-on-delivery payment choice. Contact and address fields can be left blank;
delivery notes accept up to 2,000 characters. The sample shipping prices and
catalog prices use PKR and retain their existing numeric values. They are
sample prices, not live carrier quotes. Existing orders are marked PKR by the
database setup migration.

Standard shipping is PKR 5.95, is free at PKR 75, and is estimated at 3–5
business days. Express is PKR 12.95 and estimated at 1–2 business days. Sales
tax is not calculated. Configure real carrier services and tax rules before
taking live orders.

## Store administration

Set a long, unique `admin_token` in `config.local.php`. Open
`http://localhost/ecomerce%20store/admin/` and use this token to sign in.
The admin page can add products, update price and stock, hide/restore products,
manage order status, and enter carrier tracking numbers. The products table
records product creation and removal times; restoring a product clears its
removal time. Product images must
first be copied into `assets/products/` and referenced by their relative path.
Hiding a product preserves its catalog record and historical order details.
Shipped and out-for-delivery orders require a tracking
number; the database records when an order first enters out-for-delivery and
delivered states. Cancelled orders cannot be reopened; cancellation restores
inventory once. Run `setup.php` after upgrading to apply these schema changes
to an existing database.

Customers can track an order with the order number and one-time private access
code shown after placement. Keep that code safe; it cannot be recovered from
the database.
