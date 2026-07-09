<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GroqAIService
 *
 * HTTP client wrapper for the Groq Cloud API.
 * Groq exposes an OpenAI-compatible REST API, so we use the standard
 * chat/completions endpoint with Llama models hosted on Groq's LPU hardware.
 *
 * This service is used by both Buddy AI (student) and Visitor AI (public).
 * It never handles context building — that's RAGService's job.
 */
class GroqAIService
{
    protected array $apiKeys = [];
    protected string $apiKey = '';
    protected string $baseUrl;
    protected string $model;
    protected int $maxTokens;
    protected float $temperature;

    public function __construct()
    {
        $apiKeysStr        = config('services.groq.api_key', '');
        // Split comma-separated list of keys
        $this->apiKeys     = array_values(array_filter(array_map('trim', explode(',', $apiKeysStr))));
        if (empty($this->apiKeys)) {
            $this->apiKeys = [''];
        }
        $this->apiKey      = $this->apiKeys[0];
        $this->baseUrl     = rtrim(config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
        $this->model       = config('services.groq.model', 'llama-3.3-70b-versatile');
        $this->maxTokens   = (int) config('services.groq.max_tokens', 1024);
        $this->temperature = (float) config('services.groq.temperature', 0.7);
    }

    /**
     * Check if the Groq API is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'your-groq-api-key-here';
    }

    /**
     * Execute a chat callback using key rotation on rate limits (429).
     */
    protected function executeWithApiKeyRotation(callable $callback)
    {
        $lastException = null;

        foreach ($this->apiKeys as $index => $key) {
            $this->apiKey = $key;

            try {
                return $callback();
            } catch (\RuntimeException $e) {
                $lastException = $e;

                if (str_contains(strtolower($e->getMessage()), 'rate limit') || str_contains(strtolower($e->getMessage()), '429')) {
                    $nextIndex = $index + 1;
                    if (isset($this->apiKeys[$nextIndex])) {
                        Log::warning("[GroqAI] Rate limit hit (429) for key index {$index}. Rotating to key index {$nextIndex}...");
                        continue;
                    }
                }
                throw $e;
            } catch (\Exception $e) {
                Log::error("[GroqAI] Unexpected callback error: " . $e->getMessage());
                throw new \RuntimeException('An error occurred during API execution.');
            }
        }

        if ($lastException) {
            throw $lastException;
        }

        throw new \RuntimeException('No active Groq API keys available.');
    }

    /**
     * Send a chat completion request returning JSON object.
     */
    public function chatJson(string $systemPrompt, array $messages): string
    {
        return $this->executeWithApiKeyRotation(function() use ($systemPrompt, $messages) {
            if (!$this->isConfigured()) {
                Log::error('[GroqAI] API key is not configured. Check GROQ_API_KEY in .env');
                throw new \RuntimeException('Groq AI is not configured. Please set GROQ_API_KEY in your .env file.');
            }

            try {
                $fullMessages = array_merge(
                    [['role' => 'system', 'content' => $systemPrompt]],
                    $messages
                );

                $payload = [
                    'model'           => $this->model,
                    'messages'        => $fullMessages,
                    'max_tokens'      => $this->maxTokens,
                    'temperature'     => 0.1,
                    'response_format' => ['type' => 'json_object']
                ];

                $response = Http::withToken($this->apiKey)
                    ->timeout(60)
                    ->post($this->baseUrl . '/chat/completions', $payload);

                if ($response->failed()) {
                    $status = $response->status();
                    $body   = $response->body();
                    Log::error("[GroqAI:JSON] API Error ({$status}): {$body}");

                    if ($status === 429) {
                        throw new \RuntimeException('Groq API rate limit exceeded (429).');
                    }
                    throw new \RuntimeException("Failed to communicate with Groq AI API. Status: {$status}");
                }

                return $response->json('choices.0.message.content');
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('[GroqAI] Connection failed: ' . $e->getMessage());
                throw new \RuntimeException('Could not connect to Groq AI.');
            }
        });
    }

    /**
     * Send a general chat completion request.
     */
    public function chat(string $systemPrompt, array $messages): string
    {
        return $this->executeWithApiKeyRotation(function() use ($systemPrompt, $messages) {
            if (!$this->isConfigured()) {
                Log::error('[GroqAI] API key is not configured. Check GROQ_API_KEY in .env');
                throw new \RuntimeException('Groq AI is not configured. Please set GROQ_API_KEY in your .env file.');
            }

            try {
                $fullMessages = array_merge(
                    [['role' => 'system', 'content' => $systemPrompt]],
                    $messages
                );

                $payload = [
                    'model'       => $this->model,
                    'messages'    => $fullMessages,
                    'max_tokens'  => $this->maxTokens,
                    'temperature' => $this->temperature,
                    'stream'      => false,
                ];

                $response = Http::timeout(60)
                    ->withHeaders([
                        'Authorization' => "Bearer {$this->apiKey}",
                        'Content-Type'  => 'application/json',
                    ])
                    ->post("{$this->baseUrl}/chat/completions", $payload);

                if ($response->failed()) {
                    $status = $response->status();
                    $body   = $response->body();
                    Log::error("[GroqAI] API Error ({$status}): {$body}");

                    if ($status === 401) {
                        throw new \RuntimeException('Invalid Groq API key (401).');
                    }
                    if ($status === 429) {
                        throw new \RuntimeException('Groq API rate limit exceeded (429).');
                    }

                    throw new \RuntimeException("Groq API returned status {$status}.");
                }

                $content = $response->json('choices.0.message.content');

                if (empty($content)) {
                    Log::warning('[GroqAI] Empty response from API: ' . $response->body());
                    return 'I apologize, but I could not generate a response right now. Please try again.';
                }

                return trim($content);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('[GroqAI] Connection failed: ' . $e->getMessage());
                throw new \RuntimeException('Could not connect to Groq AI.');
            }
        });
    }
}
