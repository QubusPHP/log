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

use function dirname;

class LogLevelTest extends TestCase
{
    public function testLogLevels()
    {
        $adapter = new LocalFilesystemAdapter(dirname(__DIR__) . '/storage/logs/');
        $filesystem = new Filesystem($adapter);

        $logger = new FileLogger($filesystem, LogLevel::DEBUG);

        $emergencyResult = $logger->emergency('there is a spider in the house');
        $this->assertNull($emergencyResult);

        $alertResult = $logger->alert('there is a mouse in the house');
        $this->assertNull($alertResult);

        $criticalResult = $logger->critical('there is a dog in the house');
        $this->assertNull($criticalResult);

        $errorResult = $logger->error('there is a house in the house');
        $this->assertNull($errorResult);

        $warningResult = $logger->warning('there is a spider outside the house');
        $this->assertNull($warningResult);

        $noticeResult = $logger->notice('i saw a picture of a spider');
        $this->assertNull($noticeResult);

        $infoResult = $logger->info('i do not like spiders');
        $this->assertNull($infoResult);

        $debugResult = $logger->debug('i would pet a dog');
        $this->assertNull($debugResult);
    }
}
