<?php
/**
 * Multi-provider AI chat (OpenAI-compatible + Google Gemini).
 * Dipanggil dari api/chat.php — tetap procedural untuk shared hosting.
 */

declare(strict_types=1);

/**
 * Sesuaikan field "model" dengan format API masing-masing provider.
 *
 * Banyak user mengisi model ala OpenRouter (mis. "openai/gpt-4o-mini") lalu
 * mengganti provider ke "openai" — API resmi OpenAI menolak ID tersebut (HTTP 400).
 */
function ai_normalize_model_for_provider(string $provider, string $model): string
{
    $model = trim($model);
    if ($model === '') {
        return $model;
    }

    switch ($provider) {
        case 'openai':
        case 'deepseek':
            // "openai/gpt-4o-mini" → "gpt-4o-mini"
            // "meta-llama/llama-3.1-8b-instruct" → "llama-3.1-8b-instruct" (DeepSeek mungkin tidak punya; tetap aman)
            if (preg_match('#^[a-z0-9][a-z0-9_.-]*/([a-z0-9][a-z0-9_.:-]*)$#i', $model, $m)) {
                return $m[1];
            }

            return $model;

        case 'openrouter':
            // OpenRouter wajib slug penuh vendor/model — jangan strip
            return $model;

        case 'google':
            // "google/gemini-1.5-flash" → "gemini-1.5-flash"
            if (preg_match('#^google/(.+)$#i', $model, $m)) {
                $model = $m[1];
            }
            // "models/gemini-1.5-flash" → "gemini-1.5-flash" (URL kita sudah pakai prefix models/)
            if (preg_match('#^models/(.+)$#i', $model, $m)) {
                $model = $m[1];
            }

            return trim($model);

        default:
            return $model;
    }
}

