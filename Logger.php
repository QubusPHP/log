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

namespace Qubus\Log;

use Iterator;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Qubus\Log\Loggers\BaseLogger;
use Stringable;

class Logger extends AbstractLogger
{
    public function __construct(protected Iterator $loggers)
    {
    }

    /**
     * @param string|LogLevel $level
     * @param string|Stringable $message
     * @param array $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            if (! $logger instanceof BaseLogger && ! $logger->isAvailable($level)) {
                continue;
            }
            $logger->log(level: $level, message: $message, context: $context);
        }
    }
}
