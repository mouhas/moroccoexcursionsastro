<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/github.php';
require_once __DIR__ . '/../lib/frontmatter.php';
require_once __DIR__ . '/../lib/layout.php';
require_once __DIR__ . '/../lib/form_helpers.php';
require_login();

$path = 'src/content/settings/general.yml';
$error = null;
$data = [];
$sha = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $existing = gh_get_file($path);
        $sha = $existing ? $existing['sha'] : null;
        $p = $_POST;
        $data = [
            'paypalClientId' => trim($p['paypalClientId']) !== '' ? trim($p['paypalClientId']) : 'sb',
            'paypalMode' => $p['paypalMode'] === 'live' ? 'live' : 'sandbox',
            'phone' => trim($p['phone']),
            'phoneHref' => trim($p['phoneHref']),
            'whatsappHref' => trim($p['whatsappHref']),
            'email' => trim($p['email']),
            'defaultMetaDescription' => trim($p['defaultMetaDescription']),
            'submitEndpoint' => trim($p['submitEndpoint']),
        ];
        $content = dump_data_file($data, 'yaml');
        gh_put_file($path, $content, 'Update site settings via admin panel', $sha);
        flash_set('ok', 'Settings saved. Live in a minute or two.');
        header('Location: edit.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
} else {
    try {
        $existing = gh_get_file($path);
        if ($existing) {
            $data = parse_data_file($existing['content'], 'yaml');
            $sha = $existing['sha'];
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function g($data, $key, $default = '') {
    return isset($data[$key]) && $data[$key] !== null ? $data[$key] : $default;
}

render_header('Settings', 'settings');
?>
<h1>Site Settings</h1>
<p class="sub">PayPal, contact details, and default SEO — read by the site at build time.</p>
<?php flash_render(); ?>
<?php if ($error) : ?><div class="flash error">Couldn't load/save settings: <?php echo h($error); ?></div><?php endif; ?>

<form method="post">
  <?php echo csrf_field(); ?>
  <fieldset><legend>PayPal</legend>
    <?php field_select('paypalMode', g($data, 'paypalMode', 'sandbox'), [
      ['sandbox', 'Sandbox (test — never charges real money)'],
      ['live', 'Live (real payments)'],
    ], 'Mode'); ?>
    <?php field_text('paypalClientId', g($data, 'paypalClientId', 'sb'), 'Live Client ID', 'From developer.paypal.com → Apps & Credentials → your app → Live. Only used when Mode above is Live.'); ?>
  </fieldset>
  <fieldset><legend>Contact</legend>
    <?php field_text('phone', g($data, 'phone'), 'Phone (display)'); ?>
    <?php field_text('phoneHref', g($data, 'phoneHref'), 'Phone link (tel:...)'); ?>
    <?php field_text('whatsappHref', g($data, 'whatsappHref'), 'WhatsApp link (https://wa.me/...)'); ?>
    <?php field_text('email', g($data, 'email'), 'Email', null, 'email'); ?>
  </fieldset>
  <fieldset><legend>SEO & Bookings</legend>
    <?php field_textarea('defaultMetaDescription', g($data, 'defaultMetaDescription'), 'Default SEO Description', 'Used when a page has no override and no auto-generated text.', 3); ?>
    <?php field_text('submitEndpoint', g($data, 'submitEndpoint'), 'Bookings/Messages Endpoint (Worker URL)', 'Leave blank until you\'ve deployed worker/ — see BOOKINGS-SETUP.md.'); ?>
  </fieldset>
  <button type="submit" class="btn">Save</button>
</form>
<?php render_footer(); ?>
