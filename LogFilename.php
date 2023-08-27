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

use Psr\Log\LogLevel;

use function date;

class LogFilename implements Filename
{
    /**
     * Create the log filename.
     *
     * @param string|LogLevel $level The log level.
     * @param string $filenameFormat    Accepts the same parameters as PHP's date function.
     * @param string $filenameExtension Accepts a file extension such as log.
     * @return string The filename for the log file that will be written
     */
    public function create(string|LogLevel $level, string $filenameFormat, string $filenameExtension): string
    {
        return $level . '-' . date($filenameFormat) . '.' . $filenameExtension;
    }
}
