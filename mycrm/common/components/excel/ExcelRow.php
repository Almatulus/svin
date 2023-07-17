<?php

namespace common\components\excel;

/**
 *
 */
class ExcelRow
{
    private $data;
    private $bold;

    public function __construct(array $data, bool $bold = false)
    {
        $this->data = $data;
        $this->bold = $bold;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isBold(): bool
    {
        return $this->bold;
    }
}