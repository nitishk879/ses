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
    'tokyo' => '東京都',
    'kanagawa' => '神奈川県',
    'chiba' => '千葉県',
    'ibaraki' => '茨城県',
    'saitama' => '埼玉県',
    'tochigi' => '栃木県',
    'gunma' => '群馬県',
    'osaka' => '大阪府',
    'aomori' => '青森県',
    'akita' => '秋田県',
    'fukushima' => '福島県',
    'yamagata' => '山形県',
    'iwate' => '岩手県',
    'niigata' => '新潟県',
    'toyama' => '富山県',
    'ishikawa' => '石川県',
    'fukui' => '福井県',
    'yamanashi' => '山梨県',
    'nagano' => '長野県',
    'gifu' => '岐阜県',
    'shizuoka' => '静岡県',
    'aichi' => '愛知県',
    'mie' => '三重県',
    'shiga' => '滋賀県',
    'miyagi' => '宮城県',
    'kyoto' => '京都府',
    'hyogo' => '兵庫県',
    'nara' => '奈良県',
    'wakayama' => '和歌山県',
    'tottori' => '鳥取県',
    'shimane' => '島根県',
    'okayama' => '岡山県',
    'hiroshima' => '広島県',
    'yamaguchi' => '山口県',
    'tokushima' => '徳島県',
    'kagawa' => '香川県',
    'ehime' => '愛媛県',
    'kochi' => '高知県',
    'fukuoka' => '福岡県',
    'saga' => '佐賀県',
    'nagasaki' => '長崎県',
    'kumamoto' => '熊本県',
    'oita' => '大分県',
    'miyazaki' => '宮崎県',
    'kagoshima' => '鹿児島県',
    'okinawa' => '沖縄県',
];
