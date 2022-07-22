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

namespace Qubus\Log;

use Iterator;
use Psr\Log\AbstractLogger;
use Qubus\Log\Loggers\BaseLogger;
use Stringable;

class Logger extends AbstractLogger
{
    public function __construct(protected Iterator $loggers)
    {
    }

    /**
     * @param string|LogLevel $level
     * @param array $context
     * @return null|void
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            if (! $logger instanceof BaseLogger && ! $logger->isAvailable($level)) {
                continue;
            }
            $logger->log($level, $message, $context);
        }
    }
}
