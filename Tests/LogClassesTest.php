<?php

declare(strict_types=1);

namespace Qubus\Tests\Log;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Qubus\Config\Collection;
use Qubus\FileSystem\Adapter\LocalFlysystemAdapter;
use Qubus\FileSystem\FileSystem;
use Qubus\Log\Filename;
use Qubus\Log\Format;
use Qubus\Log\LogFilename;
use Qubus\Log\LogFormat;
use Qubus\Log\Loggers\FileLogger;

use function count;
use function date;
use function end;
use function explode;
use function strpos;

class LogClassesTest extends TestCase
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
    
    public function testSpecifyingLogClasses()
    {
        $logger = new FileLogger($this->filesystem, LogLevel::DEBUG);

        $logFilename = new LogFilename();
        $logFormat = new LogFormat();

        Assert::assertInstanceOf(Filename::class, $logFilename);
        Assert::assertInstanceOf(Format::class, $logFormat);

        $logger->setLogFilename($logFilename);
        $logger->setLogFormat($logFormat);

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
}
