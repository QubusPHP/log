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
use Qubus\Exception\Data\TypeException;
use Stringable;

use function array_keys;
use function implode;

class DatabaseLogger extends BaseLogger
{
    public ?string $table = null;

    protected PDO $db;

    /**
     * @param PDO $value
     * @throws TypeException
     */
    public function setDb($value): void
    {
        if (! $value instanceof PDO) {
            throw new TypeException('To connect to the database, you will need to use PDO.');
        }
        $this->db = $value;
    }

    public function getDb(): PDO
    {
        return $this->db;
    }

    /**
     * @param mixed $level
     * @param string|Stringable $message
     * @param array $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->execute(
            [
                'date'    => $this->getDate(),
                'level'   => $level,
                'message' => $this->interpolate($message, $context),
                'context' => $this->stringify($context),
            ]
        );
    }

    /**
     * @param array $data
     */
    protected function execute(array $data)
    {
        $keys = array_keys($data);
        $sth = $this->getDb()->prepare('INSERT INTO ' . $this->table
            . ' (' . implode(', ', $keys) . ') VALUES ( ' . implode(', :', $keys) . ')');
        $sth->execute($data);
    }
}
