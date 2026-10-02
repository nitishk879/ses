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
    'slot_none_chosen' => '確定する前に日時をお選びください。',
    'slot_outside_window' => 'この招待でお選びいただけるのは :date までです。それより前の日時をお選びください。',
    'slot_not_a_working_day' => '土日は面接を実施しておりません。平日をお選びください。',
    'slot_off_grid' => ':from から :to の間で、表示されている日時からお選びください。',
    'slot_too_soon' => '準備のため、現在時刻から :hours 時間以降の日時をお選びください。',
    'calendar_nothing_left' => 'この招待でお選びいただける日時は残っていません。',
    'already_scheduled_notice' => '面接は既に :date に予定されています。',
    'session_expired_retry' => 'ページを開いたままセッションの有効期限が切れました。もう一度日時をお選びください。',

    'slot_time_unreadable' => '「:value」は日時として読み取れません。日付と時刻をお選びください。',

    'slot_time_in_past' => ':value はすでに過ぎています。現在時刻より後の日時をお選びください。',

    'no_slots_chosen' => '提示する日時が選ばれていません。1つ以上入力するか、すべて空欄にして自動選択にしてください。',

    'not_reschedulable' => 'この面接は再調整できません（現在のステータス: :status）。',

    'no_email_to_reschedule' => 'この候補者にはメールアドレスが登録されていないため、再案内を送信できません。',

    'no_slots_available' => '設定された期間内に空き日時がありません。',

    'mail' => [
        'subject' => '面接のご案内 — :project',
        'greeting' => ':name 様',
        'subject_rescheduled' => '面接日時の再調整のご案内 — :project',
        'heading_rescheduled' => '面接日時を再調整させていただきます',
        'intro_rescheduled' => ':project についてご案内した日時が難しくなりましたので、あらためて候補日時をお送りします。先にお選びいただいた日時は取り消しとなります。',
        'heading' => '書類選考を通過されました',
        'intro' => ':project の一次スクリーニング面接にご案内いたします。',
        'about' => '面接は自動化されており、所要時間は約 :minutes 分です。ご選択いただいた日時にお電話いたします。',
        'slots_heading' => 'ご選択可能な日時',
        'window_heading' => 'ご都合のよい日時をお選びください',
        'window_days' => '本日から :until まで、毎日ご選択いただけます。',
        'window_hours' => '時間帯は :from 〜 :to（:timezone）です。',
        'none_suitable_calendar' => 'この2週間でご都合の合う日時がない場合は、本メールへの返信は不要です。担当者より別途ご連絡いたします。',
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

        'invite_confirm' => '候補者に実際のメールが送信されます。続行しますか？',
        'project' => '案件',
        'bot_required' => '先に面接ボットを保存してください。質問はボットのプロンプトに記述されているため、ボットがない状態では招待を送信できません。',
        'bot_unsaved' => 'ここに表示されているボットはまだ保存されていません。保存してください。保存しない場合、招待には案件に元々設定されていたボットが使われます。',
        'no_bot_warning' => 'この案件にはボットが設定されていません。面接はボットのプロンプトに書かれた質問で行われるため、ボットを選択するまで招待は送信できません。',
        'conducted_by' => '担当ボット',
        'conducted_by_generated' => 'なし（SESが自動生成した質問）',
        'slot_times' => '提示する日時',
        'calendar_default' => '候補者には :days 日間のカレンダーが表示され、:from 〜 :to（:zone）の中からご自身で予約されます。',
        'pin_times' => '日時を指定して提示する',
        'calendar_offered' => 'カレンダーを提示済み。',
        'calendar_offered_detail' => '候補者は :until まで空いている日時を自由に予約できます。',
        'slot_times_help' => 'カレンダーの代わりに特定の日時を提示する場合のみ入力してください。入力した日時のみが候補者に提示されます。:zone の時刻で、現在時刻より後を指定してください。空欄のままにするとカレンダーが送られます。',
        'slot_times_required' => '面接候補の日時を3つすべて選択してください。入力した日時のみが候補者に提示されます。',
        'reschedule' => '日時を再調整',
        'reschedule_help' => '提示済みの日時（候補者が選択済みのものを含む）を取り消し、新しい招待をメールで送ります。空欄のままにすると新しいカレンダーが送られ、入力するとその日時のみが提示されます。',
        'reschedule_confirm' => '現在の予約を取り消し、候補者に新しい日時をメールします。よろしいですか。',
        'rescheduled' => '再調整しました。新しい日時を候補者にメールしました。',
        'threshold' => '基準スコア',


        // Choosing which DenAI dashboard bot conducts the calls.
        'interview_bot' => '面接ボット',
        'interview_bot_help' => 'このプロジェクトの候補者に電話するボットを選びます。ボットの作成と文言の編集は DenAI ダッシュボードで行います。',
        'bot' => 'ボット',
        'no_bot' => '— ボットなし（自動生成のスクリプトのみ）—',
        'save_bot' => '保存',
        'bot_assigned' => '面接ボットを保存しました。',
        'bot_cleared' => '面接ボットを解除しました。自動生成のスクリプトのみで発信します。',
        'bot_id_placeholder' => 'ダッシュボードURLのボットID',
        'bot_invalid' => 'ボットIDの形式ではありません。URL全体ではなく、ボットのIDだけを貼り付けてください。',
        'bot_list_unavailable' => 'DenAI ダッシュボードからボット一覧を取得できませんでした。ボットのURLに含まれるIDを貼り付けてください。',
        'invited' => ':count 名を招待しました。',

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
        'conversation' => '面接内容',
        'conversation_reconstructed' => '通話の書き起こしが保存されていないため、記録済みの質問と回答から再構成した内容です。それ以外の発言は含まれていません。',
        'questions_matched' => '予定していた質問 :total 件のうち :matched 件を特定',
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
        'choose_intro_calendar' => ':project の一次スクリーニング面接について、ご都合のよい日時をお選びください。まず日付を選び、次に時刻をお選びください。',
        'calendar_hours' => '毎日 :from 〜 :to の間でお選びいただけます。',
        'calendar_expires' => 'このリンクは :date 以降はご利用いただけません。',
        'day_full' => '満枠',
        'day_free' => '空き :count 枠',
        'day_full_long' => 'この日の空きはありません。別の日をお選びください。',
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

    // ── CV matching: requirements and must-haves ──────────────────────
    'requirement' => [
        'heading' => 'この案件の要件',
        'help' => 'この案件に記載された内容のみ表示されます：案件フォーム（カテゴリ・必要な実務経験・言語・勤務地・単価）と案件内容の本文。変更は案件フォームで行ってください。このクライアントにとって必須の項目をオンにしてください。スコアは全項目で算出されます。必須指定は「誰が条件を満たすか」を決めるもので、点数を変えるものではありません。',
        'mandatory_count' => '必須 :count 件',
        'none' => 'この案件にはまだ要件がありません。案件フォームで入力してください。',
        'stale' => '案件から削除済み — 適用されません',
        'edit_in_project' => '案件フォームで編集',
        'needs_number' => '年数の入力が必要',
        'years' => '年',
        'experience_label' => '実務経験 :duration 以上',
        'duration_years' => ':count年',
        'duration_months' => ':countヶ月',

        'origin' => [
            'project_form' => '案件フォーム',
            'jd_text' => '案件内容の本文',
            'recruiter' => '手動で追加',
        ],

        'from_project_form' => '案件フォームで指定された必須条件です。',
        'location_label' => '案件の勤務地で就業可能',
        'budget_label' => '月額単価 :amount 円以内',
        'kind' => [
            'skill' => 'スキル',
            'experience' => '経験',
            'language' => '言語',
            'location' => '勤務地',
            'budget' => '単価',
        ],
    ],

    // ── CV matching: the run and its results ────────────────────────
    'match_run' => [
        'title' => '候補者マッチング',
        'open_screen' => 'マッチング画面を開く',
        'to_interviews' => '面接一覧',
        'analyze' => 'マッチングを実行',
        'starting' => '開始しています…',
        'already_running' => 'この案件のマッチングはすでに実行中です。',
        'started' => 'マッチングを開始しました。:count 件の職務経歴書を読み込んでいます。完了時にメールでお知らせします。',
        'in_progress' => ':count 名をマッチング中です… この画面は自動で更新されます。',
        'failed' => '前回のマッチングは完了しませんでした：:reason',
        'never_run' => 'この案件ではまだマッチングを実行していません。',
        'last_run' => '前回の実行：:when — :scored 名を採点しました。',
        'parse_failures' => ':count 件の職務経歴書を読み取れませんでした。',
        'gate_stale' => 'このスコアの算出後に必須条件が変更されています。反映するにはマッチングを再実行してください。',
        'no_jd' => '案件内容を解析できなかったため、候補者を採点できませんでした。',
        'project_gone' => '採点の完了前に案件が削除されました。',

        'tab' => [
            'matched' => '条件を満たす',
            'review' => '要確認',
            'all' => '採点済みすべて',
        ],
        'review_help' => 'スコアは基準を満たしていますが、書類からは必須条件の可否を判断できなかった候補者です。日付のない職歴や、言語の記載がない場合によく発生します。不合格ではありません。',

        'search' => '候補者を検索…',
        'candidate' => '候補者',
        'score' => 'マッチ度',
        'must_haves' => '必須条件',
        'why' => '理由',
        'no_must_haves' => '必須条件の指定なし',
        'unnamed' => '候補者 #:id',
        'already_invited' => '案内済み',
        'already_invited_hint' => 'この候補者には有効な案内が送信済みのため、送信時はスキップされます。',

        'bot_set' => 'ボット設定済み',
        'bot_missing' => 'ボット未設定',

        'select_page' => 'このページの候補者をすべて選択',
        'select_all' => ':count 名すべてを選択',
        'selected' => ':count 名を選択中',
        'clear_selection' => '選択を解除',
        'nothing_selected' => '候補者が選択されていません。',
        'all_already_invited' => '選択した :count 名全員に有効な案内が送信済みです。何も送信していません。日時を再提示する場合は面接詳細の「再調整」をご利用ください。',
        'skipped_already_invited' => ':count 名には有効な案内が送信済みのため、再送していません。',
        'some_unreachable' => '一部の候補者に連絡できませんでした: :failures',
        'invite_selected' => '選択した候補者に案内',
        'slot_n' => '候補日時 :n',

        'empty_never_run' => 'まだスコアがありません。「マッチングを実行」を押して職務経歴書を読み込んでください。',
        'empty_filter' => 'この表示に該当する候補者はいません。スコアを下げるか、タブを切り替えてください。',

        'mail' => [
            'subject' => '「:project」に :count 名がマッチしました',
            'heading' => 'この案件に :count 名がマッチしました',
            'greeting' => ':name 様',
            'intro' => ':project のマッチングが完了しました。採点した :scored 名のうち :count 名が要件を満たしています。',
            'needs_review' => 'さらに :count 名は高スコアですが、書類から必須条件を判断できませんでした。「要確認」タブでご確認ください。',
            'preview_heading' => '上位のマッチ',
            'and_more' => '…ほか :count 名。',
            'unnamed' => '候補者 #:id',
            'cta' => 'マッチ結果をすべて見る',
            'parse_failures' => '補足：:count 件の職務経歴書を読み取れず、該当の候補者は採点できませんでした。',
            'footer' => 'ダッシュボードから候補者を選択し、面接日時をまとめて案内できます。',
        ],
    ],

    // ── AI interview evaluation digest ──────────────────────────────
    'evaluation_digest' => [
        'recommendation' => [
            'recommended' => '推奨',
            'maybe_recommended' => '要検討',
            'not_recommended' => '非推奨',
            'insufficient_data' => '判断材料が不足',
        ],

        'mail' => [
            'subject' => 'AI面接の評価が完了しました（:count 名）— :project',
            'heading' => ':count 名のAI面接評価が完了しました',
            'greeting' => ':name 様',
            'intro' => ':project の面接を受けた :count 名の採点が完了しました。',
            'recommended' => 'そのうち :count 名が次のステップに推奨されています。',
            'preview_heading' => '上位スコア',
            'and_more' => '…ほか :count 名。',
            'unnamed' => '候補者（評価 #:id）',
            'cta' => '面接内容を確認する',
            'footer' => '各候補者の画面で、面接のやり取り・スコア・その根拠をまとめて確認できます。',
        ],
    ],
];
