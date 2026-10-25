<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FormattableHandlerInterface;

/**
 * Shortens logged stack traces to the first few frames plus the application's own frames.
 *
 * Log viewers such as Vercel's cut long entries from the top, which hides the exception
 * message behind fifty framework frames.
 */
class CompactStackTraces
{
    /** Frames kept from the top of every trace, wherever they are. */
    private const TOP_FRAMES = 3;

    public function __invoke(Logger $logger): void
    {
        $formatter = new LineFormatter(allowInlineLineBreaks: true, ignoreEmptyContextAndExtra: true);
        $formatter->setBasePath(base_path());
        $formatter->includeStacktraces(true, fn (string $line): ?string => self::keep($line) ? $line : null);

        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof FormattableHandlerInterface) {
                $handler->setFormatter($formatter);
            }
        }
    }

    private static function keep(string $line): bool
    {
        if (! preg_match('/^#(\d+) (.*)$/', $line, $match)) {
            return true;
        }

        return (int) $match[1] < self::TOP_FRAMES
            || (! str_starts_with($match[2], 'vendor/') && $match[2] !== '{main}' && ! str_contains($match[2], '/vendor/'));
    }
}
