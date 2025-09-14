# 都道府県抽出API 実装計画書

## プロジェクト概要

### 目的
顧客から送られてくる住所情報から都道府県を抽出するAPIを構築する。現在95%の成功率を、AI（OpenAI/Gemini）を活用して限りなく100%に近づける。

### 技術要件
- **フレームワーク**: Laravel 12
- **開発手法**: TDD（テスト駆動開発）- t-wadaさんの手法に準拠
- **アーキテクチャ**: クリーンアーキテクチャ
- **外部API**: OpenAI API / Gemini API（切り替え可能）
- **言語**: PHP 8.2+

## システム構成

### ディレクトリ構造

```
extend-prefecture-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── PrefectureController.php
│   │   └── Requests/
│   │       └── ExtractPrefectureRequest.php
│   ├── UseCases/
│   │   └── ExtractPrefectureUseCase.php
│   ├── Repositories/
│   │   ├── Interfaces/
│   │   │   └── PrefectureExtractorInterface.php
│   │   └── PrefectureExtractorRepository.php
│   ├── ExternalServices/
│   │   ├── Interfaces/
│   │   │   └── AIServiceInterface.php
│   │   ├── OpenAIService.php
│   │   └── GeminiService.php
│   └── Providers/
│       └── PrefectureServiceProvider.php
├── resources/
│   └── views/
│       └── prefecture/
│           └── index.blade.php
├── routes/
│   └── web.php
└── tests/
    ├── Feature/
    │   └── PrefectureExtractionTest.php
    └── Unit/
        ├── UseCases/
        │   └── ExtractPrefectureUseCaseTest.php
        └── ExternalServices/
            ├── OpenAIServiceTest.php
            └── GeminiServiceTest.php
```

## 実装詳細

### 1. フロントエンド

#### 画面仕様（Bladeテンプレート）
- **ファイル**: `resources/views/prefecture/index.blade.php`
- **構成要素**:
  - 住所入力フォーム
  - 送信ボタン
  - レスポンス表示エリア
- **バリデーション**:
  - 文字列型
  - 最大200文字

#### サンプルUI構成
```html
<form>
  <div class="form-group">
    <label>住所を入力してください</label>
    <input type="text" maxlength="200" required>
  </div>
  <button type="submit">都道府県を抽出</button>
</form>
<div id="result">
  <!-- APIレスポンス表示エリア -->
</div>
```

### 2. バックエンド

#### 2.1 ルーティング（routes/web.php）
```php
// 画面表示
Route::get('/prefecture', [PrefectureController::class, 'index']);

// API エンドポイント
Route::post('/api/extract-prefecture', [PrefectureController::class, 'extract']);
```

#### 2.2 コントローラー（PrefectureController.php）
**責務**: HTTPリクエスト/レスポンスの処理
- `index()`: 入力画面表示
- `extract()`: 都道府県抽出処理の実行

#### 2.3 リクエストバリデーション（ExtractPrefectureRequest.php）
**責務**: 入力値の検証
- 住所: 必須、文字列、最大200文字

#### 2.4 ユースケース（ExtractPrefectureUseCase.php）
**責務**: ビジネスロジックの実装
- 住所文字列を受け取り、都道府県を抽出
- リポジトリを通じてAIサービスを呼び出し
- エラーハンドリング

#### 2.5 リポジトリ

##### インターフェース（PrefectureExtractorInterface.php）
```php
interface PrefectureExtractorInterface
{
    public function extract(string $address): string;
}
```

##### 実装（PrefectureExtractorRepository.php）
**責務**: AIサービスの呼び出しと結果の返却

#### 2.6 外部サービス

##### インターフェース（AIServiceInterface.php）
```php
interface AIServiceInterface
{
    public function extractPrefecture(string $address): string;
}
```

##### OpenAI実装（OpenAIService.php）
- OpenAI APIを使用した都道府県抽出
- GPT-3.5/GPT-4モデルの利用

##### Gemini実装（GeminiService.php）
- Gemini APIを使用した都道府県抽出
- Gemini Proモデルの利用

#### 2.7 サービスプロバイダー（PrefectureServiceProvider.php）
**責務**: 依存性注入の設定
- 環境変数に基づいてAIサービスを切り替え
- インターフェースと実装のバインディング

