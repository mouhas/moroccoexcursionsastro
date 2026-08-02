<?php
// Cross-language matching for the "Translations:" switcher on content/edit.php
// — lets you jump from a tour/page to the same tour/page in another
// language. Sourced from a small, hand-verified lookup table (the same one
// the live site's own language switcher uses, src/data/tour-translations.json
// + the PAGE_TRANSLATIONS table in src/site.ts), never guessed. An earlier
// version tried to fill gaps by matching shared place-names in the
// itinerary, but Morocco tour itineraries overlap so heavily in vocabulary
// (Marrakech, the Atlas, the desert, Fes... appear in nearly every
// multi-day tour) that even a strict version of that heuristic produced
// wrong matches. A table that's sometimes incomplete but never wrong beats
// one that's more complete but sometimes wrong — anything not in the table
// just offers to create that translation instead of guessing.

function load_tour_translation_groups() {
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $f = gh_get_file('src/data/tour-translations.json');
        $cache = $f ? json_decode($f['content'], true) : [];
    } catch (Exception $e) {
        $cache = [];
    }
    return is_array($cache) ? $cache : [];
}

function find_tour_translation_group($lang, $pslug) {
    foreach (load_tour_translation_groups() as $group) {
        if (isset($group[$lang]) && $group[$lang] === $pslug) return $group;
    }
    return null;
}