/** @return list<array{role:string,body:string}> */
function fetch_chat_history_rows(
    PDO $pdo,
    int $client_id,
    string $session_id,
    int $limit = 48
): array {
    $lim = max(1, min(200, $limit));

    $stmt = $pdo->prepare('
        SELECT role, body
        FROM chat_messages
        WHERE client_id = :cid AND session_id = :sid
        ORDER BY id DESC
        LIMIT ' . $lim . '
    ');
    $stmt->execute([
        ':cid' => $client_id,
        ':sid' => $session_id,
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_reverse($rows ?: []);
}

/**
 * Jalankan chat completion sesuai ai_provider.
 *
 * @param array{
 *   ai_provider:string,
 *   ai_model:string,
 *   ai_system_prompt:string,
 *   bot_name:string
 * } $widget
 * @return array{ok:bool, reply:?string, error:?string}
 */
function ai_chat_complete(array $widget, string $provider_api_key, array $history_rows, string $user_message): array
{
    $provider = (string) ($widget['ai_provider'] ?? '');
    $rawModel = (string) ($widget['ai_model'] ?? '');
    $widget['ai_model'] = ai_normalize_model_for_provider($provider, $rawModel);

    switch ($provider) {
        case 'openai':
            return ai_openai_compatible_chat(
                OPENAI_API_URL,
                $provider_api_key,
                $widget,
                $history_rows,
                $user_message,
                []
            );

        case 'deepseek':
            return ai_openai_compatible_chat(
                DEEPSEEK_API_URL,
                $provider_api_key,
                $widget,
                $history_rows,
                $user_message,
                []
            );

        case 'openrouter':
            $extra = [];
            if (OPENROUTER_HTTP_REFERER !== '') {
                $extra['HTTP-Referer'] = OPENROUTER_HTTP_REFERER;
            }
            if (OPENROUTER_APP_TITLE !== '') {
                $extra['X-Title'] = OPENROUTER_APP_TITLE;
            }

            return ai_openai_compatible_chat(
                OPENROUTER_API_URL,
                $provider_api_key,
                $widget,
                $history_rows,
                $user_message,
                $extra
            );

        case 'google':
            return ai_gemini_chat($provider_api_key, $widget, $history_rows, $user_message);

        default:
            return [
                'ok'    => false,
                'reply' => null,
                'error' => 'Provider AI tidak dikenali. Periksa kolom ai_provider di database.',
            ];
    }
}

/**
 * OpenAI Chat Completions format (dipakai OpenAI, DeepSeek, OpenRouter).
 *
 * @param array<string,string> $extra_headers
 * @return array{ok:bool, reply:?string, error:?string}
 */
function ai_openai_compatible_chat(
    string $url,
    string $api_key,
    array $widget,
    array $history_rows,
    string $user_message,
    array $extra_headers
): array {
    $system = trim((string) ($widget['ai_system_prompt'] ?? ''));
    if ($system === '') {
        $name = trim((string) ($widget['bot_name'] ?? 'Assistant'));
        $system = 'You are "' . $name . '", a helpful assistant. Reply clearly and concisely.';
    }

    $messages = [['role' => 'system', 'content' => $system]];

    foreach ($history_rows as $row) {
        $role = $row['role'] === 'assistant' ? 'assistant' : 'user';
        $messages[] = ['role' => $role, 'content' => (string) $row['body']];
    }

    $messages[] = ['role' => 'user', 'content' => $user_message];

    $payload = [
        'model'       => (string) ($widget['ai_model'] ?? 'gpt-4o-mini'),
        'messages'    => $messages,
        'temperature' => 0.7,
    ];

    $headers = [
        'Content-Type'  => 'application/json',
        'Authorization' => 'Bearer ' . $api_key,
    ];
    foreach ($extra_headers as $hk => $hv) {
        $headers[$hk] = $hv;
    }

    return ai_http_post_json_decode_chat(
        $url,
        $headers,
        $payload,
        AI_HTTP_TIMEOUT,
        static function (array $json): ?string {
            return $json['choices'][0]['message']['content']
                ?? null;
        },
        'OpenAI-compatible'
    );
}

/**
 * Google Gemini generateContent (API key di query ?key=).
 *
 * @return array{ok:bool, reply:?string, error:?string}
 */
function ai_gemini_chat(string $api_key, array $widget, array $history_rows, string $user_message): array
{
    $model = trim((string) ($widget['ai_model'] ?? 'gemini-1.5-flash'));
    if ($model === '') {
        $model = 'gemini-1.5-flash';
    }

    $base = rtrim(GEMINI_API_BASE, '/');
    $url  = $base . '/' . rawurlencode($model) . ':generateContent'
        . '?key=' . rawurlencode($api_key);

    $system = trim((string) ($widget['ai_system_prompt'] ?? ''));
    if ($system === '') {
        $name = trim((string) ($widget['bot_name'] ?? 'Assistant'));
        $system = 'You are "' . $name . '", a helpful assistant. Reply clearly and concisely.';
    }

    $contents = [];

    foreach ($history_rows as $row) {
        $gem_role = $row['role'] === 'assistant' ? 'model' : 'user';
        $contents[] = [
            'role'  => $gem_role,
            'parts' => [['text' => (string) $row['body']]],
        ];
    }

    $contents[] = [
        'role'  => 'user',
        'parts' => [['text' => $user_message]],
    ];

    $body = [
        'contents'          => $contents,
        'systemInstruction' => [
            'parts' => [['text' => $system]],
        ],
        'generationConfig'  => [
            'temperature' => 0.7,
        ],
    ];

    return ai_http_post_json_decode_chat(
        $url,
        ['Content-Type' => 'application/json'],
        $body,
        AI_HTTP_TIMEOUT,
        static function (array $json): ?string {
            $parts = $json['candidates'][0]['content']['parts'] ?? null;
            if (!is_array($parts) || $parts === []) {
                return null;
            }
            $texts = [];
            foreach ($parts as $p) {
                if (isset($p['text']) && is_string($p['text'])) {
                    $texts[] = $p['text'];
                }
            }
            return $texts !== [] ? implode('', $texts) : null;
        },
        'Gemini'
    );
}

/**
 * @param callable(array): ?string $extract_reply
 * @return array{ok:bool, reply:?string, error:?string}
 */
function ai_http_post_json_decode_chat(
    string $url,
    array $headers,
    array $payload,
    int $timeout_sec,
    callable $extract_reply,
    string $provider_label
): array {
    $json_body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json_body === false) {
        return ['ok' => false, 'reply' => null, 'error' => 'Gagal menyiapkan permintaan AI.'];
    }

    $header_lines = [];
    foreach ($headers as $k => $v) {
        $header_lines[] = $k . ': ' . $v;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $json_body,
        CURLOPT_HTTPHEADER     => $header_lines,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout_sec,
        CURLOPT_CONNECTTIMEOUT => min(15, $timeout_sec),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_MAXREDIRS      => 0,
    ]);

    $response   = curl_exec($ch);
    $http_code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error !== '') {
        error_log('[ai] ' . $provider_label . ' cURL: ' . $curl_error);
        return [
            'ok'    => false,
            'reply' => null,
            'error' => 'Koneksi ke ' . $provider_label . ' gagal atau timeout. Coba lagi.',
        ];
    }

    if (!is_string($response)) {
        return ['ok' => false, 'reply' => null, 'error' => 'Respons AI kosong.'];
    }

    $decoded = json_decode($response, true);

    if ($http_code < 200 || $http_code >= 300) {
        $detail = ai_extract_provider_error($decoded, $response);
        error_log('[ai] ' . $provider_label . ' HTTP ' . $http_code . ': ' . mb_substr($response, 0, 2000));
        return [
            'ok'    => false,
            'reply' => null,
            'error' => $provider_label . ' menolak permintaan (HTTP ' . $http_code . '). ' . $detail,
        ];
    }

    if (!is_array($decoded)) {
        return ['ok' => false, 'reply' => null, 'error' => 'Format respons AI tidak valid.'];
    }

    $reply = $extract_reply($decoded);
    if ($reply === null || trim($reply) === '') {
        $block = $decoded['promptFeedback']['blockReason'] ?? '';
        $finish = $decoded['candidates'][0]['finishReason'] ?? '';
        $hint = '';
        if (is_string($block) && $block !== '') {
            $hint = ' (' . $block . ')';
        } elseif (is_string($finish) && $finish !== '' && $finish !== 'STOP') {
            $hint = ' (' . $finish . ')';
        }

        return [
            'ok'    => false,
            'reply' => null,
            'error' => 'AI tidak mengembalikan teks jawaban.' . $hint,
        ];
    }

    return ['ok' => true, 'reply' => trim($reply), 'error' => null];
}

/** @param mixed $decoded */
function ai_extract_provider_error($decoded, string $raw): string
{
    if (is_array($decoded)) {
        if (isset($decoded['error'])) {
            $e = $decoded['error'];
            if (is_string($e)) {
                return $e;
            }
            if (is_array($e)) {
                $msg = $e['message'] ?? $e['status'] ?? '';
                if (is_string($msg) && $msg !== '') {
                    return $msg;
                }
            }
        }
    }

    $snippet = trim(mb_substr(strip_tags($raw), 0, 280));
    return $snippet !== '' ? $snippet : 'Periksa API key, model, atau kuota provider.';
}
