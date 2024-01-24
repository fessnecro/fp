<?php

namespace FpDbTest;

use Exception;
use mysqli;

class Database implements DatabaseInterface
{
    /**
     * @var mysqli
     */
     private mysqli $mysqli;

    /**
     * @var DatabaseReplaceParserInterface
     */
     private DatabaseReplaceParserInterface $parser;

    /**
     * @param mysqli $mysqli
     * @param DatabaseReplaceParserInterface $parser
     */
    public function __construct(mysqli $mysqli, DatabaseReplaceParserInterface $parser)
    {
        $this->mysqli = $mysqli;
        $this->parser = $parser;
    }

    /**
     * @param string $query
     * @param array $args
     * @return string
     * @throws Exception
     */
    public function buildQuery(string $query, array $args = []): string
    {
        $query = $this->replaceKeys($query, $args);

        return $query;
    }

    /**
     * @param string $query
     * @param array $args
     * @return string
     * @throws Exception
     */
    public function replaceKeys(string $query, array $args): string
    {
        $counter = 0;
        return preg_replace_callback("/(\?[#dfa]?)|({.*\?[#dfa]?.*})/", function ($match) use (&$counter, $args) {
            if (!array_key_exists($counter, $args)) {
                throw new Exception('Wrong arguments count');
            }

            $key = $match[0];
            $value = $args[$counter];

            //Блок с условием
            if (isset($match[2])) {
                $block = $match[2];
                preg_match("/\?[#dfa]?/", $match[2], $match2);
                $key = $match2[0];
                if (!$value) {
                    return '';
                }

                $block = str_replace(['{', '}'], '', $block);
                return str_replace($key, $this->replaceKey($key, $value), $block);
            }

            $counter++;

            return $this->replaceKey($key, $value);
        }, $query);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    private function replaceKey($key, $value): mixed
    {
       return match($key) {
            //Неопределенный тип
            '?' => call_user_func(function () use ($value) {
                return $this->parser->parseAny($value);
            }),
            //Массив идентифиакторов
            '?#' => call_user_func(function () use ($value) {
                return $this->parser->parseIdentifiers($value);
            }),
            //Целое число
            '?d' => call_user_func(function () use ($value) {
                return $this->parser->parseInt($value);
            }),
            //С плавающей точкой
            '?f' =>  call_user_func(function () use ($value) {
                return $this->parser->parseFloat($value);
            }),
            //Массив значений
            '?a' => call_user_func(function () use ($value) {
                return $this->parser->parseValues($value);
            }),
        };
    }

    /**
     * @param $value
     * @return bool
     */
    public function skip($value): bool
    {
        if (gettype($value) == 'integer' && $value <= 1) {
            $value = (bool) $value;
        }

        return !in_array(gettype($value), ['boolean', 'NULL']);
    }
}
