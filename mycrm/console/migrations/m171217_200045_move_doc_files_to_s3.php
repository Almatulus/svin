<?php

use core\models\order\OrderDocument;
use core\services\order\OrderDocumentService;
use yii\db\Migration;

/**
 * Class m171217_200045_move_doc_files_to_s3
 */
class m171217_200045_move_doc_files_to_s3 extends Migration
{
    private $service;

    public function __construct(
        OrderDocumentService $service,
        array $config = []
    ) {
        $this->service = $service;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ( ! YII_ENV_PROD) {
            return;
        }
        /* @var OrderDocument[] $order_docs */
        $order_docs     = OrderDocument::find()->all();
        $root_directory = Yii::getAlias('@frontend');

        foreach ($order_docs as $orderDoc) {
            $file_path = $root_directory.$orderDoc->path;
            if (file_exists($file_path)) {
                $orderDoc->path = $this->service->uploadToS3(
                    $orderDoc->order,
                    $file_path
                );
                if ( ! $orderDoc->save()) {
                    $errors = $orderDoc->getErrors();
                    throw new DomainException(reset($errors)[0]);
                }
                sleep(2);
                echo $orderDoc->path."\n";
            } else {
                $orderDoc->delete();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if ( ! YII_ENV_PROD) {
            return;
        }
        /* @var OrderDocument[] $order_docs */
        $order_docs     = OrderDocument::find()->all();
        $root_directory = Yii::getAlias('@frontend');

        foreach ($order_docs as $orderDoc) {
            $file_path = $root_directory.$orderDoc->path;
            if ( ! file_exists($file_path)) {
                $key = str_replace(
                    "https://s3.amazonaws.com/mycrmkzbucket/",
                    "",
                    $orderDoc->path
                );
                Yii::$app->get('s3')->delete($key);
                echo $key."\n";
            } else {
                echo $file_path."\n";
            }
        }
    }
}
