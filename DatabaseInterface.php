<?php

namespace FpDbTest;

interface DatabaseInterface
{
    /**
     * @param string $query
     * @param array $args
     * @return string
     */
    public function buildQuery(string $query, array $args = []): string;

    /**
     * @param $value
     * @return bool
     */
    public function skip($value): bool;

    /**
     * @param string $query
     * @param array $args
     * @return string
     */
    public function replaceKeys(string $query, array $args): string;
}
