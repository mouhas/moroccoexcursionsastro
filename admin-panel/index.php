<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';
require_login();

render_header('Dashboard', 'dashboard');
?>
<h1>Dashboard</h1>
<p class="sub">Logged in as <?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>
<?php flash_render(); ?>

<div class="grid-links">
  <a class="tile" href="content/list.php"><b>Site Content</b><span>Every tour, page, and car — title, price, itinerary, FAQs, reviews, SEO.</span></a>
  <a class="tile" href="settings/edit.php"><b>Settings</b><span>PayPal, contact details, default SEO description.</span></a>
  <a class="tile" href="bookings/list.php"><b>Bookings</b><span>Booking-widget submissions from tour/car pages.</span></a>
  <a class="tile" href="messages/list.php"><b>Contact Messages</b><span>Submissions from the Contact page form.</span></a>
  <a class="tile" href="media/upload.php"><b>Media</b><span>Upload images, get the path to use in content.</span></a>
</div>

<div class="card">
  <h2>How this works</h2>
  <p style="font-size:14px;color:var(--muted);margin:0">Every save here is a real commit to the site's GitHub repo. GitHub Actions automatically rebuilds the site and republishes it to your GoDaddy/HostGator hosting — usually live within a minute or two.</p>
</div>
<?php render_footer(); ?>
