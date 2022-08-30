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

use Psr\Log\LogLevel;
use Stringable;

interface Format
{
    public function create(string|LogLevel $level, string|Stringable $message, array $context = []): string;
}
