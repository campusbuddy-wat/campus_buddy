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
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $maxTokens;
    protected float $temperature;

    public function __construct()
    {
        $this->apiKey      = config('services.groq.api_key', '');
        $this->baseUrl     = rtrim(config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
        $this->model       = config('services.groq.model', 'llama-3.3-70b-versatile');
        $this->maxTokens   = (int) config('services.groq.max_tokens', 1024);
        $this->temperature = (float) config('services.groq.temperature', 0.7);
    }

    /**
     * Check if the Groq API is configured and reachable.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'your-groq-api-key-here';
    }

    /**
     * Send a chat completion request to the Groq API.
     *
     * @param string $systemPrompt  The system-level instruction (with RAG context injected).
     * @param array  $messages      Chat history in OpenAI format: [{role: "user"|"assistant", content: "..."}, ...]
     * @return string               The AI's response text.
     *
     * @throws \RuntimeException If the API call fails.
     */
    public function chatJson(string $systemPrompt, array $messages): string
    {
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
                'temperature'     => 0.1, // Lower temperature for more deterministic JSON
                'response_format' => ['type' => 'json_object']
            ];

            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->post($this->baseUrl . '/chat/completions', $payload);

            if ($response->failed()) {
                Log::error('[GroqAI] API Request Failed: ' . $response->body());
                throw new \RuntimeException('Failed to communicate with Groq AI API.');
            }

            return $response->json('choices.0.message.content');
        } catch (\Exception $e) {
            Log::error('[GroqAI] Exception: ' . $e->getMessage());
            throw new \RuntimeException('Groq AI service is currently unavailable.');
        }
    }

    public function chat(string $systemPrompt, array $messages): string
    {
        if (!$this->isConfigured()) {
            Log::error('[GroqAI] API key is not configured. Check GROQ_API_KEY in .env');
            throw new \RuntimeException('Groq AI is not configured. Please set GROQ_API_KEY in your .env file.');
        }

        try {
            // Build the full message array: system prompt + conversation history
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

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type'  => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", $payload);

            // Handle HTTP-level failures
            if ($response->failed()) {
                $status = $response->status();
                $body   = $response->body();
                Log::error("[GroqAI] API Error ({$status}): {$body}");

                if ($status === 401) {
                    throw new \RuntimeException('Invalid Groq API key. Please check your GROQ_API_KEY.');
                }
                if ($status === 429) {
                    throw new \RuntimeException('Groq API rate limit exceeded. Please try again in a moment.');
                }

                throw new \RuntimeException("Groq API returned status {$status}.");
            }

            // Extract the assistant's message content
            $content = $response->json('choices.0.message.content');

            if (empty($content)) {
                Log::warning('[GroqAI] Empty response from API: ' . $response->body());
                return 'I apologize, but I could not generate a response right now. Please try again.';
            }

            return trim($content);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[GroqAI] Connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Could not connect to Groq AI. Please check your internet connection.');
        } catch (\RuntimeException $e) {
            throw $e; // Re-throw our own exceptions
        } catch (\Exception $e) {
            Log::error('[GroqAI] Unexpected error: ' . $e->getMessage());
            throw new \RuntimeException('An unexpected error occurred while contacting the AI service.');
        }
    }
}
