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

namespace Qubus\Log\Loggers;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Qubus\Exception\Data\TypeException;
use Qubus\Log\Format;
use Qubus\Log\LogFormat;
use ReflectionException;
use Stringable;

use function array_merge;
use function implode;
use function ltrim;
use function mail;
use function sprintf;
use function strpos;
use function wordwrap;

class PhpMailLogger extends BaseLogger implements LoggerInterface
{
    public string $subject;

    public int $maxColumn = 50;

    /** @var string|array $to */
    protected string|array $to;

    protected array $headers = [];

    protected array $parameters = [];

    protected string $contentType;

    protected string $encoding = 'utf-8';

    protected string $from;

    protected ?Format $logFormat = null;

    /**
     * @param string|LogLevel $threshold Lowest level of logging to write.
     * @param array  $params
     * @throws ReflectionException
     */
    public function __construct(
        /** @var string|LogLevel $threshold Lowest level of logging to write. */
        public readonly string|LogLevel $threshold,
        array $params = []
    ) {
        parent::__construct(params: $params);
        $this->logFormat = new LogFormat();
    }

    /**
     * @param string|LogLevel $level
     * @param string|Stringable $message
     * @param array $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        // If the level is greater than or equal to the threshold, then we should log it.
        if ($this->levels[$level] >= $this->levels[$this->threshold]) {
            // Create a new LogFormat instance to format the log entry.
            $body = $this->logFormat->create($level, $message, $context);
            // Send email.
            $this->send(content: $body);
        }
    }

    /**
     * @param $content
     */
    protected function send($content): void
    {
        $contentType = ($this->getContentType() ?: $this->isHtml(data: $content)) ? 'text/html' : 'text/plain';

        if ($contentType !== 'text/html') {
            $content = wordwrap(string: $content, width: $this->maxColumn);
        }

        $headers = ltrim(string: implode(separator: "\r\n", array: $this->headers) . "\r\n", characters: "\r\n");
        $headers .= 'Content-type: ' . $contentType . '; charset=' . $this->getEncoding() . "\r\n";

        if ($contentType === 'text/html' && false === strpos(haystack: $headers, needle: 'MIME-Version:')) {
            $headers .= 'MIME-Version: 1.0' . "\r\n";
        }

        $subject = $this->subject;

        $parameters = implode(separator: ' ', array: $this->parameters);

        foreach ($this->to as $to) {
            mail(
                to: $to,
                subject: $subject,
                message: $content,
                additional_headers: $headers,
                additional_params: $parameters
            );
        }
    }

    /**
     * @param array $headers
     * @return $this
     * @throws TypeException
     */
    public function setHeader(array $headers): static
    {
        foreach ((array) $headers as $header) {
            if (strpos(haystack: $header, needle: "\n") !== false || strpos(haystack: $header, needle: "\r") !== false) {
                throw new TypeException(message: 'Headers can not contain newline characters for security reasons.');
            }
            $this->headers[] = $header;
        }

        return $this;
    }

    /**
     * @param array|string $parameters
     * @return $this
     */
    public function setParameter(array|string $parameters): static
    {
        $this->parameters = array_merge($this->parameters, (array) $parameters);

        return $this;
    }

    /**
     * @param string $contentType
     * @return $this
     * @throws TypeException
     */
    public function setContentType(string $contentType): static
    {
        if (strpos(haystack: $contentType, needle: "\n") !== false || strpos(haystack: $contentType, needle: "\r") !== false) {
            throw new TypeException(
                message: 'The content type can not contain newline characters to prevent email header injection.'
            );
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
    public function setEncoding(string $encoding): static
    {
        if (strpos($encoding, "\n") !== false || strpos($encoding, "\r") !== false) {
            throw new TypeException(
                message: 'The encoding can not contain newline characters to prevent email header injection'
            );
        }

        $this->encoding = $encoding;

        return $this;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @param array|string $value
     */
    public function setTo(array|string $value): void
    {
        $this->to = (array) $value;
    }

    /**
     * @param string $value
     * @throws TypeException
     */
    public function setFrom(string $value): void
    {
        $this->setHeader((array)sprintf('From: %s', $value));
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function isHtml(array $data): bool
    {
        return $data[0] === '<';
    }
}
