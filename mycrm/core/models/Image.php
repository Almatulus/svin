<?php

namespace core\models;

use core\models\company\Company;
use Yii;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%images}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property integer $order
 *
 * @property Company[] $companies
 * @property Staff[] $staff_images
 * @property Staff[] $staff_documents
 */
class Image extends \yii\db\ActiveRecord
{
    const DIRECTORY_NAME_LENGTH = 3; // chars
    const DIRECTORY_LEVEL = 3; // subdirectories

    const DIRECTORY_ROOT_SYSTEM = "image";
    const DIRECTORY_ROOT_UPLOAD = "uploads";

    const WEB_DIRECTORY = "web";

    const TYPE_SYSTEM = 1;
    const TYPE_UPLOAD = 2;

    const SIZE_AVATAR = 200;
    const SIZE_IMAGE = 800;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%images}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'order'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'type' => Yii::t('app', 'Type'),
            'order' => Yii::t('app', 'Order'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(Company::className(), ['logo_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffImages()
    {
        return $this->hasMany(Staff::className(), ['image_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffDocuments()
    {
        return $this->hasMany(Staff::className(), ['document_scan_id' => 'id']);
    }

    /**
     * Returns root path
     * @return string
     */
    private function getRootPath()
    {
        if ($this->type == self::TYPE_UPLOAD)
            return self::DIRECTORY_ROOT_UPLOAD;
        elseif ($this->type == self::TYPE_SYSTEM)
            return self::DIRECTORY_ROOT_SYSTEM;
        return "";
    }

    /**
     * Returns directory name
     * @param string $name image name
     * @param integer $level
     * @return string
     */
    private static function getDirectoryName($name, $level)
    {
        return substr($name, $level * self::DIRECTORY_NAME_LENGTH, self::DIRECTORY_NAME_LENGTH);
    }

    /**
     * Returns image directory
     * @param string $name image name
     * @param string $root image root directory
     * @return string
     */
    private static function getDirectoryPath($name, $root)
    {
        $path_name = DIRECTORY_SEPARATOR . $root . DIRECTORY_SEPARATOR;
        $i = 0;
        while (self::DIRECTORY_LEVEL > $i)
        {
            $path_name .= self::getDirectoryName($name, $i++) . DIRECTORY_SEPARATOR;
            $real_path = Yii::getAlias('@frontend') . DIRECTORY_SEPARATOR . self::WEB_DIRECTORY . $path_name;
            if (!file_exists($real_path) && !is_dir($real_path)) {
                mkdir($real_path);
            }
        }
        return $path_name;
    }

    /**
     * Uploads image
     * @param UploadedFile $file
     * @param null $name
     * @param int $type
     * @return Image|null
     * @throws \yii\db\Exception
     */
    public static function uploadImage(UploadedFile $file, $name = null, $type = self::TYPE_UPLOAD)
    {
        $transaction =  \Yii::$app->db->beginTransaction();
        try
        {
            $model = new Image();
            $model->type = $type;
            if ($name == null) {
                $name = Yii::$app->security->generateRandomString() . "." .  $file->extension;
            }
            $model->name = $name;
            if($model->save())
            {
                $file->saveAs($model->getRealPath());
                $transaction->commit();
                return $model;
            }
        }
        catch (\Exception $e)
        {
            $transaction->rollBack();
        }
        return null;
    }

    /**
     * Returns avatar url
     * @return string image path
     */
    public function getPath()
    {
        return self::getDirectoryPath($this->name, $this->getRootPath()) . $this->name;
    }

    /**
     * Returns real path
     * @return string
     */
    public function getRealPath()
    {
        return Yii::getAlias('@frontend') . DIRECTORY_SEPARATOR . self::WEB_DIRECTORY . $this->getPath();
    }

    /**
     * Returns resize image
     * @param integer $size
     * @return \Imagick
     */
    public function getResizeImage($size)
    {
        try {
            $image = new \Imagick($this->getRealPath());
//            $image->resizeImage($size, $size,\Imagick::FILTER_CATROM, 1);
            $image->thumbnailImage($size, 0);
            return $image;
        } catch (\ImagickException $e) {
            return null;
        }
    }

    public function getAvatarImageUrl()
    {
        return $this->getImageUrl(self::SIZE_AVATAR);
    }

    public function getBigImageUrl()
    {
        return $this->getImageUrl(self::SIZE_IMAGE);
    }

    public function getImageUrl(int $size): string
    {
        return self::getImageUrlTo($this->id, $size);
    }

    public static function getImageUrlTo(int $image_id, int $size): string
    {
        return \Yii::$app->params['crm_host'].
            Url::to([
                '/image/image',
                'id'   => $image_id,
                'size' => $size,
            ]);
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'order',
            'big_image_url' => function () {
                return $this->getBigImageUrl();
            },
            'avatar_image_url' => function () {
                return $this->getAvatarImageUrl();
            },
        ];
    }

    /**
     * Creates and returns resource of image
     * @param string $file_path
     * @return resource
     */
    private static function createImage($file_path)
    {
        $type = exif_imagetype($file_path);

        $allowedTypes = array(
            1,  // [] gif
            2,  // [] jpg
            3,  // [] png
        );
        if (!in_array($type, $allowedTypes)) {
            return false;
        }

        $im = null;
        switch ($type) {
            case 1 :
                $im = imagecreatefromgif($file_path);
                break;
            case 2 :
                $im = imagecreatefromjpeg($file_path);
                break;
            case 3 :
                $im = imagecreatefrompng($file_path);
                break;
        }
        return $im;
    }
}
