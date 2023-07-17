<?php

use core\models\File;
use yii\db\Migration;

/**
 * Class m171218_174632_add_s3_files_timestamp
 */
class m171218_174632_add_s3_files_timestamp extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%s3_files}}',
            'name',
            $this->string()
        );
        $this->addColumn(
            '{{%s3_files}}',
            'created_at',
            $this->timestamp()->defaultExpression('NOW()')
        );

        /* @var File[] $files */
        $files = \core\models\File::find()->all();

        foreach ($files as $file) {
            $file->name       = basename($file->path);
            $file->created_at = null;
            if ( ! $file->save()) {
                $errors = $file->getErrors();
                throw new DomainException(reset($errors)[0]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%s3_files}}', 'name');
        $this->dropColumn('{{%s3_files}}', 'created_at');
    }
}