// Mirrors the PAGE_TRANSLATIONS table in src/site.ts — small and rarely
// changes, so it's kept here directly rather than fetched.
function page_translation_groups() {
    return [
        ['en' => 'reviews', 'fr' => 'reviews', 'es' => 'reviews', 'it' => 'reviews', 'pt-br' => 'reviews'],
        ['en' => 'travel-agency', 'fr' => 'agence', 'es' => 'agencia', 'it' => 'agenzia', 'pt-br' => 'agencia'],
        ['en' => 'morocco-rental-cars', 'es' => 'alquiler-de-coches', 'fr' => 'location-de-voitures', 'it' => 'noleggio-auto', 'pt-br' => 'aluguel-de-carro'],
        ['en' => 'morocco-desert-tours', 'fr' => 'circuit-desert-maroc', 'es' => 'viaje-desierto-marruecos', 'it' => 'tour-deserto-marocco', 'pt-br' => 'excursoes-deserto-marrocos'],
        ['en' => 'dmc-morocco', 'fr' => 'dmc-maroc', 'es' => 'dmc-marruecos', 'it' => 'dmc-marocco', 'pt-br' => 'dmc-marrocos'],
        ['en' => 'camel-trekking-morocco', 'fr' => 'randonnee-dromadaires', 'es' => 'excursion-camellos', 'it' => 'tour-cammello-marocco', 'pt-br' => 'excursoes-camelos'],
        ['en' => 'contact', 'fr' => 'contact', 'es' => 'contacto', 'it' => 'contatto', 'pt-br' => 'contacto'],
        ['en' => 'diversity-inclusion', 'es' => 'diversidad-inclusion', 'fr' => 'diversite-inclusion', 'it' => 'diversita-inclusione', 'pt-br' => 'diversidade-inclusao'],
        ['en' => 'collaboration', 'es' => 'colaboracion', 'fr' => 'collaboration', 'it' => 'collaborazione', 'pt-br' => 'colaboracao'],
        ['en' => 'eligibility', 'es' => 'elegibilidad', 'fr' => 'eligibilite', 'it' => 'idoneita', 'pt-br' => 'elegibilidade'],
        ['en' => 'christmas-new-years-eve-in-morocco', 'es' => 'navidad-y-ano-nuevo-en-marruecos', 'fr' => 'noel-et-nouvel-an-au-maroc', 'it' => 'natale-e-capodanno-in-marocco', 'pt-br' => 'natal-e-ano-novo-no-marrocos'],
        ['en' => 'code-of-conduct', 'es' => 'codigo-de-conducta', 'fr' => 'code-de-conduite', 'it' => 'codice-di-condotta', 'pt-br' => 'codigo-de-conduta'],
        ['en' => 'safety-risk-management', 'es' => 'gestion-de-riesgos', 'fr' => 'gestion-des-risques', 'it' => 'gestione-dei-rischi', 'pt-br' => 'gestao-de-riscos'],
        ['en' => 'morocco-students-tours', 'es' => 'tours-estudiantes-marruecos', 'fr' => 'circuits-etudiants-maroc', 'it' => 'tour-studenti-marocco', 'pt-br' => 'tours-estudantes-marrocos'],
        ['en' => 'semester-at-sea-morocco', 'es' => 'semestre-en-el-mar-marruecos', 'fr' => 'semestre-en-mer-maroc', 'it' => 'semestre-in-mare-marocco', 'pt-br' => 'semestre-no-mar-marrocos'],
        ['en' => 'morocco-2-day-trips', 'es' => 'marruecos-2-dias', 'fr' => 'maroc-2-jours', 'it' => 'marocco-2-giorni', 'pt-br' => 'marrocos-2-dias'],
        ['en' => 'morocco-3-day-trips', 'es' => 'marruecos-3-dias', 'fr' => 'maroc-3-jours', 'it' => 'marocco-3-giorni', 'pt-br' => 'marrocos-3-dias'],
        ['en' => 'morocco-4-day-trips', 'es' => 'marruecos-4-dias', 'fr' => 'maroc-4-jours', 'it' => 'marocco-4-giorni', 'pt-br' => 'marrocos-4-dias'],
        ['en' => 'morocco-5-day-trips', 'es' => 'marruecos-5-dias', 'fr' => 'maroc-5-jours', 'it' => 'marocco-5-giorni', 'pt-br' => 'marrocos-5-dias'],
        ['en' => 'morocco-6-day-trips', 'es' => 'marruecos-6-dias', 'fr' => 'maroc-6-jours', 'it' => 'marocco-6-giorni', 'pt-br' => 'marrocos-6-dias'],
        ['en' => 'morocco-7-day-trips', 'es' => 'marruecos-7-dias', 'fr' => 'maroc-7-jours', 'it' => 'marocco-7-giorni', 'pt-br' => 'marrocos-7-dias'],
        ['en' => 'morocco-8-day-trips', 'es' => 'marruecos-8-dias', 'fr' => 'maroc-8-jours', 'it' => 'marocco-8-giorni', 'pt-br' => 'marrocos-8-dias'],
        ['en' => 'morocco-9-day-trips', 'es' => 'marruecos-9-dias', 'fr' => 'maroc-9-jours', 'it' => 'marocco-9-giorni', 'pt-br' => 'marrocos-9-dias'],
        ['en' => 'morocco-10-day-trips', 'es' => 'marruecos-10-dias', 'fr' => 'maroc-10-jours', 'it' => 'marocco-10-giorni', 'pt-br' => 'marrocos-10-dias'],
        ['en' => 'morocco-11-day-trips', 'es' => 'marruecos-11-dias', 'fr' => 'maroc-11-jours', 'it' => 'marocco-11-giorni', 'pt-br' => 'marrocos-11-dias'],
        ['en' => 'morocco-12-day-trips', 'es' => 'marruecos-12-dias', 'fr' => 'maroc-12-jours', 'it' => 'marocco-12-giorni', 'pt-br' => 'marrocos-12-dias'],
        ['en' => 'morocco-13-day-trips', 'es' => 'marruecos-13-dias', 'fr' => 'maroc-13-jours', 'it' => 'marocco-13-giorni', 'pt-br' => 'marrocos-13-dias'],
        ['en' => 'morocco-14-day-trips', 'es' => 'marruecos-14-dias', 'fr' => 'maroc-14-jours', 'it' => 'marocco-14-giorni', 'pt-br' => 'marrocos-14-dias'],
        ['en' => 'morocco-15-day-trips', 'es' => 'marruecos-15-dias', 'fr' => 'maroc-15-jours', 'it' => 'marocco-15-giorni', 'pt-br' => 'marrocos-15-dias'],
        ['en' => 'morocco-16-day-trips', 'es' => 'marruecos-16-dias', 'fr' => 'maroc-16-jours', 'it' => 'marocco-16-giorni', 'pt-br' => 'marrocos-16-dias'],
        ['en' => 'morocco-17-day-trips', 'es' => 'marruecos-17-dias', 'fr' => 'maroc-17-jours', 'it' => 'marocco-17-giorni', 'pt-br' => 'marrocos-17-dias'],
        ['en' => 'morocco-18-day-trips', 'es' => 'marruecos-18-dias', 'fr' => 'maroc-18-jours', 'it' => 'marocco-18-giorni', 'pt-br' => 'marrocos-18-dias'],
        ['en' => 'morocco-19-day-trips', 'es' => 'marruecos-19-dias', 'fr' => 'maroc-19-jours', 'it' => 'marocco-19-giorni', 'pt-br' => 'marrocos-19-dias'],
        ['en' => 'faqs', 'fr' => 'faqs', 'es' => 'faqs', 'it' => 'faqs', 'pt-br' => 'faqs'],
        ['en' => 'sitemap', 'fr' => 'sitemap', 'es' => 'sitemap', 'it' => 'sitemap', 'pt-br' => 'sitemap'],
    ];
}

