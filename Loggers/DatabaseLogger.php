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

use PDO;
use Psr\Log\LoggerInterface;
use Qubus\Exception\Data\TypeException;
use Stringable;

use function array_keys;
use function implode;
use function sprintf;

class DatabaseLogger extends BaseLogger implements LoggerInterface
{
    public ?string $table = null;

    protected PDO $db;

    /**
     * @throws TypeException
     */
    public function setDb(PDO $value): void
    {
        if (! $value instanceof PDO) {
            throw new TypeException(message: 'To connect to the database, you will need to use PDO.');
        }
        $this->db = $value;
    }

    public function getDb(): PDO
    {
        return $this->db;
    }

    /**
     * @param mixed $level
     * @param array $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->execute(
            [
                'date'    => $this->getDate(),
                'level'   => $level,
                'message' => $this->interpolate(message: $message, context: $context),
                'context' => $this->stringify(data: $context),
            ]
        );
    }

    /**
     * @param array $data
     */
    protected function execute(array $data)
    {
        $keys = array_keys(array: $data);
        $sth = $this->getDb()->prepare(query: sprintf('INSERT INTO %s', $this->table)
            . ' (' . implode(separator: ', ', array: $keys) . ') VALUES ( ' . implode(separator: ', :', array: $keys) . ')');
        $sth->execute(params: $data);
    }
}
