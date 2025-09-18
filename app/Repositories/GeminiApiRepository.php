<?php

namespace App\Repositories;

use App\Repositories\Interfaces\AiApiInterface;
use App\Exceptions\ExternalResourceNotFoundException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiApiRepository implements AiApiInterface
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('ai.gemini.api_key') ?: '';
        $this->model = config('ai.gemini.model', 'gemini-pro');
        $this->apiUrl = config('ai.gemini.api_url') ?: 'https://generativelanguage.googleapis.com/v1/models/' . $this->model . ':generateContent';
    }

    public function extractPrefectureByAi(string $address): ?string
    {
        try {
            $response = $this->callGeminiApi($address);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                return $this->extractPrefectureFromResponse($content);
            }

            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            throw new ExternalResourceNotFoundException('Gemini APIリクエストが失敗しました');

        } catch (Exception $e) {
            Log::error('Gemini API error', ['message' => $e->getMessage()]);
            throw new ExternalResourceNotFoundException('Gemini APIでエラーが発生しました: ' . $e->getMessage());
        }
    }

    private function callGeminiApi(string $address): Response
    {
        $prompt = $this->buildPrompt($address);

        return Http::timeout(30)->post($this->apiUrl . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 50
            ]
        ]);
    }

    private function buildPrompt(string $address): string
    {
        return "あなたは日本の住所解析の専門家です。以下の住所文字列から都道府県名のみを抽出してください。都道府県名が見つからない場合は「不明」と答えてください。\n\n住所: {$address}\n\n都道府県名:";
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