<?php

declare(strict_types=1);

namespace Qubus\Tests\Log;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Qubus\Config\Collection;
use Qubus\FileSystem\Adapter\LocalFlysystemAdapter;
use Qubus\FileSystem\FileSystem;
use Qubus\Log\LogFilename;
use Qubus\Log\Loggers\FileLogger;

use function count;
use function date;
use function end;
use function explode;
use function strpos;

class LogFilenameTest extends TestCase
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
    
    public function testCreateFilename()
    {
        $logFile = new LogFilename();
        $expectedResult = 'info' . '-' . date('Y-m-d') . '.log';
        $result = $logFile->create('info', 'Y-m-d', 'log');
        Assert::assertEquals($expectedResult, $result);
    }

    public function testSettingPropertiesInLogger()
    {
        $logger = new FileLogger($this->filesystem, LogLevel::INFO);
        $logger->setFilenameFormat('Y-m-d');
        $logger->setFilenameExtension('log');

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
}
