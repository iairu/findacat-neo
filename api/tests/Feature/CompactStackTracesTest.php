<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class CompactStackTracesTest extends TestCase
{
    public function test_stderr_log_keeps_the_message_and_drops_framework_frames(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'log');
        config(['logging.channels.stderr.handler_with.stream' => $file]);

        try {
            collect([1])->each(fn () => throw new RuntimeException('Mail server refused'));
        } catch (RuntimeException $e) {
            Log::channel('stderr')->error($e->getMessage(), ['exception' => $e]);
        }
        $log = file_get_contents($file);
        unlink($file);

        $this->assertStringContainsString('Mail server refused', $log);
        $this->assertStringContainsString('tests/Feature/CompactStackTracesTest.php', $log);
        $this->assertStringNotContainsString(base_path().'/', $log);
        $this->assertLessThanOrEqual(3, preg_match_all('/^#\d+ vendor\//m', $log));
        $this->assertStringNotContainsString('{main}', $log);
    }
}
