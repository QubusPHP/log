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

use function date;

class LogFilename implements Filename
{
    /**
     * Create the log filename.
     *
     * @param string $filenameFormat    Accepts the same parameters as PHP's date function.
     * @param string $filenameExtension Accepts a file extension such as log.
     * @return string The filename for the log file that will be written
     */
    public function create($level, $filenameFormat, $filenameExtension)
    {
        return $level . '-' . date($filenameFormat) . '.' . $filenameExtension;
    }
}
