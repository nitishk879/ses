<?php

/*
 * Japanese counterpart of lang/en/interview.php. Keys must stay in step with
 * it: a missing key here falls back to rendering the key itself, not to the
 * English string.
 */
return [
    // Resource lifecycle
    'attempt_deleted' => '面接の試行を削除しました。',
    'question_deleted' => '質問を削除しました。',
    'question_already_asked' => 'この質問は既に実施済みのため削除できません。',
    'answer_already_exists' => 'この質問の回答は既に登録されています。',
    'answer_saved' => '回答を保存しました。',
    'evaluation_queued' => '評価を登録しました。',

    // Call outcomes
    'no_answer' => '候補者が応答しませんでした。',
    'call_failed' => '通話を完了できませんでした。',
    'no_transcript' => '候補者の発言が記録されないまま通話が終了しました。',
    'poll_timeout' => '通話の終了確認を打ち切りました。結果は不明です。',
    'max_attempts_reached' => '発信回数の上限に達しました。',
    'slot_missed' => '予定時刻を過ぎたため面接を開始できませんでした。',

    // Preconditions
    'phone_not_dialable' => '候補者 :talent の電話番号が発信可能な形式ではありません。',
    'jd_not_parsed' => 'この案件の求人情報がまだ解析されていません。',
    'resume_not_parsed' => 'この候補者の職務経歴書がまだ解析されていません。',
    'all_lines_busy' => '面接用の回線がすべて使用中のため発信しませんでした。',
    'ai_unavailable' => 'AI面接サービスに接続できませんでした。',
    'already_in_progress' => 'この試行は既に質問を開始しているため再開できません。新しい試行を作成してください。',

    'question_type' => [
        'core' => '基本',
        'jd_specific' => '案件別',
        'candidate_specific' => '候補者別',
        'follow_up' => '追加質問',
    ],
];
