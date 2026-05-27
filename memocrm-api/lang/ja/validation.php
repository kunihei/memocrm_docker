<?php

return [
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列で入力してください。',
    'integer' => ':attributeは整数で入力してください。',
    'numeric' => ':attributeは数値で入力してください。',
    'boolean' => ':attributeは真偽値で入力してください。',
    'email' => ':attributeは有効なメールアドレスで入力してください。',
    'regex' => ':attributeの形式が正しくありません。',
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください。',
        'numeric' => ':attributeは:max以下で入力してください。',
    ],
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください。',
        'numeric' => ':attributeは:min以上で入力してください。',
    ],
    'confirmed' => ':attributeが確認用の値と一致しません。',
    'unique' => ':attributeは既に使用されています。',
    'exists' => '選択された:attributeは正しくありません。',

    'attributes' => [
        'co_cd' => '会社コード',
        'co_name' => '会社名',
        'co_address' => '会社住所',
        'tanto_name' => '担当者名',
        'tanto_tel' => '担当者電話番号',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'device_name' => 'デバイス名',
        'refresh_token' => 'リフレッシュトークン',
        'tel' => '電話番号',
        'mail_address' => 'メールアドレス',
        'tag_cd' => 'タグコード',
        'tag_name' => 'タグ名',
        'memo_cd' => 'メモコード',
        'memo_title' => 'メモタイトル',
        'memo_content' => 'メモ内容',
    ],
];