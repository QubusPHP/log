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
use Qubus\Mail\Mailer;

class SwiftMailerLogger extends BaseLogger
{
    /** @var string|null $subject */
    public ?string $subject = null;

    protected ?Mailer $mailer = null;

    /** @var string|array $to */
    protected $to;

    /** @var string $from */
    protected string $from;

    /** @var string $encoding */
    protected string $encoding = 'utf-8';

    /**
     * Lowest level of logging to write.
     *
     * @var LogLevel
     */
    protected $threshold;

    /** @var Format|null $logFormat */
    protected ?Format $logFormat = null;

    public function __construct(Mailer $mailer, $threshold, array $params = [])
    {
        parent::__construct($params);
        
        $this->threshold = $threshold;
        $this->logFormat = new LogFormat();
        $this->mailer = $mailer;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
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
        $this->mailer->send(function ($message) use ($content) {
            $message->from($this->from);
            $message->to($this->to);
            $message->subject($this->subject);
            $message->body($content);
            $message->charset($this->encoding);
            $message->html(true);
        });
    }

    /**
     * @param $encoding
     * @return $this
     * @throws TypeException
     */
    public function setEncoding($encoding)
    {
        if (strpos($encoding, "\n") !== false || strpos($encoding, "\r") !== false) {
            throw new TypeException('The encoding can not contain newline characters to prevent email header injection.');
        }

        $this->encoding = $encoding;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function setFrom($value)
    {
        $this->from = $value;
    }

    public function setTo($value)
    {
        $this->to = (array) $value;
    }
}
