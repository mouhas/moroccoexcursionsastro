<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/github.php';
require_once __DIR__ . '/../lib/frontmatter.php';
require_once __DIR__ . '/../lib/layout.php';
require_once __DIR__ . '/../lib/form_helpers.php';
require_login();

$CONTENT_DIR = 'src/content/site';
$file = isset($_GET['file']) ? basename($_GET['file']) : (isset($_POST['file']) ? basename($_POST['file']) : '');
if (!$file || substr($file, -3) !== '.md') {
    header('Location: list.php');
    exit;
}
$path = $CONTENT_DIR . '/' . $file;

$error = null;
$data = [];
$body = '';
$sha = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $existing = gh_get_file($path);
        $sha = $existing ? $existing['sha'] : null;

        $p = $_POST;
        $num = function ($v) { return $v === '' || $v === null ? null : (float) $v; };

        $data = [
            'title' => trim($p['title']),
            'lang' => $p['lang'],
            'section' => $p['section'],
            'pslug' => trim($p['pslug']),
            'urlPath' => trim($p['urlPath']),
            'price' => $num($p['price']),
            'duration' => trim($p['duration']) !== '' ? trim($p['duration']) : null,
            'tag' => trim($p['tag']) !== '' ? trim($p['tag']) : null,
            'source' => trim($p['source']),

            'rating' => $num($p['rating']),
            'reviewCount' => $p['reviewCount'] === '' ? null : (int) $p['reviewCount'],
            'tourType' => trim($p['tourType']) !== '' ? trim($p['tourType']) : null,
            'cancellation' => trim($p['cancellation']) !== '' ? trim($p['cancellation']) : null,
            'languagesSpoken' => trim($p['languagesSpoken']) !== '' ? trim($p['languagesSpoken']) : null,
            'tourCode' => trim($p['tourCode']) !== '' ? trim($p['tourCode']) : null,
            'mapUrl' => trim($p['mapUrl']) !== '' ? trim($p['mapUrl']) : null,

            'overviewHtml' => norm_nl($p['overviewHtml']),
            'priceHeading' => trim($p['priceHeading']) !== '' ? trim($p['priceHeading']) : null,
            'priceRows' => parse_price_rows($p['priceRows']),
            'notesHeading' => trim($p['notesHeading']) !== '' ? trim($p['notesHeading']) : null,
            'notes' => parse_string_list($p['notes']),
            'highlights' => parse_string_list($p['highlights']),

            'itinerary' => parse_object_list($p['itinerary'], [['name' => 'title'], ['name' => 'html']], 'title'),
            'included' => parse_string_list($p['included']),
            'excluded' => parse_string_list($p['excluded']),
            'faqs' => parse_object_list($p['faqs'], [['name' => 'q'], ['name' => 'aHtml']], 'q'),
            'reviews' => parse_object_list($p['reviews'], [
                ['name' => 'name'], ['name' => 'date'], ['name' => 'stars', 'type' => 'number'],
                ['name' => 'likes', 'type' => 'number'], ['name' => 'title'], ['name' => 'text'],
            ], 'name'),

            'pricingMode' => $p['pricingMode'],
            'priceTiers' => parse_object_list($p['priceTiers'], [['name' => 'people', 'type' => 'number'], ['name' => 'perPerson', 'type' => 'number']], 'people'),
            'campTiers' => parse_object_list($p['campTiers'], [['name' => 'label'], ['name' => 'perPerson', 'type' => 'number']], 'label'),
            'hasDesertExtras' => !empty($p['hasDesertExtras']),

            'carBadges' => parse_string_list($p['carBadges']),
            'carCategory' => trim($p['carCategory']) !== '' ? trim($p['carCategory']) : null,
            'carFeatures' => parse_string_list($p['carFeatures']),

            'iconGrid' => parse_object_list($p['iconGrid'], [['name' => 'icon'], ['name' => 'title'], ['name' => 'text']], 'title'),
            'carListings' => array_map(function ($row) {
                $row['specs'] = parse_string_list(isset($row['specs']) ? $row['specs'] : '');
                return $row;
            }, parse_object_list_keep_specs($p['carListings'])),

            'metaTitle' => trim($p['metaTitle']) !== '' ? trim($p['metaTitle']) : null,
            'metaDescription' => trim($p['metaDescription']) !== '' ? trim($p['metaDescription']) : null,
        ];

        // autoPricing: only saved as an object if at least "days" is filled in.
        if (trim($p['autoPricing_days']) !== '') {
            $data['autoPricing'] = [
                'days' => (int) $p['autoPricing_days'],
                'nights' => (int) $p['autoPricing_nights'],
                'hotelPerNight' => (float) $p['autoPricing_hotelPerNight'],
                'superiorSurcharge' => (float) $p['autoPricing_superiorSurcharge'],
                'transportPerDay' => (float) $p['autoPricing_transportPerDay'],
            ];
        } else {
            $data['autoPricing'] = null;
        }
        // ratingBreakdown: only saved if at least one sub-score is filled in.
        $rb = ['hotels' => $p['rb_hotels'], 'guides' => $p['rb_guides'], 'transport' => $p['rb_transport'], 'activities' => $p['rb_activities']];
        $rbFilled = array_filter($rb, function ($v) { return trim($v) !== ''; });
        $data['ratingBreakdown'] = $rbFilled ? array_map(function ($v) { return (float) $v; }, $rb) : null;

        $data['activityOptions'] = []; // edited as raw JSON below, see note in the form

        $newBody = isset($p['body']) ? norm_nl($p['body']) : '';
        $fileContent = dump_frontmatter($data, $newBody);
        gh_put_file($path, $fileContent, 'Edit ' . $data['title'] . ' via admin panel', $sha);

        flash_set('ok', 'Saved. The live site will update automatically in a minute or two.');
        header('Location: edit.php?file=' . urlencode($file));
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
        // fall through to re-render the form with what was submitted
        $data = $p;
        $body = isset($p['body']) ? $p['body'] : '';
    }
} else {
    try {
        $existing = gh_get_file($path);
        if (!$existing) {
            header('Location: list.php');
            exit;
        }
        $parsed = parse_frontmatter($existing['content']);
        $data = $parsed['data'];
        $body = $parsed['body'];
        $sha = $existing['sha'];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function parse_object_list_keep_specs($raw) {
    // carListings has a "specs" sub-field that's itself a newline list, not
    // a plain scalar — handle it separately from parse_object_list().
    if (!is_array($raw)) return [];
    $out = [];
    foreach ($raw as $row) {
        if (!is_array($row) || trim((isset($row['title']) ? $row['title'] : '')) === '') continue;
        $out[] = [
            'icon' => isset($row['icon']) ? trim($row['icon']) : '',
            'title' => trim($row['title']),
            'text' => isset($row['text']) ? trim($row['text']) : '',
            'specs' => isset($row['specs']) ? $row['specs'] : '',
            'price' => (isset($row['price']) && trim($row['price']) !== '') ? trim($row['price']) : null,
            'href' => (isset($row['href']) && trim($row['href']) !== '') ? trim($row['href']) : null,
            'bookHref' => (isset($row['bookHref']) && trim($row['bookHref']) !== '') ? trim($row['bookHref']) : null,
        ];
    }
    return $out;
}

function g($data, $key, $default = '') {
    return isset($data[$key]) && $data[$key] !== null ? $data[$key] : $default;
}

render_header('Edit — ' . g($data, 'title', $file), 'content');
?>
<div class="actions-row">
  <div>
    <h1><?php echo h(g($data, 'title', $file)); ?></h1>
    <p class="sub"><?php echo h($file); ?></p>
  </div>
  <a href="list.php" class="btn secondary small">← Back to list</a>
</div>
<?php flash_render(); ?>
<?php if ($error) : ?><div class="flash error">Save failed: <?php echo h($error); ?></div><?php endif; ?>

<form method="post">
  <?php echo csrf_field(); ?>
  <input type="hidden" name="file" value="<?php echo h($file); ?>">

  <fieldset><legend>Basics</legend>
    <?php field_text('title', g($data, 'title'), 'Title'); ?>
    <?php field_select('lang', g($data, 'lang', 'en'), ['en', 'fr', 'es', 'it', 'pt-br'], 'Language'); ?>
    <?php field_select('section', g($data, 'section', 'pages'), ['pages', 'tours', 'cars'], 'Section'); ?>
    <?php field_text('pslug', g($data, 'pslug'), 'Slug (pslug)'); ?>
    <?php field_text('urlPath', g($data, 'urlPath'), 'URL Path'); ?>
    <?php field_text('price', g($data, 'price'), 'Price (EUR, number only)', null, 'number'); ?>
    <?php field_text('duration', g($data, 'duration'), 'Duration (e.g. "4 Days")'); ?>
    <?php field_text('tag', g($data, 'tag'), 'Tag'); ?>
    <?php field_text('source', g($data, 'source'), 'Source URL (original page)'); ?>
  </fieldset>

  <fieldset><legend>Ratings</legend>
    <?php field_text('rating', g($data, 'rating'), 'Rating (0–5)', null, 'number'); ?>
    <?php field_text('reviewCount', g($data, 'reviewCount'), 'Review Count', null, 'number'); ?>
    <div class="hint" style="margin:-6px 0 10px">Rating Breakdown (leave all blank to omit)</div>
    <?php $rb = g($data, 'ratingBreakdown', []); if (!is_array($rb)) $rb = []; ?>
    <?php field_text('rb_hotels', g($rb, 'hotels'), 'Hotels', null, 'number'); ?>
    <?php field_text('rb_guides', g($rb, 'guides'), 'Guides', null, 'number'); ?>
    <?php field_text('rb_transport', g($rb, 'transport'), 'Transport', null, 'number'); ?>
    <?php field_text('rb_activities', g($rb, 'activities'), 'Activities', null, 'number'); ?>
  </fieldset>

  <fieldset><legend>Tour Info</legend>
    <?php field_text('tourType', g($data, 'tourType'), 'Tour Type (e.g. "Private Tour")'); ?>
    <?php field_text('cancellation', g($data, 'cancellation'), 'Free Cancellation Window'); ?>
    <?php field_text('languagesSpoken', g($data, 'languagesSpoken'), 'Languages Spoken'); ?>
    <?php field_text('tourCode', g($data, 'tourCode'), 'Tour Code'); ?>
    <?php field_text('mapUrl', g($data, 'mapUrl'), 'Google Maps Embed URL'); ?>
  </fieldset>

  <fieldset><legend>Overview & Pricing Table</legend>
    <?php field_richtext('overviewHtml', g($data, 'overviewHtml'), 'Overview'); ?>
    <?php field_text('priceHeading', g($data, 'priceHeading'), 'Price Table Heading'); ?>
    <?php field_price_rows('priceRows', g($data, 'priceRows', []), 'Price Table Rows'); ?>
    <?php field_text('notesHeading', g($data, 'notesHeading'), 'Notes Heading'); ?>
    <?php field_string_list('notes', g($data, 'notes', []), 'Notes (Good to Know)'); ?>
    <?php field_string_list('highlights', g($data, 'highlights', []), 'Highlights'); ?>
  </fieldset>

  <?php field_object_list('itinerary', g($data, 'itinerary', []), [
    ['name' => 'title', 'label' => 'Day Title'],
    ['name' => 'html', 'label' => 'Day Description', 'type' => 'richtext'],
  ], 'Day-by-Day Itinerary', 2); ?>

  <fieldset><legend>What's Included</legend>
    <?php field_string_list('included', g($data, 'included', []), 'Included'); ?>
    <?php field_string_list('excluded', g($data, 'excluded', []), 'Not Included'); ?>
  </fieldset>

  <?php field_object_list('faqs', g($data, 'faqs', []), [
    ['name' => 'q', 'label' => 'Question'],
    ['name' => 'aHtml', 'label' => 'Answer', 'type' => 'richtext'],
  ], 'FAQs', 2); ?>

  <?php field_object_list('reviews', g($data, 'reviews', []), [
    ['name' => 'name', 'label' => 'Name'],
    ['name' => 'date', 'label' => 'Date'],
    ['name' => 'stars', 'label' => 'Stars (1-5)', 'type' => 'number'],
    ['name' => 'likes', 'label' => 'Likes', 'type' => 'number'],
    ['name' => 'title', 'label' => 'Review Title'],
    ['name' => 'text', 'label' => 'Review Text', 'type' => 'textarea'],
  ], 'Reviews', 2); ?>

  <fieldset><legend>Pricing Engine</legend>
    <?php field_select('pricingMode', g($data, 'pricingMode', 'group'), [
      ['group', 'Group — flat per-person price × travelers'],
      ['tiers', 'Tiers — manual price per group size'],
      ['auto', 'Auto — hotel/night + transport/day formula'],
      ['activity', 'Activity — vehicle option × duration'],
    ], 'Pricing Mode'); ?>
    <div class="hint" style="margin-bottom:14px">Group mode uses the Price field above. Fill in Tiers or Auto below only if you use that mode.</div>
    <?php field_object_list('priceTiers', g($data, 'priceTiers', []), [
      ['name' => 'people', 'label' => 'Number of People', 'type' => 'number'],
      ['name' => 'perPerson', 'label' => 'Price Per Person (EUR)', 'type' => 'number'],
    ], 'Price Tiers (Tiers mode)', 2); ?>
    <div class="hint" style="margin:-6px 0 10px">Auto-Pricing Formula (Auto mode — leave Days blank to omit entirely)</div>
    <?php $ap = g($data, 'autoPricing', []); if (!is_array($ap)) $ap = []; ?>
    <?php field_text('autoPricing_days', g($ap, 'days'), 'Days', null, 'number'); ?>
    <?php field_text('autoPricing_nights', g($ap, 'nights'), 'Nights', null, 'number'); ?>
    <?php field_text('autoPricing_hotelPerNight', g($ap, 'hotelPerNight', 40), 'Standard Hotel (EUR/person/night)', null, 'number'); ?>
    <?php field_text('autoPricing_superiorSurcharge', g($ap, 'superiorSurcharge', 50), 'Superior Upgrade (EUR/person/night)', null, 'number'); ?>
    <?php field_text('autoPricing_transportPerDay', g($ap, 'transportPerDay', 300), 'Transport (EUR/day, flat)', null, 'number'); ?>
    <?php field_object_list('campTiers', g($data, 'campTiers', []), [
      ['name' => 'label', 'label' => 'Label (e.g. "Standard")'],
      ['name' => 'perPerson', 'label' => 'Price Per Person (EUR)', 'type' => 'number'],
    ], 'Camp Tiers (desert overnight Standard/Luxury choice)', 1); ?>
    <?php field_checkbox('hasDesertExtras', g($data, 'hasDesertExtras'), 'Offer Desert Extras (Quad Biking / Dune Buggy)'); ?>
    <?php if (g($data, 'activityOptions', [])) : ?>
      <div class="hint">This tour uses "Activity" pricing (vehicle × duration) — editing that structure isn't supported in this form yet; ask your developer to adjust it directly if needed.</div>
    <?php endif; ?>
  </fieldset>

  <fieldset><legend>Car-Specific</legend>
    <?php field_string_list('carBadges', g($data, 'carBadges', []), 'Car Badges'); ?>
    <?php field_text('carCategory', g($data, 'carCategory'), 'Car Category'); ?>
    <?php field_string_list('carFeatures', g($data, 'carFeatures', []), 'Car Features'); ?>
  </fieldset>

  <?php field_object_list('iconGrid', g($data, 'iconGrid', []), [
    ['name' => 'icon', 'label' => 'Icon name (see Icon.astro)'],
    ['name' => 'title', 'label' => 'Title'],
    ['name' => 'text', 'label' => 'Text', 'type' => 'textarea'],
  ], 'Icon Grid (feature cards)', 2); ?>

  <fieldset><legend>Car Listings (fleet grid)</legend>
    <?php
    $listings = g($data, 'carListings', []);
    $slots = count($listings) + 2;
    for ($i = 0; $i < $slots; $i++) :
      $item = isset($listings[$i]) ? $listings[$i] : [];
    ?>
      <div class="repeat-item">
        <div class="row-head"><strong>#<?php echo $i + 1; ?></strong></div>
        <?php field_text("carListings[$i][icon]", g($item, 'icon'), 'Icon (car, van, or bus)'); ?>
        <?php field_text("carListings[$i][title]", g($item, 'title'), 'Title'); ?>
        <?php field_textarea("carListings[$i][text]", g($item, 'text'), 'Text', null, 3); ?>
        <?php field_string_list("carListings[$i][specs]", g($item, 'specs', []), 'Specs'); ?>
        <?php field_text("carListings[$i][price]", g($item, 'price'), 'Price (e.g. 130€/Day)'); ?>
        <?php field_text("carListings[$i][href]", g($item, 'href'), "Link to this car's own page (optional)"); ?>
        <?php field_text("carListings[$i][bookHref]", g($item, 'bookHref'), 'Book link'); ?>
      </div>
    <?php endfor; ?>
  </fieldset>

  <fieldset><legend>SEO</legend>
    <?php field_text('metaTitle', g($data, 'metaTitle'), 'SEO Title Override', 'Leave blank to use the auto-generated title.'); ?>
    <?php field_textarea('metaDescription', g($data, 'metaDescription'), 'SEO Description Override', 'Leave blank to use the auto-generated description.', 3); ?>
  </fieldset>

  <fieldset><legend>Page Body (markdown — info pages only)</legend>
    <?php field_textarea('body', $body, '', 'This is what renders below the page header on generic content pages (About, FAQs, etc).', 14); ?>
  </fieldset>

  <button type="submit" class="btn">Save</button>
  <a href="list.php" class="btn secondary">Cancel</a>
</form>
<?php rich_editor_assets(base_path()); ?>
<?php render_footer(); ?>
