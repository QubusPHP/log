<?php

/**
 * Qubus\Log
 *
 * @link       https://github.com/QubusPHP/log
 * @copyright  2020
 * @author     Joshua Parker <joshua@joshuaparker.dev>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace Qubus\Tests\Log;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Qubus\Config\Collection;
use Qubus\FileSystem\Adapter\LocalFlysystemAdapter;
use Qubus\FileSystem\FileSystem;
use Qubus\Log\Loggers\FileLogger;

class LoggerImplementationTest extends TestCase
{
    protected LocalFlysystemAdapter $adapter;
    protected FileSystem $filesystem;

    public function setUp(): void
    {
        $config = Collection::factory([
            'path' => __DIR__ . '/config',
        ]);

        $this->adapter = new LocalFlysystemAdapter($config);
        $this->filesystem = new FileSystem($this->adapter);
    }
    
    public function testIfItImplementsPsrThree()
    {
        $logger = new FileLogger($this->filesystem, LogLevel::INFO);
        Assert::assertInstanceOf(LoggerInterface::class, $logger);
        Assert::assertInstanceOf(AbstractLogger::class, $logger);
    }
}
