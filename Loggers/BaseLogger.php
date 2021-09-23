<?php

/**
 * Qubus\Log
 *
 * @link       https://github.com/QubusPHP/log
 * @copyright  2020 2020 Joshua Parker <josh@joshuaparker.blog>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 *
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Qubus\Log\Loggers;

use DateTime;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Qubus\Exception\Exception;
use ReflectionClass;
use ReflectionException;

use function in_array;
use function is_array;
use function is_object;
use function json_encode;
use function method_exists;
use function strtr;

abstract class BaseLogger extends AbstractLogger
{
    public $enabled = true;
    public $dateFormat = 'Y-m-d G:i:s.u';

    /**
     * Associative array of the log levels that are given a numerical value
     * to allow comparison of the threshold and the method calling log.
     *
     * @var array
     */
    public $levels = [
        LogLevel::EMERGENCY => 7,
        LogLevel::ALERT     => 6,
        LogLevel::CRITICAL  => 5,
        LogLevel::ERROR     => 4,
        LogLevel::WARNING   => 3,
        LogLevel::NOTICE    => 2,
        LogLevel::INFO      => 1,
        LogLevel::DEBUG     => 0,
    ];

    /**
     * @param array $params
     * @throws ReflectionException
     */
    public function __construct(array $params = [])
    {
        $reflection = new ReflectionClass($this);
        foreach ($params as $name => $value) {
            $property = $reflection->getProperty($name);
            if ($reflection->hasMethod('set' . $name)) {
                $this->{'set' . $name}($value);
            } elseif ($property->isPublic()) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * @param $level
     */
    public function isAvailable($level): bool
    {
        return $this->enabled && ($this->levels === null || in_array($level, $this->levels));
    }

    protected function getDate(): string
    {
        return (new DateTime())->format($this->dateFormat);
    }

    /**
     * @param array $data
     */
    protected function stringify(array $data = []): string
    {
        return $data !== [] ? json_encode($data) : '';
    }

    /**
     * @param $message
     * @param array $context
     */
    protected function interpolate($message, array $context = []): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (! is_array($val) && (! is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * @param $name
     * @throws Exception
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->{$getter};
        }
        if (method_exists($this, 'set' . $name)) {
            throw new Exception('Getting write-only property: ' . static::class . '::' . $name);
        }

        throw new Exception('Getting unknown property: ' . static::class . '::' . $name);
    }

    /**
     * @param $name
     * @param $value
     * @throws Exception
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->{$setter}($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new Exception('Setting read-only property: ' . static::class . '::' . $name);
        } else {
            throw new Exception('Setting unknown property: ' . static::class . '::' . $name);
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->{$getter}() !== null;
        }
        return false;
    }
}
