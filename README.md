# MemoCRM（簡易CRM：顧客メモ＋タグ）

顧客情報に **メモ** と **タグ** を紐付けて管理できる、  
**業務向けの簡易CRMアプリ**です。

- モバイルアプリ（Flutter）＋ API（Laravel）を分離した構成
- 実務を想定した **Docker開発環境**
- API認証・CRUD・多対多リレーションを含む設計

ポートフォリオ用途として  
**「Flutter × API の実装ができる」** ことを明確に示す目的で作成しています。

---

## 主な機能

### 顧客管理
- 顧客一覧 / 詳細表示
- 顧客の作成・編集・削除
- 名前検索

### メモ管理
- 顧客ごとのメモ一覧
- メモの追加・編集・削除

### タグ管理
- タグの作成・編集・削除
- 顧客に複数タグを紐付け
- タグによる顧客絞り込み

### 認証（API）
- トークンベース認証（Laravel Sanctum）
- 認証必須API設計

---

## システム構成

```
  [ Flutter App ]
        │
        │ HTTPS / HTTP (Dio)
        ▼
    [ Nginx ]
        ▼
[ Laravel API (PHP-FPM) ]
        ▼
    [ MySQL ]
```

---

## 技術スタック

### フロントエンド（Flutter）
- Flutter
- Dart
- Riverpod（状態管理）
- go_router（画面遷移）
- Dio（HTTP / HTTPS 通信）
- Freezed / json_serializable（Model生成）

### バックエンド（API）
- Laravel 11
- PHP 8.3（PHP-FPM）
- Laravel Sanctum（API認証）
- Eloquent ORM
- REST API

### インフラ / 開発環境
- Docker / Docker Compose
- Nginx
- MySQL 8
- mkcert（ローカルHTTPS ※任意）

---

## ディレクトリ構成（API側）

```
memocrm-api/
├─ app/
│  ├─ Http/
│  │  ├─ Controllers/Api
│  │  ├─ Requests/Api
│  │  └─ Resources/Api
│  ├─ Models
│  └─ Services
├─ database/
│  ├─ migrations
│  └─ seeders
├─ routes/
│  ├─ web.php
│  └─ api.php
└─ ...
```

---

## 初回セットアップ（HTTP）

### 1. Dockerコンテナ起動
```bash
cd memocrm
docker compose up -d --build
```

### 2. Laravel依存関係インストール
```bash
docker compose exec app composer install
```

### 3. 環境変数ファイル作成
```bash
cp memocrm-api/.env.example memocrm-api/.env
```

### 4. APP_KEY生成
```bash
docker compose exec app php artisan key:generate
```

### 5. マイグレーション実行
```bash
docker compose exec app php artisan migrate
```

アクセス：
```
http://localhost:8080
```

---

## HTTPS（ローカル・任意）

※ HTTPS は **任意**です。  
通常は HTTP のまま開発可能です。

### mkcert インストール（Mac）
```bash
brew install mkcert
brew install nss
mkcert -install
```

### 証明書作成
```bash
mkdir -p docker/nginx/certs

mkcert \
  -key-file docker/nginx/certs/localhost-key.pem \
  -cert-file docker/nginx/certs/localhost.pem \
  localhost 127.0.0.1 ::1
```

### HTTPS起動
```bash
docker compose -f docker-compose.yml -f docker-compose.https.yml up -d
```

アクセス：
```
https://localhost:8443
```

---

## API 動作確認

```http
GET /api/health
```

レスポンス例：
```json
{
  "ok": true
}
```

---

## このアプリで意識した点

- モバイルアプリとAPIを分離した構成
- Dockerによる開発環境の完全再現
- HTTP / HTTPS の切り替え可能な構成
- 多対多（Customer × Tag）のDB設計
- Controllerを肥大化させない設計
- 副業・業務委託案件を想定した粒度

---

## 想定ユースケース
- 小規模チームの顧客管理
- 営業メモ管理
- Flutter × API 構成のサンプル実装

---

## Author
- クニ　扱える言語（Flutter / Swift / PHP / SQL Server）
