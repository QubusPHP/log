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
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Qubus\Log\LogFilename;
use Qubus\Log\Loggers\FileLogger;

use function count;
use function date;
use function dirname;
use function end;
use function explode;
use function strpos;

class LogFilenameTest extends TestCase
{
    public function testCreateFilename()
    {
        $logFile = new LogFilename();
        $expectedResult = 'info' . '-' . date('Y-m-d') . '.log';
        $result = $logFile->create('info', 'Y-m-d', 'log');
        Assert::assertEquals($expectedResult, $result);
    }

    public function testSettingPropertiesInLogger()
    {
        $adapter = new LocalFilesystemAdapter(dirname(__DIR__) . '/storage/logs/');
        $filesystem = new Filesystem($adapter);
        $logger = new FileLogger($filesystem, LogLevel::INFO);
        $logger->setFilenameFormat('Y-m-d');
        $logger->setFilenameExtension('log');

        $result = $logger->info('A horse is a horse.');
        Assert::assertNull($result);

        $logs = $filesystem->read('info' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $log = explode("\n", $logs); // explode the string by newline into an array

        $numberOfLines = count($log); // get the number of indexes in the array
        unset($log[$numberOfLines - 1]); // take off the last line / last index of the array

        $containHorse = false;

        if (strpos(end($log), 'horse') !== false) {
            $containHorse = true;
        }

        Assert::assertTrue($containHorse);
    }
}
