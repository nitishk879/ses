  <?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute フィールドを受け入れる必要があります。',
    'accepted_if' => ' :other が :value の場合、:attribute フィールドは受け入れられる必要があります。',
    'active_url' => ':attribute フィールドは有効な URL である必要があります。',
    'after' => ':attribute フィールドは :date 以降の日付である必要があります。',
    'after_or_equal' => ':attribute フィールドは :date 以降の日付である必要があります。',
    'alpha' => ':attribute フィールドには文字のみを含めることができます。',
    'alpha_dash' => ':attribute フィールドには、文字、数字、ダッシュ、アンダースコアのみを含めることができます。',
    'alpha_num' => ':attribute フィールドには文字と数字のみを含めることができます。',
    'array' => ':attribute フィールドは array である必要があります。',
    'ascii' => ':attribute フィールドには、1 バイトの英数字と記号のみを含める必要があります。',
    'before' => ':attribute フィールドは :date より前の日付である必要があります。',
    'before_or_equal' => ':attribute フィールドは :date 以前の日付である必要があります。',
    'between' => [
        'array' => ':attribute フィールドには、:min から :max までの項目が必要です。',
        'file' => ':attribute フィールドは、:min キロバイトから :max キロバイトの範囲になければなりません。',
        'numeric' => ':attribute フィールドは :min と :max の間になければなりません。',
        'string' => ':attribute フィールドは :min 文字から :max 文字までの範囲でなければなりません。',
    ],
    'boolean' => ':attribute フィールドは true または false である必要があります。',
    'can' => ':attribute フィールドに許可されていない値が含まれています。',
    'confirmed' => ':attribute フィールドの確認が一致しません。',
    'contains' => ':attribute フィールドに必要な値がありません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attribute フィールドは有効な日付である必要があります。',
    'date_equals' => ':attribute フィールドは :date と同じ日付である必要があります。',
    'date_format' => ':attribute フィールドは :format 形式と一致する必要があります。',
    'decimal' => ':attribute フィールドには :decimal の小数点以下の桁数が必要です。',
    'declined' => ':attribute フィールドは拒否する必要があります。',
    'declined_if' => ':other が :value の場合、:attribute フィールドは拒否される必要があります。',
    'different' => ':attribute フィールドと :other は異なる必要があります。',
    'digits' => ':attribute フィールドは :digits digitsである必要があります。',
    'digits_between' => ':attribute フィールドは :min digitから :max digit までの範囲でなければなりません。',
    'dimensions' => ':attribute フィールドに無効な画像サイズがあります。',
    'distinct' => ':attribute フィールドに重複した値があります。',
    'doesnt_end_with' => ':attribute フィールドは、次のいずれかで終わってはなりません: :values。',
    'doesnt_start_with' => ':attribute フィールドは、次のいずれかで始まってはなりません: :values。',
    'email' => ':attribute フィールドは有効な電子メール アドレスである必要があります。',
    'ends_with' => ':attribute フィールドは、次のいずれかで終わる必要があります: :values。',
    'enum' => '選択された :attributeは無効です。',
    'exists' => '選択された :attributeは無効です。',
    'extensions' => ':attribute フィールドには、次のいずれかの extensions: :values が必要です。',
    'file' => ':attribute フィールドはファイルである必要があります。',
    'filled' => ':attribute フィールドには value が必要です。',
    'gt' => [
        'array' => ':attribute フィールドには少なくとも :value 項目が必要です。',
        'file' => ':attribute フィールドは :value キロバイトより大きくを超えてはなりません。',
        'numeric' => ':attribute フィールドは :value より大きく超えてはなりません。',
        'string' => ':attribute フィールドは :value 文字より大きくを超えてはなりません.',
    ],
    'gte' => [
        'array' => ':attribute フィールドには :value 項目以上が必要です。',
        'file' => ':attribute フィールドは :value キロバイト以上である必要があります。',
        'numeric' => ':attribute フィールドは :value 以上である必要があります。',
        'string' => ':attribute フィールドは :value 文字以上である必要があります。',
    ],
    'hex_color' => ':attribute フィールドは有効な Hexadecimal の色である必要があります。',
    'image' => ':attribute フィールドは画像である必要があります。',
    'in' => '選択された:attributeは無効です。',
    'in_array' => ':attribute フィールドは :other 内に存在する必要があります。',
    'integer' => ':attribute フィールドは整数である必要があります。',
    'ip' => ':attribute フィールドは有効な IP アドレスである必要があります。',
    'ipv4' => ':attribute フィールドは有効な IPv4 アドレスである必要があります。',
    'ipv6' => ':attribute フィールドは有効な IPv6 アドレスである必要があります。',
    'json' => ':attribute フィールドは有効な JSON 文字列である必要があります。',
    'list' => ':attribute フィールドはリストである必要があります。',
    'lowercase' => ':attribute フィールドは小文字にする必要があります。',
    'lt' => [
        'array' => ':attribute フィールドには :value 項目未満が必要です。',
        'file' => ':attribute フィールドは :value より小さくなければなりません。',
        'numeric' => ':attribute フィールドは :value より小さくなければなりません。',
        'string' => ':attribute フィールドは :value 文字未満である必要があります。',
    ],
    'lte' => [
        'array' => ':attribute フィールドには、:value 項目を超える項目を含めることはできません。',
        'file' => ':attribute フィールドは :value キロバイト以下である必要があります。',
        'numeric' => ':attribute フィールドは :value 以下である必要があります。',
        'string' => ':attribute フィールドは :value 文字以下である必要があります。',
    ],
    'mac_address' => ':attribute フィールドは有効な MAC アドレスである必要があります。',
    'max' => [
        'array' => ':attribute フィールドには :max を超える項目を含めることはできません。',
        'file' => ':attribute フィールドは :max キロバイトを超えてはなりません。',
        'numeric' => ':attribute フィールドは :max より大きくすることはできません。',
        'string' => ':attribute フィールドは :max 文字数を超えてはなりません。',
    ],
    'max_digits' => ':attribute フィールドには :max digit を超える文字数を指定することはできません。',
    'mimes' => ':attribute フィールドは、タイプ: :values のファイルである必要があります。',
    'mimetypes' => ':attribute フィールドは、タイプ: :values のファイルである必要があります。',
    'min' => [
        'array' => ':attribute フィールドには少なくとも :min 個の項目が必要です。',
        'file' => ':attribute フィールドは少なくとも :min キロバイトである必要があります。',
        'numeric' => ':attribute フィールドは少なくとも :min である必要があります。',
        'string' => ':attribute フィールドは少なくとも :min 文字である必要があります。',
    ],
    'min_digits' => ':attribute フィールドには少なくとも :min 桁が必要です。',
    'missing' => ':attribute フィールドが欠落している必要があります。',
    'missing_if' => ':other が :value の場合、:attribute フィールドは省略する必要があります。',
    'missing_unless' => ':other が :value でない限り、:attribute フィールドは省略する必要があります。',
    'missing_with' => ':values が存在する場合、:attribute フィールドは欠落している必要があります。',
    'missing_with_all' => ':values が存在する場合、:attribute フィールドは欠落している必要があります。',
    'multiple_of' => ':attribute フィールドは :value の倍数である必要があります。',
    'not_in' => '選択された:attributeは無効です。',
    'not_regex' => ':attribute フィールドの形式が無効です。',
    'numeric' => ':attribute フィールドは数値である必要があります。',
    'password' => [
        'letters' => ':attribute フィールドには少なくとも 1 文字が含まれている必要があります。',
        'mixed' => ':attribute フィールドには、少なくとも 1 つの大文字と 1 つの小文字が含まれている必要があります。',
        'numbers' => ':attributeフィールドには少なくとも1つの数字が含まれている必要があります.',
        'symbols' => ':attribute フィールドには少なくとも 1 つのシンボルが含まれている必要があります。',
        'uncompromised' => '指定された :attribute はデータ漏洩に発生しました。別の :attribute を選択してください。',
    ],
    'present' => ':attribute フィールドが存在する必要があります。',
    'present_if' => ':other が :value の場合、:attribute フィールドが存在する必要があります。',
    'present_unless' => ':other が :value でない限り、:attribute フィールドが存在する必要があります。',
    'present_with' => ':values が存在する場合は、:attribute フィールドも存在する必要があります。',
    'present_with_all' => ':values が存在する場合は、:attribute フィールドも存在する必要があります。',
    'prohibited' => ':attribute フィールドは禁止されています。',
    'prohibited_if' => ':other が :value の場合、:attribute フィールドは禁止されます。',
    'prohibited_unless' => ':other が :values に含まれていない限り、:attribute フィールドは禁止されます。',
    'prohibits' => ':attribute フィールドでは :other の存在が禁止されます。',
    'regex' => ':attribute フィールドの形式が無効です。',
    'required' => ':attribute フィールドは必須です。',
    'required_array_keys' => ':attribute フィールドには、:values のエントリが含まれている必要があります。',
    'required_if' => ':other が :value の場合、:attribute フィールドは必須です。',
    'required_if_accepted' => ':other が受け入れられる場合、:attribute フィールドは必須です。',
    'required_if_declined' => ':other が拒否された場合、:attribute フィールドは必須です。',
    'required_unless' => ':other が :values に含まれていない限り、:attribute フィールドは必須です。',
    'required_with' => ':values が存在する場合、:attribute フィールドは必須です。',
    'required_with_all' => ':values が存在する場合、:attribute フィールドは必須です。',
    'required_without' => ':values が存在しない場合は、:attribute フィールドが必須です。',
    'required_without_all' => ':values がいずれも存在しない場合は、:attribute フィールドが必須です。',
    'same' => ':attribute フィールドは :other と一致する必要があります。',
    'size' => [
        'array' => ':attribute フィールドには :size 項目が含まれている必要があります。',
        'file' => ':attribute フィールドは :size キロバイトである必要があります。',
        'numeric' => ':attribute フィールドは :size である必要があります。',
        'string' => ':attribute フィールドは :size 文字である必要があります。',
    ],
    'starts_with' => ':attribute フィールドは、次のいずれかで始まる必要があります: :values。',
    'string' => ':attribute フィールドは文字列である必要があります。',
    'timezone' => ':attribute フィールドは有効なタイムゾーンである必要があります。',
    'unique' => ':attribute はすでに使用されています。',
    'uploaded' => ':attribute のアップロードに失敗しました。',
    'uppercase' => ':attribute フィールドは大文字にする必要があります。',
    'url' => ':attribute フィールドは有効な URL である必要があります。',
    'ulid' => ':attribute フィールドは有効な ULID である必要があります。',
    'uuid' => ':attribute フィールドは有効な UUID である必要があります。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
