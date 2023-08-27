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
use Stringable;

interface Format
{
    public function create(string|LogLevel $level, string|Stringable $message, array $context = []): string;
}
