<?php
namespace FpDbTest;

interface DatabaseReplaceParserInterface
{
    /**
     * @param $value
     * @return int|string
     */
    public function parseInt($value): int|string;

    /**
     * @param $value
     * @return int|string|float|bool|null
     */
    public function parseAny($value): int|string|float|bool|null;

    /**
     * @param $identifiers
     * @return string
     */
    public function parseIdentifiers($identifiers): string;

    /**
     * @param $value
     * @return float|string
     */
    public function parseFloat($value): float|string;


    /**
     * @param $values
     * @return string
     */
    public function parseValues($values): string;
}
