<?php

/**
 * Qubus\Log
 *
 * @link       https://github.com/QubusPHP/log
 * @copyright  2020 Joshua Parker
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 *
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Qubus\Tests\Log;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Qubus\Log\Loggers\FileLogger;

use function count;
use function date;
use function dirname;
use function end;
use function explode;
use function strpos;

class WriteToFileTest extends TestCase
{
    public function testWritingToFile()
    {
        $adapter = new LocalFilesystemAdapter(dirname(__DIR__) . '/storage/logs/');
        $filesystem = new Filesystem($adapter);
        $logger = new FileLogger($filesystem, LogLevel::INFO);

        $result = $logger->info('A horse is a horse.');
        $this->assertNull($result);

        $logs = $filesystem->read('info' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $log = explode("\n", $logs); // explode the string by newline into an array

        $numberOfLines = count($log); // get the number of indexes in the array
        unset($log[$numberOfLines - 1]); // take off the last line / last index of the array

        $containHorse = false;

        if (strpos(end($log), 'horse') !== false) {
            $containHorse = true;
        }

        $this->assertTrue($containHorse);
    }

    public function testNotWritingToFile()
    {
        $adapter = new LocalFilesystemAdapter(dirname(__DIR__) . '/storage/logs/');
        $filesystem = new Filesystem($adapter);
        $logger = new FileLogger($filesystem, LogLevel::INFO);

        $result = $logger->debug('A horse is a dog.');
        $this->assertNull($result);

        $logs = $filesystem->read('debug' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $log = explode("\n", $logs); // explode the string by newline into an array

        $numberOfLines = count($log); // get the number of indexes in the array
        unset($log[$numberOfLines - 1]); // take off the last line / last index of the array

        $containHorse = false;

        if (strpos(end($log), 'cat') !== false) {
            $containHorse = true;
        }

        $this->assertFalse($containHorse);
    }

    public function testWritingArrayToFile()
    {
        $adapter = new LocalFilesystemAdapter(dirname(__DIR__) . '/storage/logs/');
        $filesystem = new Filesystem($adapter);
        $logger = new FileLogger($filesystem, LogLevel::INFO);

        $horses = [
            'spirit',
            'iron maiden',
            'juniper',
        ];

        $result = $logger->info('A horse is a horse.', $horses);
        $this->assertNull($result);

        $logs = $filesystem->read('info' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $containsArray = false;

        if (strpos($logs, 'spirit') !== false) {
            $containsArray = true;
        }

        $this->assertTrue($containsArray);
    }
}