function find_page_translation_group($lang, $pslug) {
    foreach (page_translation_groups() as $group) {
        if (isset($group[$lang]) && $group[$lang] === $pslug) return $group;
    }
    return null;
}

// Only 10 cars exist, so — same reasoning as PAGE_TRANSLATIONS — a small
// hardcoded table is simplest and safest, no ambiguity risk to guard
// against like with tours.
function car_translation_groups() {
    return [
        ['en' => 'bus-hire', 'es' => 'alquiler-autobus', 'fr' => 'location-bus', 'it' => 'noleggio-bus', 'pt-br' => 'aluguel-onibus'],
        ['en' => 'family', 'es' => 'coche-familiar', 'fr' => 'voiture-familiale', 'it' => 'auto-familiare', 'pt-br' => 'carro-familia'],
        ['en' => 'luxury-van', 'es' => 'furgoneta-lujo', 'fr' => 'van-de-luxe', 'it' => 'furgone-lusso', 'pt-br' => 'van-luxo'],
        ['en' => 'minivan', 'es' => 'minivan', 'fr' => 'minivan', 'it' => 'minivan', 'pt-br' => 'minivan'],
        ['en' => 'normal-4x4', 'es' => '4x4-normal', 'fr' => '4x4-standard', 'it' => '4x4-normale', 'pt-br' => '4x4-normal'],
        ['en' => 'normal-car', 'es' => 'coche-normal', 'fr' => 'voiture-standard', 'it' => 'auto-normale', 'pt-br' => 'carro-normal'],
        ['en' => 'pick-up', 'es' => 'pick-up', 'fr' => 'pick-up', 'it' => 'pick-up', 'pt-br' => 'picape'],
        ['en' => 'rental-bus', 'es' => 'autobus-alquiler', 'fr' => 'bus-location', 'it' => 'bus-noleggio', 'pt-br' => 'onibus-aluguel'],
        ['en' => 'suv', 'es' => 'suv', 'fr' => 'suv', 'it' => 'suv', 'pt-br' => 'suv'],
        ['en' => 'taxi', 'es' => 'taxi', 'fr' => 'taxi', 'it' => 'taxi', 'pt-br' => 'taxi'],
    ];
}

function find_car_translation_group($lang, $pslug) {
    foreach (car_translation_groups() as $group) {
        if (isset($group[$lang]) && $group[$lang] === $pslug) return $group;
    }
    return null;
}

