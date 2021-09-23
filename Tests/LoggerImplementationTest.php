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

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Qubus\Log\Loggers\FileLogger;

use function dirname;

class LoggerImplementationTest extends TestCase
{
    public function testIfItImplementsPsrThree()
    {
        $adapter = new LocalFilesystemAdapter(dirname(__DIR__) . '/storage/logs/');
        $filesystem = new Filesystem($adapter);
        $logger = new FileLogger($filesystem, LogLevel::INFO);
        Assert::assertInstanceOf(LoggerInterface::class, $logger);
        Assert::assertInstanceOf(AbstractLogger::class, $logger);
    }
}
