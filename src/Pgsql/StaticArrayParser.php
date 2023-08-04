<?php

declare(strict_types=1);

namespace Tigrov\Yii3\Benchmarks\Pgsql;

use function in_array;

/**
 * Array representation to PHP array parser for PostgreSQL Server.
 */
final class StaticArrayParser
{
    /**
     * Convert an array from PostgresSQL to PHP.
     *
     * @param string|null $value String to convert.
     */
    public static function parse(string|null $value): array|null
    {
        return $value !== null && $value[0] === '{'
            ? self::parseArray($value)
            : null;
    }

    /**
     * Parse PostgreSQL array encoded in string.
     *
     * @param string $value String to parse.
     * @param int $i parse starting position.
     */
    private static function parseArray(string $value, int &$i = 0): array
    {
        if ($value[++$i] === '}') {
            ++$i;
            return [];
        }

        for ($result = [];; ++$i) {
            $result[] = match ($value[$i]) {
                '{' => self::parseArray($value, $i),
                ',', '}' => null,
                '"' => self::parseQuotedString($value, $i),
                default => self::parseUnquotedString($value, $i),
            };

            if ($value[$i] === '}') {
                ++$i;
                return $result;
            }
        }
    }

    /**
     * Parses quoted string.
     */
    private static function parseQuotedString(string $value, int &$i): string
    {
        for ($result = '', ++$i;; ++$i) {
            if ($value[$i] === '\\') {
                ++$i;
            } elseif ($value[$i] === '"') {
                ++$i;
                return $result;
            }

            $result .= $value[$i];
        }
    }

    /**
     * Parses unquoted string.
     */
    private static function parseUnquotedString(string $value, int &$i): string|null
    {
        for ($result = '';; ++$i) {
            if (in_array($value[$i], [',', '}'], true)) {
                return $result !== 'NULL'
                    ? $result
                    : null;
            }

            $result .= $value[$i];
        }
    }
}
