<?php

namespace Tests\Unit\Services;

use App\Services\AiApiFactory;
use App\Repositories\OpenAiApiRepository;
use App\Repositories\GeminiApiRepository;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Tests\TestCase;

class AiApiFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // デフォルト設定をクリア
        Config::set('ai.openai.api_key', null);
        Config::set('ai.gemini.api_key', null);
        Config::set('ai.default_provider', 'openai');
    }

    public function test_create_OpenAIインスタンスを作成()
    {
        $instance = AiApiFactory::create('openai');
        $this->assertInstanceOf(OpenAiApiRepository::class, $instance);
    }

    public function test_create_Geminiインスタンスを作成()
    {
        $instance = AiApiFactory::create('gemini');
        $this->assertInstanceOf(GeminiApiRepository::class, $instance);
    }

    public function test_create_デフォルトプロバイダーを使用()
    {
        Config::set('ai.default_provider', 'openai');
        $instance = AiApiFactory::create();
        $this->assertInstanceOf(OpenAiApiRepository::class, $instance);

        Config::set('ai.default_provider', 'gemini');
        $instance = AiApiFactory::create();
        $this->assertInstanceOf(GeminiApiRepository::class, $instance);
    }

    public function test_create_サポートされていないプロバイダーで例外をスロー()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported AI provider: invalid_provider');

        AiApiFactory::create('invalid_provider');
    }

    public function test_getAvailableProviders_OpenAIのみ利用可能()
    {
        Config::set('ai.openai.api_key', 'test-key');
        Config::set('ai.gemini.api_key', null);

        $providers = AiApiFactory::getAvailableProviders();
        $this->assertEquals(['openai'], $providers);
    }

    public function test_getAvailableProviders_Geminiのみ利用可能()
    {
        Config::set('ai.openai.api_key', null);
        Config::set('ai.gemini.api_key', 'test-key');

        $providers = AiApiFactory::getAvailableProviders();
        $this->assertEquals(['gemini'], $providers);
    }

    public function test_getAvailableProviders_両方利用可能()
    {
        Config::set('ai.openai.api_key', 'test-key');
        Config::set('ai.gemini.api_key', 'test-key');

        $providers = AiApiFactory::getAvailableProviders();
        $this->assertEquals(['openai', 'gemini'], $providers);
    }

    public function test_getAvailableProviders_どちらも利用不可()
    {
        Config::set('ai.openai.api_key', null);
        Config::set('ai.gemini.api_key', null);

        $providers = AiApiFactory::getAvailableProviders();
        $this->assertEquals([], $providers);
    }
}