<?php
// Small helpers shared by the content/settings edit forms. Repeatable
// object lists (itinerary, FAQs, reviews, etc.) are rendered as N existing
// rows + a few blank rows to add more — no JS needed. On save, blank rows
// are dropped. Simple string lists (notes, highlights, included/excluded)
// use one-item-per-line textareas instead of per-row inputs, since that's
// both simpler to build and faster to edit for plain text lists.

function h($v) { return htmlspecialchars($v === null ? '' : (string) $v, ENT_QUOTES); }

function field_text($name, $value, $label, $hint = null, $type = 'text') {
    echo '<div class="field"><label for="' . h($name) . '">' . h($label) . '</label>';
    echo '<input type="' . h($type) . '" id="' . h($name) . '" name="' . h($name) . '" value="' . h($value) . '">';
    if ($hint) echo '<div class="hint">' . h($hint) . '</div>';
    echo '</div>';
}

function field_textarea($name, $value, $label, $hint = null, $rows = 5) {
    echo '<div class="field"><label for="' . h($name) . '">' . h($label) . '</label>';
    echo '<textarea id="' . h($name) . '" name="' . h($name) . '" rows="' . (int) $rows . '">' . h($value) . '</textarea>';
    if ($hint) echo '<div class="hint">' . h($hint) . '</div>';
    echo '</div>';
}

function field_select($name, $value, $options, $label) {
    echo '<div class="field"><label for="' . h($name) . '">' . h($label) . '</label><select id="' . h($name) . '" name="' . h($name) . '">';
    foreach ($options as $opt) {
        $optValue = is_array($opt) ? $opt[0] : $opt;
        $optLabel = is_array($opt) ? $opt[1] : $opt;
        $sel = ((string) $value === (string) $optValue) ? ' selected' : '';
        echo '<option value="' . h($optValue) . '"' . $sel . '>' . h($optLabel) . '</option>';
    }
    echo '</select></div>';
}

function field_checkbox($name, $checked, $label) {
    $c = $checked ? ' checked' : '';
    echo '<div class="field"><label><input type="checkbox" name="' . h($name) . '" value="1"' . $c . ' style="width:auto;display:inline-block;margin-right:8px">' . h($label) . '</label></div>';
}

// A list of plain strings <-> a textarea, one item per line.
function field_string_list($name, $values, $label, $hint = 'One per line.') {
    $text = implode("\n", array_map(function ($v) { return (string) $v; }, (array) $values));
    field_textarea($name, $text, $label, $hint, 6);
}
function parse_string_list($raw) {
    $lines = preg_split('/\r\n|\r|\n/', (string) $raw);
    $lines = array_map('trim', $lines);
    return array_values(array_filter($lines, function ($l) { return $l !== ''; }));
}

// A list of objects with simple scalar fields, e.g. itinerary: [{title, html}].
// $fieldDefs: [['name'=>'title','label'=>'Title','type'=>'text'], ['name'=>'html','label'=>'Description (HTML)','type'=>'textarea']]
function field_object_list($name, $items, $fieldDefs, $label, $extraSlots = 2) {
    $items = is_array($items) ? array_values($items) : [];
    $slots = count($items) + $extraSlots;
    echo '<fieldset><legend>' . h($label) . '</legend>';
    for ($i = 0; $i < $slots; $i++) {
        $item = isset($items[$i]) ? $items[$i] : [];
        echo '<div class="repeat-item"><div class="row-head"><strong>#' . ($i + 1) . '</strong></div>';
        foreach ($fieldDefs as $fd) {
            $fname = $name . '[' . $i . '][' . $fd['name'] . ']';
            $fval = isset($item[$fd['name']]) ? $item[$fd['name']] : '';
            if (isset($fd['type']) && $fd['type'] === 'number') {
                field_text($fname, $fval, $fd['label'], null, 'number');
            } elseif (isset($fd['type']) && $fd['type'] === 'textarea') {
                field_textarea($fname, $fval, $fd['label'], null, 4);
            } else {
                field_text($fname, $fval, $fd['label']);
            }
        }
        echo '</div>';
    }
    echo '</fieldset>';
}

// Parses back a field_object_list submission, dropping fully-blank rows.
// $requiredField: a row only counts as "filled in" if at least this field is non-empty.
function parse_object_list($raw, $fieldDefs, $requiredField = null) {
    if (!is_array($raw)) return [];
    $out = [];
    foreach ($raw as $row) {
        if (!is_array($row)) continue;
        $anyFilled = false;
        foreach ($fieldDefs as $fd) {
            if (isset($row[$fd['name']]) && trim((string) $row[$fd['name']]) !== '') { $anyFilled = true; break; }
        }
        if (!$anyFilled) continue;
        if ($requiredField !== null && (!isset($row[$requiredField]) || trim((string) $row[$requiredField]) === '')) continue;
        $entry = [];
        foreach ($fieldDefs as $fd) {
            $v = isset($row[$fd['name']]) ? trim((string) $row[$fd['name']]) : '';
            if (isset($fd['type']) && $fd['type'] === 'number') {
                $entry[$fd['name']] = $v === '' ? 0 : (float) $v;
                if ($entry[$fd['name']] == (int) $entry[$fd['name']]) $entry[$fd['name']] = (int) $entry[$fd['name']];
            } else {
                $entry[$fd['name']] = $v;
            }
        }
        $out[] = $entry;
    }
    return $out;
}

// priceRows: array of arrays of strings (a table, first row = header).
function field_price_rows($name, $rows, $label) {
    $rows = is_array($rows) ? array_values($rows) : [];
    echo '<fieldset><legend>' . h($label) . '</legend><div class="hint" style="margin-bottom:10px">One row per line, columns separated by " | " (pipe). First row is the table header.</div>';
    $lines = [];
    foreach ($rows as $row) {
        $lines[] = implode(' | ', array_map('strval', (array) $row));
    }
    field_textarea($name, implode("\n", $lines), '', null, 8);
    echo '</fieldset>';
}
function parse_price_rows($raw) {
    $lines = preg_split('/\r\n|\r|\n/', (string) $raw);
    $out = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $cells = array_map('trim', explode('|', $line));
        $out[] = $cells;
    }
    return $out;
}
