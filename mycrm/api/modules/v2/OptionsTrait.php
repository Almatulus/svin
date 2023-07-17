<?php

namespace api\modules\v2;

use Yii;

trait OptionsTrait
{
    protected function getOptionsHeaders()
    {
        Yii::$app->response->headers->set(
            "Access-Control-Expose-Headers",
            implode(", ", [
                "X-Pagination-Per-Page",
                "X-Pagination-Current-Page",
                "X-Pagination-Page-Count",
                "X-Pagination-Total-Count",
            ])
        );
        Yii::$app->response->headers->set(
            "Access-Control-Allow-Origin", "*"
        );
        Yii::$app->response->headers->set(
            "X-Content-Type-Options", "nosniff"
        );
        Yii::$app->response->headers->set(
            "Access-Control-Allow-Headers",
            implode(', ', [
                "X-Requested-With",
                "content-type",
                "access-control-allow-origin",
                "access-control-allow-methods",
                "access-control-allow-headers"
            ])
        );
        Yii::$app->response->headers->set(
            "Access-Control-Allow-Methods",
            implode(', ', ["PUT", "PATCH", "POST", "GET", "HEAD", "OPTIONS", "DELETE"])
        );
    }
}