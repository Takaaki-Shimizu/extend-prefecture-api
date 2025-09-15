<?php

namespace App\Services;

use App\Repositories\Interfaces\AiApiInterface;
use App\Repositories\OpenAiApiRepository;
use App\Repositories\GeminiApiRepository;
use InvalidArgumentException;

class AiApiFactory
{
    /**
     * AI APIインスタンスを作成する
     *
     * @param string|null $provider プロバイダー名（nullの場合はデフォルト）
     * @return AiApiInterface
     * @throws InvalidArgumentException
     */
    public static function create(?string $provider = null): AiApiInterface
    {
        $provider = $provider ?? config('ai.default_provider');

        return match ($provider) {
            'openai' => new OpenAiApiRepository(),
            'gemini' => new GeminiApiRepository(),
            default => throw new InvalidArgumentException("Unsupported AI provider: {$provider}")
        };
    }

    /**
     * 利用可能なプロバイダーのリストを取得
     *
     * @return array
     */
    public static function getAvailableProviders(): array
    {
        $providers = [];

        if (config('ai.openai.api_key')) {
            $providers[] = 'openai';
        }

        if (config('ai.gemini.api_key')) {
            $providers[] = 'gemini';
        }

        return $providers;
    }
}