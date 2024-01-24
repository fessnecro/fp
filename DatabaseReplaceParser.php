<?php
namespace FpDbTest;

use Exception;
use \mysqli;

class DatabaseReplaceParser implements DatabaseReplaceParserInterface
{

    /**
     * Разрешенные типы данных
     */
    const ALLOWED_TYPES = ['string', 'integer', 'double', 'boolean', 'NULL'];

    /**
     * @var mysqli
     */
    private mysqli $mysqli;

    /**
     * @param mysqli $mysqli
     */
    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    /**
     * @param $value
     * @return int|string|float|bool|null
     * @throws Exception
     */
    public function parseAny($value): int|string|float|bool|null
    {
        $type = gettype($value);
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new Exception('Wrong date type');
        }

        if ($type == 'string') {
            $value = "'" . $this->mysqli->real_escape_string($value) . "'";
        }

        return $this->preParse($value) ?? $value;
    }

    /**
     * @param $identifiers
     * @return string
     */
    public function parseIdentifiers($identifiers): string
    {
        if (is_array($identifiers)) {
            return implode(', ',  array_map(function ($val) {
                return "`" . $this->mysqli->real_escape_string($val) . "`";
            }, $identifiers));
        }

        return "`" . $this->mysqli->real_escape_string($identifiers) . "`";
    }

    /**
     * @param $value
     * @return int|string
     * @throws Exception
     */
    public function parseInt($value): int|string
    {
        if (is_bool($value)) {
            $value = (int) $value;
        }

        if (!in_array(gettype($value), ['integer', 'NULL'])) {
            throw new Exception('Wrong date type');
        }

        return $this->preParse($value) ?? (int) $value;
    }

    /**
     * @param $value
     * @return float|string
     * @throws Exception
     */
    public function parseFloat($value): float|string
    {
        if (!in_array(gettype($value), ['double', 'NULL'])) {
            throw new Exception('Wrong date type');
        }

        return $this->preParse($value) ?? (float) $value;
    }

    /**
     * @param $values
     * @return string
     * @throws Exception
     */
    public function parseValues($values): string
    {
        if (array_is_list($values)) {
            return implode(', ',  array_map(function ($val) {
                return $this->mysqli->real_escape_string($val);
            }, $values));
        }

        $result = '';

        $i = 0;
        foreach ($values as $key => $val) {
            $key = $this->parseIdentifiers($key);

            $val = $this->parseAny($val);

            $result .= $key . ' = ' . $val . (!(count($values) - 1 == $i) ? ', ' : '');
            $i++;
        }

        return $result;
    }

    /**
     * @param $value
     * @return int|string|null
     */
    private function preParse($value): int|string|null
    {
        if (is_bool($value)) {
            return (int) $value;
        }

        if (is_null($value)) {
            return 'NULL';
        }

        return null;
    }
}
