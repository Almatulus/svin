<?php

namespace core\services\medCard\dto;

/**
 * @property $comment
 * @property $comment_template_category_id
 */
class MedCardTabCommentData
{
    public $comment;
    public $comment_template_category_id;

    public function __construct($comment, $comment_template_category_id)
    {
        $this->comment = $comment;
        $this->comment_template_category_id = $comment_template_category_id;
    }
}