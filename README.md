# HTTPで動かす初回セットアップ方法

## dockerでコンテナを起動

```
cd memocrm
docker compose up -d --build
```

## Laravel依存関係
```
docker compose exec app composer install
```

## env作成（まだなら）
```
cp memocrm-api/.env.example memocrm-api/.env
```

## APP_KEY
```
docker compose exec app php artisan key:generate
```

## マイグレーション
```
docker compose exec app php artisan migrate
```
