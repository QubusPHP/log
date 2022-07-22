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

class LogLevelTest extends TestCase
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

    public function testLogLevels()
    {
        $logger = new FileLogger($this->filesystem, LogLevel::DEBUG);

        $emergencyResult = $logger->emergency('there is a spider in the house');
        Assert::assertNull($emergencyResult);

        $alertResult = $logger->alert('there is a mouse in the house');
        Assert::assertNull($alertResult);

        $criticalResult = $logger->critical('there is a dog in the house');
        Assert::assertNull($criticalResult);

        $errorResult = $logger->error('there is a house in the house');
        Assert::assertNull($errorResult);

        $warningResult = $logger->warning('there is a spider outside the house');
        Assert::assertNull($warningResult);

        $noticeResult = $logger->notice('i saw a picture of a spider');
        Assert::assertNull($noticeResult);

        $infoResult = $logger->info('i do not like spiders');
        Assert::assertNull($infoResult);

        $debugResult = $logger->debug('i would pet a dog');
        Assert::assertNull($debugResult);
    }
}
