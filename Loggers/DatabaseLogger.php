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

use PDO;
use Qubus\Exception\Data\TypeException;

use function array_keys;
use function implode;

class DatabaseLogger extends BaseLogger
{
    /** @var string|null $table */
    public ?string $table = null;

    /** @var PDO $db */
    protected PDO $db;

    /**
     * @param $value
     * @throws TypeException
     */
    public function setDb($value)
    {
        if (! $value instanceof PDO) {
            throw new TypeException('To connect to the database, you will need to use PDO.');
        }
        $this->db = $value;
    }

    /**
     * @return PDO
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
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
