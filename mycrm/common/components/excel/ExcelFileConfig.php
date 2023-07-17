<?php

namespace common\components\excel;

/**
 * @property $filename
 * @property $creator
 * @property $title
 * @property $description
 * @property $subject
 * @property $keywords
 * @property $category
 */
class ExcelFileConfig
{
    public $filename;
    public $title;
    public $creator;
    public $description;
    public $subject;
    public $keywords;
    public $category;

    public function __construct(
        string $filename,
        string $creator,
        string $title
    ) {
        $this->filename = $filename;
        $this->creator  = $creator;
        $this->title    = $title;
    }
}