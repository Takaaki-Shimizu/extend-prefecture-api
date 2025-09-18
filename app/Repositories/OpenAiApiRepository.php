<?php

namespace App\Repositories;

use App\Repositories\Interfaces\AiApiInterface;
use App\Exceptions\ExternalResourceNotFoundException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAiApiRepository implements AiApiInterface
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('ai.openai.api_key') ?: '';
        $this->apiUrl = config('ai.openai.api_url', 'https://api.openai.com/v1/chat/completions');
        $this->model = config('ai.openai.model');
    }

    public function extractPrefectureByAi(string $address): ?string
    {
        try {
            $response = $this->callOpenAiApi($address);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? null;

                return $this->extractPrefectureFromResponse($content);
            }

            Log::error('OpenAI API request failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            throw new ExternalResourceNotFoundException('OpenAI APIリクエストが失敗しました');

        } catch (Exception $e) {
            Log::error('OpenAI API error', ['message' => $e->getMessage()]);
            throw new ExternalResourceNotFoundException('OpenAI APIでエラーが発生しました: ' . $e->getMessage());
        }
    }

    private function callOpenAiApi(string $address): Response
    {
        $prompt = $this->buildPrompt($address);

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($this->apiUrl, [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'あなたは日本の住所解析の専門家です。与えられた住所文字列から都道府県名のみを抽出してください。'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 50,
            'temperature' => 0.1
        ]);
    }

    private function buildPrompt(string $address): string
    {
        return "以下の住所文字列から都道府県名のみを抽出してください。都道府県名が見つからない場合は「不明」と答えてください。\n\n住所: {$address}\n\n都道府県名:";
    }

    private function extractPrefectureFromResponse(?string $content): ?string
    {
        if (empty($content)) {
            return null;
        }

        $content = trim($content);

        // 「不明」の場合はnullを返す
        if (in_array($content, ['不明', 'unknown', 'Unknown', 'UNKNOWN'])) {
            return null;
        }

        // 47都道府県の正規表現でチェック
        $prefectures = config('prefectures.list');
        foreach ($prefectures as $prefecture) {
            if (strpos($content, $prefecture) !== false) {
                return $prefecture;
            }
        }

        return null;
    }
}