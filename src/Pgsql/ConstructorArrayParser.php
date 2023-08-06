<?php

declare(strict_types=1);

namespace Tigrov\Yii3\Benchmarks\Pgsql;

use function in_array;

/**
 * Array representation to PHP array parser for PostgreSQL Server.
 */
final class ConstructorArrayParser
{
    private int $i = 0;

    public function __construct(private string|null $value)
    {
    }

    public function __invoke(): array|null
    {
        return $this->parse();
    }

    /**
     * Convert an array from PostgresSQL to PHP.
     */
    public function parse(): array|null
    {
        $this->i = 0;

        return $this->value !== null && $this->value[0] === '{'
            ? $this->parseArray()
            : null;
    }

    /**
     * Parse PostgreSQL array encoded in string.
     */
    private function parseArray(): array
    {
        if ($this->value[++$this->i] === '}') {
            ++$this->i;
            return [];
        }

        for ($result = [];; ++$this->i) {
            $result[] = match ($this->value[$this->i]) {
                '{' => $this->parseArray(),
                ',', '}' => null,
                '"' => $this->parseQuotedString(),
                default => $this->parseUnquotedString(),
            };

            if ($this->value[$this->i] === '}') {
                ++$this->i;
                return $result;
            }
        }
    }

    /**
     * Parses quoted string.
     */
    private function parseQuotedString(): string
    {
        for ($result = '', ++$this->i;; ++$this->i) {
            if ($this->value[$this->i] === '\\') {
                ++$this->i;
            } elseif ($this->value[$this->i] === '"') {
                ++$this->i;
                return $result;
            }

            $result .= $this->value[$this->i];
        }
    }

    /**
     * Parses unquoted string.
     */
    private function parseUnquotedString(): string|null
    {
        for ($result = '';; ++$this->i) {
            if (in_array($this->value[$this->i], [',', '}'], true)) {
                return $result !== 'NULL'
                    ? $result
                    : null;
            }

            $result .= $this->value[$this->i];
        }
    }
}
