<?php

/*
 * The Japanese half of `common/common`.
 *
 * This file did not exist. Every key in it was reached through
 * `__('common/common.…')` on pages that are otherwise fully translated, and
 * with no Japanese file Laravel falls back to the English one — so the sample
 * dialog, its two buttons and the auth screens stayed in English however the
 * interface was set.
 */

return array(
    'login' => 'ログイン',
    'register' => '新規登録',
    'register_request' => 'アカウントをお持ちでない方はこちらから登録してください。',
    'login_request' => 'すでにアカウントをお持ちの方はこちらからログインしてください。',
    'auth_content_title' => '10,000人以上が利用するコミュニティにご参加ください。',
    'auth_content_text' => '最適な人材をお探しの方も、案件を管理される方も、優秀なエンジニアを探されている方も、本サービスが最良のプロフェッショナルとおつなぎします。今すぐご登録ください。',
    'auth_content_btn_1' => 'AI活用',
    'auth_content_btn_2' => 'セキュア',
    'auth_content_btn_3' => '信頼性',
    'unauthorised_page_title' => 'アクセス権限がありません',
    '401_error_title' => '401エラー：このページへのアクセス権限がありません。',
    '401_error_text' => 'お探しのページにアクセスする権限がありません。',
    '404_error_title' => '404エラー',
    '404_error_text' => 'お探しのページは見つかりませんでした。',
    '500_error_title' => '500エラー',
    '500_error_text' => 'URLが正しいかご確認ください。',
    'back_to_home' => 'ホームへ戻る',
    'talent_registration_title' => '人材として登録する',
    'member_registration_title' => '案件掲載者として登録する',
    'use' => '反映する',
    'close' => '閉じる',
    'sample_data_title' => '入力例',
    'modal_title' => '入力例',
    // The project form's submit button, which was `__("Submit")` — a bare
    // string with no entry anywhere, so it printed itself.
    'submit' => '登録する',
    'date_format' => 'Y年n月j日',
);