// $currentFile: pass the real file being edited (marks that language as the
// non-clickable "current" pill).
//
// $groupLang/$groupPslug: which real, saved tour to look up translations
// for — defaults to $lang/$pslug, but must be overridden to the copy
// source's identity when rendering a not-yet-saved "create new translation"
// draft (see edit.php). A draft's own $lang/$pslug is borrowed from
// whatever it was copied from (e.g. lang=pt-br, pslug still the English
// slug) and was never a real saved combination, so looking up translations
// under that identity finds nothing — every other language would wrongly
// show as untranslated even when real translations exist.
function find_translation_links($currentFile, $lang, $section, $pslug, $groupLang = null, $groupPslug = null) {
    if ($groupLang === null) $groupLang = $lang;
    if ($groupPslug === null) $groupPslug = $pslug;
    $langs = ['en', 'es', 'fr', 'it', 'pt-br'];
    $links = [];
    $group = null;
    if ($section === 'tours') $group = find_tour_translation_group($groupLang, $groupPslug);
    elseif ($section === 'pages') $group = find_page_translation_group($groupLang, $groupPslug);
    elseif ($section === 'cars') $group = find_car_translation_group($groupLang, $groupPslug);
    foreach ($langs as $l) {
        if ($l === $lang) {
            $selfFile = $currentFile !== null ? $currentFile : "{$lang}__{$section}__{$pslug}.md";
            $links[$l] = ['file' => $selfFile, 'current' => $currentFile !== null];
            continue;
        }
        if ($l === $groupLang) {
            // The reference tour itself — we know its real file directly,
            // no group lookup needed (and it wouldn't find itself anyway).
            $links[$l] = ['file' => "{$groupLang}__{$section}__{$groupPslug}.md", 'current' => false];
            continue;
        }
        $targetSlug = isset($group[$l]) ? $group[$l] : null;
        $links[$l] = $targetSlug ? ['file' => "{$l}__{$section}__{$targetSlug}.md", 'current' => false] : ['file' => null, 'current' => false];
    }
    return $links;
}

// Shared markup for the "Translations:" pill bar on content/edit.php.
function render_lang_switch_bar($links, $section, $fromLang, $fromPslug) {
    $labels = ['en' => 'EN', 'es' => 'ES', 'fr' => 'FR', 'it' => 'IT', 'pt-br' => 'PT'];
    echo '<div class="pill-row" style="margin-bottom:18px"><span class="pill-row-caption">Language:</span>';
    foreach ($labels as $lc => $label) {
        $t = isset($links[$lc]) ? $links[$lc] : null;
        if ($t && !empty($t['current'])) {
            echo '<span class="pill active">' . h($label) . '</span>';
        } elseif ($t && $t['file']) {
            echo '<a class="pill" href="edit.php?file=' . urlencode($t['file']) . '">' . h($label) . '</a>';
        } else {
            // Not in the table — offer to start one instead of a dead end:
            // opens edit.php in the same window/tab, pre-filled with
            // content to translate rather than a blank form. Nothing is
            // written until that new page is saved. Same plain pill style
            // as a real match — the title tooltip is the only hint this
            // one creates a draft rather than opening an existing file.
            //
            // Prefer copying from EN when an EN version exists anywhere in
            // this group (even if that's not the page you're currently on)
            // — it's the master copy everything else is ultimately derived
            // from. Only fall back to the current page's language when
            // there's truly no EN version to work from.
            $enFile = isset($links['en']) && $links['en']['file'] ? $links['en']['file'] : null;
            $copyFrom = $enFile ?: "{$fromLang}__{$section}__{$fromPslug}.md";
            $copyFromLabel = $enFile ? 'EN' : strtoupper($fromLang);
            $newFile = "{$lc}__{$section}__{$fromPslug}.md";
            echo '<a class="pill" title="No translation yet — starts a new draft pre-filled from ' . h($copyFromLabel) . '" href="edit.php?file=' . urlencode($newFile) . '&copyFrom=' . urlencode($copyFrom) . '">' . h($label) . '</a>';
        }
    }
    echo '</div>';
}