### 3. 環境設定

#### .env設定例
```env
# AI Service Configuration
AI_SERVICE_PROVIDER=openai  # 'openai' or 'gemini'

# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=100
OPENAI_TEMPERATURE=0.3

# Gemini Configuration
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-pro
GEMINI_MAX_TOKENS=100
GEMINI_TEMPERATURE=0.3

# API Retry Configuration
API_MAX_RETRIES=3
API_TIMEOUT=30
```

## テスト戦略（TDD）

### テスト駆動開発のサイクル
1. **Red Phase**: 失敗するテストを書く
2. **Green Phase**: テストを通す最小限のコードを書く
3. **Refactor Phase**: コードを改善する

### テストケース

#### Feature Test（E2Eテスト）
- 正常系: 標準的な住所から都道府県を抽出
- 異常系:
  - 空文字列の処理
  - 200文字超過の処理
  - 都道府県が含まれない住所の処理

#### Unit Test
- UseCaseのロジックテスト
- OpenAIServiceのモックテスト
- GeminiServiceのモックテスト
- バリデーションテスト

### サンプルテストコード
```php
// tests/Feature/PrefectureExtractionTest.php
public function test_can_extract_prefecture_from_address()
{
    $response = $this->post('/api/extract-prefecture', [
        'address' => '東京都渋谷区道玄坂1-2-3'
    ]);

    $response->assertStatus(200);
    $response->assertJson(['prefecture' => '東京都']);
}
```

## エラーハンドリング

### API通信エラー
- リトライ機能（最大3回）
- タイムアウト設定（30秒）
- フォールバック処理

### ユーザーエラー
- バリデーションエラーメッセージの表示
- 分かりやすいエラー文言

## パフォーマンス最適化

### キャッシング
- 同一住所のリクエストをキャッシュ
- Redis/Memcachedの利用を検討

### レート制限
- API呼び出し回数の管理
- スロットリングの実装

## セキュリティ考慮事項

### APIキー管理
- 環境変数での管理
- .envファイルのgit除外

### 入力値検証
- SQLインジェクション対策
- XSS対策
- 文字数制限

## 開発フロー

### Phase 1: 基礎実装
1. テストファイルの作成
2. 基本的なディレクトリ構造の作成
3. インターフェースの定義
4. OpenAIサービスの実装
5. コントローラーとユースケースの実装
6. フロントエンド画面の作成

### Phase 2: 機能拡張
1. Geminiサービスの実装
2. サービス切り替え機能の実装
3. エラーハンドリングの強化
4. リトライ機能の実装

### Phase 3: 最適化
1. キャッシング機能の実装
2. ログ機能の追加
3. パフォーマンスチューニング
4. ドキュメント整備

## 必要なComposerパッケージ

```json
{
    "require": {
        "guzzlehttp/guzzle": "^7.0",
        "openai-php/client": "^0.7",
        "google/generative-ai-php": "^0.1"
    },
    "require-dev": {
        "mockery/mockery": "^1.6",
        "phpunit/phpunit": "^11.0"
    }
}
```

## コマンド一覧

```bash
# テスト実行
php artisan test

# 特定のテスト実行
php artisan test --filter PrefectureExtractionTest

# サーバー起動
php artisan serve

# キャッシュクリア
php artisan cache:clear

# 設定キャッシュ
php artisan config:cache
```

## 今後の拡張可能性

1. **複数AI併用**: 両方のAIを使用して精度向上
2. **機械学習モデル**: 独自モデルの訓練と利用
3. **バッチ処理**: 大量の住所を一括処理
4. **API認証**: トークンベースの認証追加
5. **ダッシュボード**: 利用統計の可視化

## 参考資料

- [Laravel Documentation](https://laravel.com/docs)
- [OpenAI API Documentation](https://platform.openai.com/docs)
- [Google AI Gemini Documentation](https://ai.google.dev/docs)
- [Test Driven Development by Kent Beck](https://www.amazon.com/Test-Driven-Development-Kent-Beck/dp/0321146530)
- [Clean Architecture by Robert C. Martin](https://www.amazon.com/Clean-Architecture-Craftsmans-Software-Structure/dp/0134494164)