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
use Qubus\Log\Logger;
use Qubus\Log\Loggers\FileLogger;
use SplObjectStorage;

use function date;
use function strpos;

class InterpolateTest extends TestCase
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

    public function testInterpolate()
    {
        $logger = new FileLogger($this->filesystem, LogLevel::INFO);

        $values = [
            'animal' => 'cat',
        ];

        $logger->info('A horse is a {animal}.', $values);

        $logs = $this->filesystem->read('info' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $containsCat = false;

        if (strpos($logs, 'cat') !== false) {
            $containsCat = true;
        }

        $this->assertTrue($containsCat);
    }

    public function testInterpolateWithSplObjectStorage()
    {
        $storage = new SplObjectStorage();

        $storage->attach(new FileLogger($this->filesystem, LogLevel::INFO));

        $logger = new Logger($storage);

        $values = [
            'animal' => 'dog',
        ];

        $logger->info('A pig is a {animal}.', $values);

        $logs = $this->filesystem->read('info' . '-' . date('Y-m-d') . '.log'); // read the logs into a string

        $containsDog = false;

        if (strpos($logs, 'dog') !== false) {
            $containsDog = true;
        }

        Assert::assertTrue($containsDog);
    }
}
