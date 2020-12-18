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

use Qubus\Exception\Data\TypeException;
use Qubus\Log\Format;
use Qubus\Log\LogFormat;

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
     *
     * @var LogLevel
     */
    protected $threshold;

    protected ?Format $logFormat = null;

    /**
     * @param string $threshold Lowest level of logging to write.
     * @param array  $params
     * @throws ReflectionException
     */
    public function __construct($threshold, array $params = [])
    {
        parent::__construct($params);

        $this->threshold = $threshold;
        $this->logFormat = new LogFormat();
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = [])
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
    protected function send($content)
    {
        $contentType = $this->getContentType() ?: $this->isHtml($content) ? 'text/html' : 'text/plain';

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
     * @param $headers
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
     * @param $parameters
     * @return $this
     */
    public function setParameter($parameters)
    {
        $this->parameters = array_merge($this->parameters, (array) $parameters);

        return $this;
    }

    /**
     * @param $contentType
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
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param $encoding
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

    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param $value
     */
    public function setTo($value)
    {
        $this->to = (array) $value;
    }

    /**
     * @param $value
     * @throws TypeException
     */
    public function setFrom($value)
    {
        $this->setHeader(sprintf('From: %s', $value));
    }

    /**
     * @param $data
     * @return bool
     */
    protected function isHtml($data)
    {
        return $data[0] === '<';
    }
}
