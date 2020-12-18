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

namespace Qubus\Log;

use Iterator;
use Psr\Log\AbstractLogger;
use Qubus\Log\Loggers\BaseLogger;

class Logger extends AbstractLogger
{
    protected $loggers;

    public function __construct(Iterator $loggers)
    {
        $this->loggers = $loggers;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function log($level, $message, array $context = [])
    {
        foreach ($this->loggers as $logger) {
            if (! $logger instanceof BaseLogger || ! $logger->isAvailable($level)) {
                continue;
            }
            $logger->log($level, $message, $context);
        }
    }
}
