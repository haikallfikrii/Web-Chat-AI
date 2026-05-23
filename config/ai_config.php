<?php
declare(strict_types=1);

/**
 * Konfigurasi AI terkelola ChatLM (system / managed plans).
 * API key HANYA dari environment — jangan commit nilai asli.
 */
require_once dirname(__DIR__) . '/config.php';

$provider = strtolower(config_env('SYSTEM_AI_PROVIDER', 'openrouter'));
if (!in_array($provider, ['openrouter', 'openai', 'deepseek', 'google'], true)) {
    $provider = 'openrouter';
}

$key = match ($provider) {
    'openai'    => config_env('SYSTEM_OPENAI_API_KEY', config_env('SYSTEM_AI_API_KEY', '')),
    'deepseek'  => config_env('SYSTEM_DEEPSEEK_API_KEY', config_env('SYSTEM_AI_API_KEY', '')),
    'google'    => config_env('SYSTEM_GEMINI_API_KEY', config_env('SYSTEM_AI_API_KEY', '')),
    default     => config_env('SYSTEM_OPENROUTER_API_KEY', config_env('SYSTEM_AI_API_KEY', '')),
};

$defaultModel = config_env('SYSTEM_AI_DEFAULT_MODEL', 'openai/gpt-4o-mini');
if ($provider === 'openai' && str_contains($defaultModel, '/')) {
    $defaultModel = 'gpt-4o-mini';
}
if ($provider === 'google' && str_starts_with($defaultModel, 'openai/')) {
    $defaultModel = 'gemini-2.0-flash';
}

return [
    'enabled'         => $key !== '',
    'provider'        => $provider,
    'api_key'         => $key,
    'default_model'   => $defaultModel,
    'fallback_models' => array_values(array_filter(array_map('trim', explode(',', config_env(
        'SYSTEM_AI_FALLBACK_MODELS',
        'deepseek/deepseek-chat,openai/gpt-4o-mini,google/gemini-2.0-flash'
    ))))),
    'max_tokens'      => (int) config_env('SYSTEM_AI_MAX_TOKENS', '1024'),
    'temperature'     => (float) config_env('SYSTEM_AI_TEMPERATURE', '0.4'),
];
