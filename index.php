<?php
declare(strict_types=1);
require __DIR__ . '/countries.php';
$countries = store_countries();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f6f5f0">
  <meta name="description" content="The beautiful game starts with the right gear. Shop curated football boots, balls, training essentials and more at Sunday Club.">
  <title>Sunday Club — Good gear. Good game.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <script src="app.js" defer></script>
</head>
<body>
  <div class="announcement">
    <span>Free shipping on orders PKR 75+</span>
    <span class="announcement-note">Made for the love of the game <span aria-hidden="true">✳</span></span>
  </div>

  <header class="site-header">
    <a class="brand" href="#" aria-label="Sunday Club home">
      <span class="brand-mark" aria-hidden="true">s<span>c</span></span>
      <span class="brand-name">sunday<span>club</span></span>
    </a>
    <nav class="main-nav" id="main-nav" aria-label="Main navigation">
      <a href="#shop">Shop all</a>
      <a href="#shop" data-nav-category="Boots">Boots</a>
      <a href="#shop" data-nav-category="Balls">Balls</a>
      <a href="#story">Our game</a>
      <a href="#tracking-dialog" class="open-tracking">Track order</a>
    </nav>
    <div class="header-actions">
      <button class="icon-button search-toggle" aria-label="Focus search" type="button"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path></svg></button>
      <button class="icon-button favorites-toggle" aria-label="Show favorites" type="button"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.8c0 5.2-8.8 10.1-8.8 10.1S3.2 14 3.2 8.8a4.5 4.5 0 0 1 8-2.8 4.5 4.5 0 0 1 8 2.8Z"></path></svg><span class="header-count favorite-count">0</span></button>
      <button class="icon-button cart-toggle" aria-label="Open shopping bag" type="button"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l1 12H4L5 8Z"></path><path d="M9 9V6a3 3 0 0 1 6 0v3"></path></svg><span class="header-count cart-count">0</span></button>
      <button class="menu-toggle" aria-label="Toggle navigation" aria-expanded="false" type="button"><span></span><span></span></button>
    </div>
  </header>

  <main>
    <section class="hero" aria-labelledby="hero-title">
      <div class="hero-copy">
        <p class="eyebrow"><span class="eyebrow-dot"></span> YOUR NEXT 90 MINUTES START HERE</p>
        <h1 id="hero-title">Good gear.<br><span>Good game.</span></h1>
        <p class="hero-description">Thoughtfully picked football essentials for the ones who play for the feeling.</p>
        <a class="button button-dark" href="#shop">Find your match <span aria-hidden="true">↗</span></a>
        <div class="hero-proof"><span class="proof-stars" aria-label="5 out of 5 stars">★★★★★</span><span>Loved by 2,400+ players</span></div>
      </div>
      <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1517466787929-bc90951d0974?auto=format&fit=crop&w=1400&q=85" alt="Footballers playing on a sunny green pitch">
        <div class="hero-sticker"><span>PLAY</span><span>YOUR</span><span>WAY <b>✳</b></span></div>
        <div class="hero-caption"><span>01 / 03</span><span>For the love of the game.</span></div>
      </div>
      <div class="hero-side-note">EST. FOR THE WEEKEND · 2024</div>
    </section>

    <section class="benefits" aria-label="Shopping benefits">
      <div><span class="benefit-icon">✳</span><span>Free shipping over PKR 75</span></div>
      <div><span class="benefit-icon">↺</span><span>Easy 30-day returns</span></div>
      <div><span class="benefit-icon">♡</span><span>Gear picked with care</span></div>
      <div><span class="benefit-icon">✦</span><span>Made for every player</span></div>
    </section>

    <section class="shop-section" id="shop">
      <div class="section-heading">
        <div><p class="eyebrow">THE GOOD STUFF</p><h2>Find your <span>game.</span></h2></div>
        <p class="section-intro">The right gear makes every kick feel a little better.<br>Find the pieces you'll keep coming back to.</p>
      </div>
      <div class="shop-toolbar">
        <div class="category-list" role="group" aria-label="Filter products by category">
          <button class="category-chip active" type="button" data-category="All">All gear <span>22</span></button>
          <button class="category-chip" type="button" data-category="Boots">Boots</button>
          <button class="category-chip" type="button" data-category="Balls">Balls</button>
          <button class="category-chip" type="button" data-category="Training">Training</button>
          <button class="category-chip" type="button" data-category="Accessories">Accessories</button>
          <button class="category-chip" type="button" data-category="Apparel">Apparel</button>
        </div>
        <div class="toolbar-right">
          <label class="search-box"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path></svg><input id="search-input" type="search" placeholder="Find your gear..." aria-label="Search products"></label>
          <label class="sort-select"><span class="sr-only">Sort products</span><select id="sort-select"><option value="featured">Featured</option><option value="low">Price: low to high</option><option value="high">Price: high to low</option><option value="rating">Top rated</option></select></label>
        </div>
      </div>
      <div class="results-line"><span id="results-count">Showing 22 pieces</span><button id="clear-filters" class="clear-filters" type="button" hidden>Clear filters <span>×</span></button></div>
      <div class="product-grid" id="product-grid" aria-live="polite"></div>
      <div id="empty-state" class="empty-state" hidden><span>◌</span><h3>No gear found</h3><p>Try a different search or category.</p><button class="button button-dark" id="reset-shop" type="button">See all gear</button></div>
    </section>

    <section class="story-section" id="story">
      <div class="story-image"><img src="https://images.unsplash.com/photo-1522778119026-d647f0596c20?auto=format&fit=crop&w=1200&q=85" alt="Football resting on the pitch after a match"><span class="story-image-tag">THE SUNDAY FEELING — SINCE DAY ONE</span></div>
      <div class="story-copy"><p class="eyebrow"><span class="eyebrow-dot"></span> A LITTLE ABOUT US</p><h2>It's never just<br>about the <span>score.</span></h2><p>It's the muddy boots by the door. The last-minute goal. The friends who feel like teammates. We're here for all of it — with gear that shows up, so you can too.</p><a class="text-link" href="#newsletter">A little more about our game <span>↗</span></a><span class="story-scribble" aria-hidden="true">good<br>game!</span></div>
    </section>

    <section class="newsletter" id="newsletter">
      <div><p class="eyebrow">NOT JUST ANOTHER NEWSLETTER</p><h2>A good thing<br>in your <span>inbox.</span></h2><p>New gear, good stories, and the occasional excuse to get outside.</p></div>
      <form class="newsletter-form" id="newsletter-form"><label class="sr-only" for="email-input">Your email address</label><input id="email-input" type="email" placeholder="Your email address" required><button type="submit" aria-label="Subscribe">Count me in <span>↗</span></button><span class="newsletter-note">No spam. Just the good stuff. Unsubscribe whenever.</span><span id="newsletter-message" class="newsletter-message" role="status"></span></form>
      <span class="newsletter-ball" aria-hidden="true">⚽</span>
    </section>
  </main>

  <footer class="site-footer">
    <div class="footer-top"><a class="brand footer-brand" href="#" aria-label="Sunday Club home"><span class="brand-mark" aria-hidden="true">s<span>c</span></span><span class="brand-name">sunday<span>club</span></span></a><p>For the love of the game.<br>And everyone who plays it.</p><div class="footer-links"><a href="#shop">Shop all</a><a href="#story">Our game</a><a href="mailto:hello@sundayclub.example">Get in touch</a></div><div class="social-links"><a href="#newsletter" aria-label="Instagram">ig ↗</a><a href="#newsletter" aria-label="TikTok">tk ↗</a></div></div>
    <div class="footer-bottom"><span>© 2025 Sunday Club Goods. Play nice.</span><span>Made for the love of the game <b>✳</b></span><div><a href="#shop">Privacy</a><a href="#shop">Terms</a></div></div>
  </footer>

  <div class="drawer-backdrop" id="drawer-backdrop"></div>
  <aside class="cart-drawer" id="cart-drawer" aria-label="Shopping cart" aria-hidden="true">
    <div class="drawer-header"><div><p class="eyebrow">YOUR GOOD STUFF</p><h2>Your bag <span id="drawer-item-count">(0)</span></h2></div><button class="close-drawer" type="button" aria-label="Close shopping cart">×</button></div>
    <div class="shipping-progress"><p id="shipping-message">You're PKR 75 away from free shipping</p><div class="progress-track"><span id="shipping-progress-fill"></span></div></div>
    <div class="cart-items" id="cart-items"></div>
    <div class="cart-empty" id="cart-empty"><span>◌</span><h3>Your bag's taking a breather.</h3><p>Let's find something for your next game.</p><button class="button button-dark continue-shopping" type="button">Keep shopping</button></div>
    <div class="cart-footer" id="cart-footer"><div class="cart-shipping-preview"><span>Standard delivery</span><strong id="cart-shipping">PKR 5.95</strong></div><div class="subtotal-row"><span>Subtotal</span><strong id="cart-subtotal">PKR 0.00</strong></div><div class="cart-total-preview"><span>Total</span><strong id="cart-total">PKR 0.00</strong></div><p id="cart-shipping-note">Taxes, if applicable, are confirmed at checkout.</p><button class="checkout-button" id="checkout-button" type="button">Continue to delivery <span>↗</span></button><div class="secure-note">Cash on delivery · Easy 30-day returns</div></div>
  </aside>
  <dialog class="store-dialog" id="checkout-dialog" aria-labelledby="checkout-title">
    <div class="dialog-heading"><div><p class="eyebrow">ALMOST GAME TIME</p><h2 id="checkout-title">Delivery details</h2></div><button class="dialog-close" type="button" data-close-dialog aria-label="Close checkout">×</button></div>
    <div id="checkout-content">
      <form id="checkout-form">
        <div class="checkout-form-grid">
          <label>Full name<input name="name" autocomplete="name" maxlength="100"></label>
          <label>Email address<input name="email" type="text" autocomplete="email" maxlength="200"></label>
          <label class="form-wide">Phone number<input name="phone" type="text" autocomplete="tel" maxlength="30"></label>
          <label class="form-wide">Street address<input name="address1" autocomplete="address-line1" maxlength="180"></label>
          <label>City<input name="city" autocomplete="address-level2" maxlength="100"></label>
          <label>Country
            <input id="country-search" name="country-name" list="country-options" autocomplete="country-name" value="Pakistan" required>
            <input id="country-code" name="country" type="hidden" value="PK">
            <datalist id="country-options">
              <?php foreach ($countries as $code => $name): ?>
                <option value="<?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-code="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </label>
        </div>
        <fieldset class="shipping-choices"><legend>Shipping method</legend>
          <label class="shipping-choice"><input type="radio" name="shipping" value="standard" checked><span><b>Standard delivery</b><small>3–5 business days</small></span><strong id="standard-shipping-price">PKR 5.95</strong></label>
          <label class="shipping-choice"><input type="radio" name="shipping" value="express"><span><b>Express delivery</b><small>1–2 business days</small></span><strong id="express-shipping-price">PKR 12.95</strong></label>
        </fieldset>
        <div class="payment-note"><span>♡</span><div><b>Cash on delivery</b><small>Pay your delivery person when your order arrives.</small></div></div>
        <div class="checkout-total"><span>Subtotal</span><b id="checkout-subtotal">PKR 0.00</b><span>Shipping</span><b id="checkout-shipping">PKR 5.95</b><span>Tax</span><b>Calculated at checkout</b><span>Total due</span><b id="checkout-total">PKR 0.00</b></div>
        <p class="tax-note">Sales tax is not calculated in this starter store. Verify tax requirements before accepting live orders.</p>
        <label class="form-wide note-field">Delivery note <span>(optional)</span><textarea name="note" maxlength="2000" rows="2" placeholder="Anything your delivery person should know?"></textarea></label>
        <p id="checkout-error" class="form-error" role="alert"></p>
        <button class="checkout-button" type="submit" id="place-order-button">Place cash-on-delivery order <span>↗</span></button>
      </form>
    </div>
    <div class="order-success" id="order-success" hidden><span class="success-mark">✓</span><p class="eyebrow">YOU'RE ALL SET</p><h3>Order received.</h3><p id="success-copy"></p><div class="order-access"><span>Save these details to track your order later:</span><b id="success-order-number"></b><b id="success-access-code"></b><small>This private access code is shown only once.</small></div><div class="order-status-card"><span>Payment</span><b>Cash on delivery</b><span>Estimated delivery</span><b id="success-delivery"></b></div><button class="button button-dark" id="track-new-order" type="button">Track your order <span>↗</span></button></div>
  </dialog>
  <dialog class="store-dialog tracking-dialog" id="tracking-dialog" aria-labelledby="tracking-title">
    <div class="dialog-heading"><div><p class="eyebrow">WHERE'S MY GOOD STUFF?</p><h2 id="tracking-title">Track an order</h2></div><button class="dialog-close" type="button" data-close-dialog aria-label="Close tracking">×</button></div>
    <form id="tracking-form" class="tracking-form"><label>Order number<input name="orderNumber" required placeholder="SC-YYYYMMDD-ABC123"></label><label>Private access code<input name="accessCode" required autocomplete="off"></label><p class="tax-note">Use the private access code shown when you placed the order.</p><p id="tracking-error" class="form-error" role="alert"></p><button class="checkout-button" type="submit">Find my order <span>↗</span></button></form>
    <div id="tracking-result" class="tracking-result" hidden></div>
  </dialog>
  <div class="toast" id="toast" role="status" aria-live="polite"></div>
</body>
</html>
