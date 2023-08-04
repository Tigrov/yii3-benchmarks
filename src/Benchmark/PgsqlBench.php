<?php

declare(strict_types=1);

namespace Tigrov\Yii3\Benchmarks\Benchmark;

use Tigrov\Yii3\Benchmarks\Pgsql\StaticArrayParser;
use Yiisoft\Db\Pgsql\ArrayParser;
use Yiisoft\Db\Pgsql\ColumnSchema;
use Yiisoft\Db\Schema\SchemaInterface;

/**
 * @Iterations(5)
 * @Revs(1000)
 * @Groups({"pgsql"})
 * @BeforeMethods({"before"})
 */
class PgsqlBench
{
    private const ITEMS_COUNT = 1000;

    private string $rawIntArray;

    private ColumnSchema $column;

    private ArrayParser $parser;

    /**
     * Load the bulk of the definitions.
     */
    public function before(): void
    {
        $intList = range(0, self::ITEMS_COUNT);
        $this->rawIntArray = '{' . implode(',', $intList) . '}';

        $this->column = new ColumnSchema('int_array');
        $this->column->type(SchemaInterface::TYPE_INTEGER);
        $this->column->dbType(SchemaInterface::TYPE_INTEGER);
        $this->column->phpType(SchemaInterface::PHP_TYPE_INTEGER);
        $this->column->dimension(1);

        $this->parser = new ArrayParser();
    }

    public function benchParseIntArrayCurrent(): void
    {
        $parsedArray = $this->getArrayParser()->parse($this->rawIntArray);
    }

    public function benchParseIntArrayProperty(): void
    {
        $parsedArray = $this->parser->parse($this->rawIntArray);
    }

    public function benchParseIntArrayVar(): void
    {
        $parser = new ArrayParser();
        $parsedArray = $parser->parse($this->rawIntArray);
    }

    public function benchParseIntArrayNew(): void
    {
        $parsedArray = (new ArrayParser())->parse($this->rawIntArray);
    }

    public function benchParseIntArrayStatic(): void
    {
        $parsedArray = StaticArrayParser::parse($this->rawIntArray);
    }

    public function benchParseThenIntvalIntArray(): void
    {
        $parsedArray = (new StaticArrayParser())->parse($this->rawIntArray);
        $castArray = array_map('intval', $parsedArray);
    }

    public function benchPhpTypecastIntArray(): void
    {
        $castArray = $this->column->phpTypecast($this->rawIntArray);
    }

    private function getArrayParser(): ArrayParser
    {
        return new ArrayParser();
    }
}
