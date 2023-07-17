<?php
namespace frontend\controllers;

use core\models\Image;
use frontend\search\ImageSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ImageController extends Controller
{
    const DEFAULT_IMAGE = 392;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'index', 'upload'],
                'rules' => [
                    [
                        'actions' => ['create', 'index', 'upload'],
                        'allow' => true,
                        'roles' => ['administrator'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ]
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ImageSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Image model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        if (\Yii::$app->request->isPost)
        {
            $imageFile = UploadedFile::getInstanceByName('imageFile');
            if ($imageFile !== null && $image = Image::uploadImage($imageFile, $imageFile->name, Image::TYPE_SYSTEM))
            {
                return $this->redirect('index');
            }
        }
        return $this->render('create');
    }

    /**
     * Crop image
     *
     * @param integer $id
     * @param integer $size
     *
     * @throws NotFoundHttpException
     */
    public function actionImage($id, $size)
    {
        /* @var $model Image */
        $model = $this->findModel($id);

        $image = $model->getResizeImage($size);

        header('Content-type: image/' . $image->getImageFormat());
        echo $image;
    }

    /**
     * Deletes an existing Company model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionUpload()
    {
        $fileName = 'file';
        $uploadPath = './uploads';

        if (isset($_FILES[$fileName])) {
            $file = \yii\web\UploadedFile::getInstanceByName($fileName);

            //Print file data
            //print_r($file);

            if ($file->saveAs($uploadPath . '/' . $file->name)) {
                //Now save file data to database

                echo \yii\helpers\Json::encode($file);
            }
        }

        return false;
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Image the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Image::findOne($id)) !== null) {
            return $model;
        } elseif (($model = Image::findOne(self::DEFAULT_IMAGE)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}