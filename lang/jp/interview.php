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

    // ── 招待と日程選択 (tasks 5-7) ───────────────────────────────────────────
    'slot_status' => [
        'offered' => '提示済み',
        'selected' => '選択済み',
        'expired' => '期限切れ',
        'released' => '解放済み',
    ],

    'invitation_expired' => 'この招待は有効期限が切れています。',
    'invitation_not_answered' => '有効期限までに候補者が日程を選択しませんでした。',
    'link_not_recognised' => 'このリンクは無効です。既に使用されたか、有効期限が切れている可能性があります。',
    'all_slots_passed' => '提示されたすべての日時が過ぎています。',
    'slot_no_longer_available' => 'その日時は選択できなくなりました。',
    'slot_just_taken' => 'その日時は先ほど他の方に確定されました。別の日時をお選びください。',
    'slot_not_for_this_interview' => 'その日時はこの招待のものではありません。',
    'already_scheduled_notice' => '面接は既に :date に予定されています。',
    'session_expired_retry' => 'ページを開いたままセッションの有効期限が切れました。もう一度日時をお選びください。',

    'mail' => [
        'subject' => '面接のご案内 — :project',
        'heading' => '書類選考を通過されました',
        'intro' => ':project の一次スクリーニング面接にご案内いたします。',
        'about' => '面接は自動化されており、所要時間は約 :minutes 分です。ご選択いただいた日時にお電話いたします。',
        'slots_heading' => 'ご選択可能な日時',
        'cta' => '日時を選択する',
        'expires' => ':date までにご選択ください。',
        'none_suitable' => 'ご都合の合う日時がない場合は、本メールへの返信は不要です。担当者より別途ご連絡いたします。',
        'recorded_notice' => 'ご回答確認のため、通話は録音させていただきます。',
        'regards' => 'よろしくお願いいたします。',
        'button_fallback' => '上のボタンが動作しない場合は、以下のURLをブラウザに貼り付けてください。',
    ],

    // ── 採用担当者ダッシュボード (task 16) ──────────────────────────────────
    'dashboard' => [
        'title' => '面接一覧',
        'subtitle' => '担当案件のスクリーニング面接 — 招待、日程、結果。',
        'detail_title' => '面接詳細',
        'back' => '面接一覧へ戻る',

        'actions' => '操作',
        'run_matching' => 'マッチングを実行',
        'run_matching_help' => '求人情報と未解析の職務経歴書を解析し、全候補者をこの案件に対してスコアリングします。バックグラウンドで実行されます。',
        'invite_shortlist' => '候補者を招待',
        'invite_help' => '基準スコア以上の候補者全員に、3つの面接日時を提示するメールを送信します。',
        'invite_confirm' => '候補者に実際のメールが送信されます。続行しますか？',
        'project' => '案件',
        'choose_project' => '案件を選択…',
        'threshold' => '基準スコア',
        'run' => '実行',
        'send' => '送信',

        'matching_queued' => 'マッチングを登録しました。:count 件の職務経歴書を解析中です。1分ほどで再読み込みしてください。',

        // Choosing which DenAI dashboard bot conducts the calls.
        'interview_bot' => '面接ボット',
        'interview_bot_help' => 'このプロジェクトの候補者に電話するボットを選びます。ボットの作成と文言の編集は DenAI ダッシュボードで行います。',
        'bot' => 'ボット',
        'no_bot' => '— ボットなし（自動生成のスクリプトのみ）—',
        'save_bot' => '保存',
        'bot_assigned' => '面接ボットを保存しました。',
        'bot_cleared' => '面接ボットを解除しました。自動生成のスクリプトのみで発信します。',
        'bot_id_placeholder' => 'ダッシュボードURLのボットID',
        'bot_list_unavailable' => 'DenAI ダッシュボードからボット一覧を取得できませんでした。ボットのURLに含まれるIDを貼り付けてください。',
        'no_shortlist' => 'この案件で :threshold 以上の候補者はまだいません。',
        'invited' => ':count 名を招待しました。',
        'invited_with_failures' => ':count 名を招待しました。一部は送信できませんでした： :failures',

        'stat_total' => '合計',
        'stat_awaiting_reply' => '返信待ち',
        'stat_scheduled' => '日程確定',
        'stat_completed' => '完了',
        'stat_needs_attention' => '要対応',

        'search' => '候補者',
        'search_placeholder' => '氏名またはメール',
        'status' => 'ステータス',
        'all_statuses' => 'すべてのステータス',
        'all_projects' => 'すべての案件',
        'filter' => '絞り込む',
        'clear' => 'クリア',

        'candidate' => '候補者',
        'unknown_candidate' => '不明な候補者',
        'match' => 'マッチ度',
        'scheduled' => '予定日時',
        'result' => '結果',
        'view' => '詳細',
        'duration' => '通話時間',

        'empty' => '面接はまだありません。',
        'empty_help' => '案件でマッチングを実行し、候補者を招待してください。',

        'slots' => '提示した日時',
        'questions' => '質問内容',
        'no_questions' => 'この面接の質問はまだ作成されていません。',
        'transcript' => '通話記録',
        'no_transcript' => '通話記録はありません — 通話が未実施か、発言が記録されませんでした。',
        'recording' => '録音',
        'interviewer' => '面接官',
        'pii_notice' => '保存された通話記録では個人情報はマスクされています。',

        'evaluation' => '評価',
        'no_evaluation' => '未評価です。通話記録が作成された時点で評価されます。',
        'technical_fit' => '技術適合度',
        'jd_fit' => '案件適合度',
        'communication' => 'コミュニケーション',
        'coverage' => '回答数',
        'evidence' => '根拠',
        'prompt_version' => '評価基準',
        'attempts' => '発信履歴',
    ],

    'page' => [
        'title' => '面接日程の調整',
        'choose_title' => '面接日時をお選びください',
        'choose_intro' => ':project の一次スクリーニング面接について、以下から日時をお選びください。',
        'duration_note' => '面接は自動化されており、所要時間は約 :minutes 分です。ご選択の日時にお電話いたします。',
        'times_shown_in' => '表示はすべて :timezone の時刻です。',
        'confirm_button' => 'この日時で確定する',
        'confirmed_title' => '面接の日時が確定しました',
        'confirmed_intro' => ':project について、上記の日時にお電話いたします。',
        'confirmed_what_happens' => '電波の良い静かな場所でお待ちください。お出になれなかった場合は改めておかけ直しします。',
        'unavailable_title' => 'このリンクはご利用いただけません',
        'unavailable_next' => 'ご参加をご希望の場合は、招待メールにご返信ください。担当者が新しい日時を調整いたします。',
        'recorded_notice' => 'ご回答確認のため、通話は録音させていただきます。',
        'footer_note' => 'このリンクは選考通過者の方にお送りしています。お心当たりがない場合は破棄してください。',
    ],
];
