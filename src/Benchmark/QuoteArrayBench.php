<?php

declare(strict_types=1);

namespace Tigrov\Yii3\Benchmarks\Benchmark;

use Yiisoft\Db\Schema\Quoter;

use function array_map;
use function range;
use function str_repeat;

/**
 * @Iterations(5)
 * @Revs(1000)
 * @Groups({"typecast"})
 * @BeforeMethods({"before"})
 */
class QuoteArrayBench
{
    private array $array;

    private Quoter $quoter;

    /**
     * Load the bulk of the definitions.
     */
    public function before(): void
    {
        foreach (range('a', 'z') as $letter) {
            $this->array[] = str_repeat($letter, 5);
        }

        $this->quoter = new Quoter('`', '`');
    }

    public function benchForeachIndex(): void
    {
        foreach ($this->array as $i => $value) {
            $this->array[$i] = $this->quoter->quoteColumnName($value);
        }
    }

    public function benchForeachLink(): void
    {
        foreach ($this->array as &$value) {
            $value = $this->quoter->quoteColumnName($value);
        }
        unset($value);
    }

    public function benchArrayMapFn(): void
    {
        $this->array = array_map(fn ($value) => $this->quoter->quoteColumnName($value), $this->array);
    }

    public function benchArrayMapStaticFn(): void
    {
        $quoter = $this->quoter;
        $array = $this->array;
        $this->array = array_map(static fn ($value) => $quoter->quoteColumnName($value), $array);
    }

    public function benchArrayMapMethod(): void
    {
        $this->array = array_map([$this->quoter, 'quoteColumnName'], $this->array);
    }
}
