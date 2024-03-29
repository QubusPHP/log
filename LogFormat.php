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
use Qubus\Support\DateTime\QubusDateTime;
use Stringable;

use function count;
use function is_array;
use function is_object;
use function json_encode;
use function method_exists;
use function strtoupper;
use function strtr;

class LogFormat implements Format
{
    /**
     * Create the log format that will be used when writing to the file. It is
     * a template for the data: date/time, message, and an array.
     *
     * @param string|LogLevel $level PSR-3 log levels.
     * @param string|Stringable $message The log message to be written.
     * @param array $context An option array to be written to the log file.
     * @return string Return the string with the formatted log message.
     */
    public function create(string|LogLevel $level, string|Stringable $message, array $context = []): string
    {
        // Assemble the message. Ex. [2020-12-17 8:54:03.355345] [DEBUG] hello
        $message = '[' . (new QubusDateTime())->format(format: 'Y-m-d G:i:s.u') . '] '
        . '[' . strtoupper(string: $level) . '] ' . $message;

        // If an array was passed as well, export it to a string
        if (count($context) > 0) {
            $message .= ' ' . $this->stringify(data: $context);
        }

        return $this->interpolate(message: $message, context: $context);
    }

    /**
     * Interpolate a string that contains braces as placeholders. It uses an associative
     * array as the key => value to replace the key (placeholder) with the value from the
     * array.
     *
     * @param string $message The message containing the string that needs filtered
     * @param array $context  An associative array of the key => value to interpolate
     * @return string The message after it has been interpolated.
     */
    protected function interpolate(string $message, array $context = []): string
    {
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            // check that the value can be cast to string
            if (! is_array(value: $val)
                && (! is_object(value: $val) || method_exists(object_or_class: $val, method: '__toString'))
            ) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    /**
     * @param array $data
     * @return string
     */
    protected function stringify(array $data = []): string
    {
        return $data !== [] ? json_encode(value: $data) : '';
    }
}
