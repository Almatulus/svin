<?php

namespace frontend\modules\company\controllers;

use core\forms\company\CompanyUpdateForm;
use core\models\company\Company;
use core\models\Image;
use core\services\company\CompanyService;
use core\services\dto\CompanyDetailsData;
use core\services\dto\PersonData;
use frontend\modules\division\search\DivisionSearch;
use frontend\search\CompanyPaymentLogSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * DefaultController implements the CRUD actions for Company model.
 */
class DefaultController extends Controller
{
    private $companyService;

    public function __construct(
        $id,
        $module,
        CompanyService $companyService,
        $config = []
    ) {
        $this->companyService = $companyService;
        parent::__construct($id, $module, $config = []);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['update'],
                        'allow'   => true,
                        'roles'   => ['companyView'],
                    ],
                    [
                        'actions' => ['payment'],
                        'allow'   => true,
                        'roles'   => ['paymentAdmin'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionUpdate()
    {
        $user_company_id = Yii::$app->user->identity->company_id;
        $model           = $this->findModel($user_company_id);

        if ( ! Yii::$app->user->can('companyUpdate', ['model' => $model])) {
            throw new ForbiddenHttpException('You are not allowed');
        }

        $form = new CompanyUpdateForm($model);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $imageFile = UploadedFile::getInstance($form, 'image_file');
            if ($imageFile !== null
                && $image = Image::uploadImage($imageFile)
            ) {
                $form->logo_id = $image->id;
            }

            try {
                $this->companyService->restrictEdit(
                    $model->id,
                    new CompanyDetailsData(
                        $form->address,
                        $form->bank,
                        $form->bik,
                        $form->bin,
                        $form->iik,
                        $form->license_issued,
                        $form->license_number,
                        $form->name,
                        $form->phone,
                        $form->widget_prefix,
                        $form->online_start,
                        $form->online_finish,
                        $form->logo_id
                    ),
                    new PersonData(
                        $form->head_name,
                        $form->head_surname,
                        $form->head_patronymic
                    ),
                    $form->notify_about_order,
                    $form->cashback_percent
                );
                Yii::$app->session->setFlash('success',
                    Yii::t('app', 'Successful saving'));
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }

            return $this->redirect(['update']);
        }

        $searchModel  = new DivisionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->company($model->id)->permitted();

        return $this->render('update', [
            'model'        => $form,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Create payment
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionPayment()
    {
        $user_company_id = Yii::$app->user->identity->company_id;
        $company         = $this->findModel($user_company_id);

        $searchModel             = new CompanyPaymentLogSearch();
        $searchModel->company_id = $company->id;
        $dataProvider
                                 = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('payment', [
            'company'      => $company,
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel
        ]);
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Company the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
