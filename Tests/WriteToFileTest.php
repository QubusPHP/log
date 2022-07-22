<?php

/**
 * Qubus\Log
 *
 * @link       https://github.com/QubusPHP/log
 * @copyright  2020 Joshua Parker <josh@joshuaparker.blog>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 *
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Qubus\Tests\Log;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Qubus\Config\Collection;
use Qubus\FileSystem\Adapter\LocalFlysystemAdapter;
use Qubus\FileSystem\FileSystem;
use Qubus\Log\Loggers\FileLogger;

use function count;
use function date;
use function end;
use function explode;
use function strpos;

class WriteToFileTest extends TestCase
{
    protected LocalFlysystemAdapter $adapter;
    protected FileSystem $filesystem;

    public function setUp(): void
    {
        $config = Collection::factory([
            'path' => __DIR__ . '/../config',
        ]);

        $this->adapter = new LocalFlysystemAdapter($config);
        $this->filesystem = new FileSystem($this->adapter);

    }

    public function testWritingToFile()
    {
        $logger = new FileLogger($this->filesystem, LogLevel::INFO);

        $result = $logger->info('A horse is a horse.');
        Assert::assertNull($result);

        $logs = $this->filesystem->read('info' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $log = explode("\n", $logs); // explode the string by newline into an array

        $numberOfLines = count($log); // get the number of indexes in the array
        unset($log[$numberOfLines - 1]); // take off the last line / last index of the array

        $containHorse = false;

        if (strpos(end($log), 'horse') !== false) {
            $containHorse = true;
        }

        Assert::assertTrue($containHorse);
    }

    public function testNotWritingToFile()
    {
        $logger = new FileLogger($this->filesystem, LogLevel::INFO);

        $result = $logger->debug('A horse is a dog.');
        Assert::assertNull($result);

        $logs = $this->filesystem->read('debug' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $log = explode("\n", $logs); // explode the string by newline into an array

        $numberOfLines = count($log); // get the number of indexes in the array
        unset($log[$numberOfLines - 1]); // take off the last line / last index of the array

        $containHorse = false;

        if (strpos(end($log), 'cat') !== false) {
            $containHorse = true;
        }

        Assert::assertFalse($containHorse);
    }

    public function testWritingArrayToFile()
    {
        $logger = new FileLogger($this->filesystem, LogLevel::INFO);

        $horses = [
            'spirit',
            'iron maiden',
            'juniper',
        ];

        $result = $logger->info('A horse is a horse.', $horses);
        Assert::assertNull($result);

        $logs = $this->filesystem->read('info' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $containsArray = false;

        if (strpos($logs, 'spirit') !== false) {
            $containsArray = true;
        }

        Assert::assertTrue($containsArray);
    }
}
