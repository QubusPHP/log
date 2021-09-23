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

namespace Qubus\Log\Loggers;

use Psr\Log\LogLevel;
use Qubus\Exception\Data\TypeException;
use Qubus\Log\Format;
use Qubus\Log\LogFormat;
use Stringable;

use function array_merge;
use function implode;
use function ltrim;
use function mail;
use function sprintf;
use function strpos;
use function wordwrap;

class PhpMailLogger extends BaseLogger
{
    public string $subject;

    public int $maxColumn = 50;

    /** @var string|array $to */
    protected $to;

    protected array $headers = [];

    protected array $parameters = [];

    protected string $contentType;

    protected string $encoding = 'utf-8';

    protected string $from;

    /**
     * Lowest level of logging to write.
     */
    protected string|LogLevel $threshold;

    protected ?Format $logFormat = null;

    /**
     * @param string|LogLevel $threshold Lowest level of logging to write.
     * @param array  $params
     * @throws ReflectionException
     */
    public function __construct(string|LogLevel $threshold, array $params = [])
    {
        parent::__construct($params);

        $this->threshold = $threshold;
        $this->logFormat = new LogFormat();
    }

    /**
     * @param string|LogLevel $level
     * @param array $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        // If the level is greater than or equal to the threshold, then we should log it.
        if ($this->levels[$level] >= $this->levels[$this->threshold]) {
            // Create a new LogFormat instance to format the log entry.
            $body = $this->logFormat->create($level, $message, $context);
            // Send email.
            $this->send($body);
        }
    }

    /**
     * @param $content
     */
    protected function send($content): void
    {
        $contentType = ($this->getContentType() ?: $this->isHtml($content)) ? 'text/html' : 'text/plain';

        if ($contentType !== 'text/html') {
            $content = wordwrap($content, $this->maxColumn);
        }

        $headers = ltrim(implode("\r\n", $this->headers) . "\r\n", "\r\n");
        $headers .= 'Content-type: ' . $contentType . '; charset=' . $this->getEncoding() . "\r\n";

        if ($contentType === 'text/html' && false === strpos($headers, 'MIME-Version:')) {
            $headers .= 'MIME-Version: 1.0' . "\r\n";
        }

        $subject = $this->subject;

        $parameters = implode(' ', $this->parameters);

        foreach ($this->to as $to) {
            mail($to, $subject, $content, $headers, $parameters);
        }
    }

    /**
     * @param array $headers
     * @return $this
     * @throws TypeException
     */
    public function setHeader($headers)
    {
        foreach ((array) $headers as $header) {
            if (strpos($header, "\n") !== false || strpos($header, "\r") !== false) {
                throw new TypeException('Headers can not contain newline characters for security reasons.');
            }
            $this->headers[] = $header;
        }

        return $this;
    }

    /**
     * @param string|array $parameters
     * @return $this
     */
    public function setParameter($parameters)
    {
        $this->parameters = array_merge($this->parameters, (array) $parameters);

        return $this;
    }

    /**
     * @param string $contentType
     * @return $this
     * @throws TypeException
     */
    public function setContentType($contentType)
    {
        if (strpos($contentType, "\n") !== false || strpos($contentType, "\r") !== false) {
            throw new TypeException('The content type can not contain newline characters to prevent email header injection.');
        }

        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string $encoding
     * @return $this
     * @throws TypeException
     */
    public function setEncoding($encoding)
    {
        if (strpos($encoding, "\n") !== false || strpos($encoding, "\r") !== false) {
            throw new TypeException('The encoding can not contain newline characters to prevent email header injection');
        }

        $this->encoding = $encoding;

        return $this;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @param string|array $value
     */
    public function setTo($value): void
    {
        $this->to = (array) $value;
    }

    /**
     * @param string $value
     * @throws TypeException
     */
    public function setFrom($value): void
    {
        $this->setHeader(sprintf('From: %s', $value));
    }

    /**
     * @param array $data
     */
    protected function isHtml($data): bool
    {
        return $data[0] === '<';
    }
}
