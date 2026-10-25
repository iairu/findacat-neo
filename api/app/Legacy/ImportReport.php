<?php

namespace App\Legacy;

/** Outcome of one legacy import: how many rows were handled and what looked wrong. */
final class ImportReport
{
    /** @var array<string, int> */
    public array $counts = [];

    /** @var list<string> */
    public array $warnings = [];

    public function count(string $key, int $by = 1): void
    {
        $this->counts[$key] = ($this->counts[$key] ?? 0) + $by;
    }

    public function warn(string $message): void
    {
        $this->warnings[] = $message;
    }
}
