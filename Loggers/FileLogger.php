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

namespace Qubus\Log\Loggers;

use League\Flysystem\FilesystemOperator;
use Qubus\Log\Filename;
use Qubus\Log\Format;
use Qubus\Log\LogFilename;
use Qubus\Log\LogFormat;
use ReflectionException;

class FileLogger extends BaseLogger
{
    /**
     * Flysystem filesystem abstraction.
     */
    protected ?FilesystemOperator $filesystem = null;

    /**
     * Lowest level of logging to write.
     *
     * @var LogLevel
     */
    protected $threshold;

    /**
     * Date format of the log filename.
     */
    protected string $filenameFormat = 'Y-m-d';

    /**
     * Extension of the log file.
     */
    protected string $filenameExtension = 'log';

    protected ?Format $logFormat = null;

    protected ?Filename $logFilename = null;

    /**
     * @param FilesystemOperator $filesystem Flysystem filesystem abstraction
     * @param string             $threshold  Lowest level of logging to write
     * @param array              $params
     * @throws ReflectionException
     */
    public function __construct(FilesystemOperator $filesystem, $threshold, array $params = [])
    {
        parent::__construct($params);

        $this->filesystem = $filesystem;
        $this->threshold = $threshold;
        $this->logFormat = new LogFormat();
        $this->logFilename = new LogFilename();
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        // If the level is greater than or equal to the threshold, then we should log it.
        if ($this->levels[$level] >= $this->levels[$this->threshold]) {
            // Call the LogFilename static function create to get the filename.
            $filename = $this->logFilename->create($level, $this->filenameFormat, $this->filenameExtension);

            // Create a new LogFormat instance to format the log entry.
            $message = $this->logFormat->create($level, $message, $context);

            $contents = '';

            // Check and see if the file exists.
            if ($this->filesystem->fileExists($filename)) {
                // Get the contents of the file before writing to it. This is so it can be appended.
                $contents = $this->filesystem->read($filename);
            }

            $contents .= $message;

            // Write the log message from the Log Format instance to the Log Format file name instance.
            $this->filesystem->write($filename, $contents . "\n");
        }
    }

    /**
     * Set the log filename format using PHP's date parameters.
     *
     * @link https://secure.php.net/manual/en/function.date.php
     *
     * @param string $filenameFormat
     */
    public function setFilenameFormat($filenameFormat)
    {
        $this->filenameFormat = $filenameFormat;
    }

    /**
     * Set the filename extension. Ex: 'log' will be '.log'.
     *
     * @param string $filenameExtension
     */
    public function setFilenameExtension($filenameExtension)
    {
        $this->filenameExtension = $filenameExtension;
    }

    /**
     * Optionally create your own Format class and set it to be used instead.
     */
    public function setLogFormat(Format $logFormat)
    {
        $this->logFormat = $logFormat;
    }

    /**
     * Optionally create your own Filename class and use this method to use it.
     */
    public function setLogFilename(Filename $logFilename)
    {
        $this->logFilename = $logFilename;
    }
}
