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
use Qubus\Log\Logger;
use Qubus\Log\Loggers\FileLogger;
use SplObjectStorage;

use function date;
use function dirname;
use function strpos;

class InterpolateTest extends TestCase
{
    public function testInterpolate()
    {
        $adapter = new LocalFilesystemAdapter(dirname(__DIR__) . '/storage/logs/');
        $filesystem = new Filesystem($adapter);
        $logger = new FileLogger($filesystem, LogLevel::INFO);

        $values = [
            'animal' => 'cat',
        ];

        $logger->info('A horse is a {animal}.', $values);

        $logs = $filesystem->read('info' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $containsCat = false;

        if (strpos($logs, 'cat') !== false) {
            $containsCat = true;
        }

        $this->assertTrue($containsCat);
    }

    public function testInterpolateWithSplObjectStorage()
    {
        $storage = new SplObjectStorage();
        $adapter = new LocalFilesystemAdapter(dirname(__DIR__) . '/storage/logs/');
        $filesystem = new Filesystem($adapter);

        $storage->attach(new FileLogger($filesystem, LogLevel::INFO));

        $logger = new Logger($storage);

        $values = [
            'animal' => 'dog',
        ];

        $logger->info('A pig is a {animal}.', $values);

        $logs = $filesystem->read('info' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $containsDog = false;

        if (strpos($logs, 'dog') !== false) {
            $containsDog = true;
        }

        Assert::assertTrue($containsDog);
    }
}
