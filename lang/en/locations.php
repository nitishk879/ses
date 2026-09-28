<?php

/*
 * Work locations, keyed by `locations.slug`.
 *
 * The `locations` table stores romaji titles ("Tokyo", "Kanagawa"), and every
 * screen printed that column directly — so the work-location list stayed in
 * English even with the interface set to Japanese. Translating here rather
 * than rewriting the column keeps one row per prefecture, leaves the English
 * interface unchanged, and needs no migration on a table other records point
 * at. `slug` is the key because it is stable: it is derived once at seed time
 * and never shown to anyone.
 *
 * A slug with no entry here falls back to the stored title — see
 * Location::displayTitle() — so a location added later is readable before it
 * is translated.
 */

return [
    'tokyo' => 'Tokyo',
    'kanagawa' => 'Kanagawa',
    'chiba' => 'Chiba',
    'ibaraki' => 'Ibaraki',
    'saitama' => 'Saitama',
    'tochigi' => 'Tochigi',
    'gunma' => 'Gunma',
    'osaka' => 'Osaka',
    'aomori' => 'Aomori',
    'akita' => 'Akita',
    'fukushima' => 'Fukushima',
    'yamagata' => 'Yamagata',
    'iwate' => 'Iwate',
    'niigata' => 'Niigata',
    'toyama' => 'Toyama',
    'ishikawa' => 'Ishikawa',
    'fukui' => 'Fukui',
    'yamanashi' => 'Yamanashi',
    'nagano' => 'Nagano',
    'gifu' => 'Gifu',
    'shizuoka' => 'Shizuoka',
    'aichi' => 'Aichi',
    'mie' => 'Mie',
    'shiga' => 'Shiga',
    'miyagi' => 'Miyagi',
    'kyoto' => 'Kyoto',
    'hyogo' => 'Hyogo',
    'nara' => 'Nara',
    'wakayama' => 'Wakayama',
    'tottori' => 'Tottori',
    'shimane' => 'Shimane',
    'okayama' => 'Okayama',
    'hiroshima' => 'Hiroshima',
    'yamaguchi' => 'Yamaguchi',
    'tokushima' => 'Tokushima',
    'kagawa' => 'Kagawa',
    'ehime' => 'Ehime',
    'kochi' => 'Kochi',
    'fukuoka' => 'Fukuoka',
    'saga' => 'Saga',
    'nagasaki' => 'Nagasaki',
    'kumamoto' => 'Kumamoto',
    'oita' => 'Oita',
    'miyazaki' => 'Miyazaki',
    'kagoshima' => 'Kagoshima',
    'okinawa' => 'Okinawa',
];
