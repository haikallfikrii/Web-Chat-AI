<?php
declare(strict_types=1);

/** Ambil blok konten per bahasa; fallback ke en. */
function docs_pick(array $byLang, string $lang): string
{
    return $byLang[$lang] ?? $byLang['en'] ?? '';
}
