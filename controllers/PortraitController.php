<?php

namespace app\controllers;

use Yii;
use app\models\Portrait;
use app\models\PortraitSearch;
use app\models\Users;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PortraitController implements the CRUD actions for Portrait model.
 */
class PortraitController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    /**
     * Lists all Portrait models.
     * @return mixed
     */
    public function actionIndex()
    { 
        $searchModel = new PortraitSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Create a new Portrait model.
     * If creation is successful, the browser will be redirected to the 'portrait' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $count = 0;
        if ($count <= 1) {
            $id = $this->createUser();
            $model = new Portrait(['scenario' => Portrait::SCENARIO_CREATE]);
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'User has been successfully created.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            return $this->render('create', [
                'model' => $model,
                'id' => $id,
            ]); 
        }
        $count++;
    }

    /**
     * Register a Portrait.
     * If registration is successful, the browser will be redirected to the 'portrait' page.
     * @return mixed
     */
    public function actionRegister()
    {
        $count = 0;
        if ($count <= 1) {
            $id = $this->createUser();
            $model = new Portrait(['scenario' => Portrait::SCENARIO_REGISTER]);
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'User has been successfully created.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            return $this->render('register', [
                'model' => $model,
                'id' => $id,
            ]); 
        }
        $count++;      
    }

    /**
     * Updates an existing Portrait model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = Portrait::SCENARIO_UPDATE;
        $model->password = '';
        $user_portrait = Portrait::find()->where(['us_id' => Yii::$app->user->id])->one()['id'];

        if ($id == $user_portrait || Yii::$app->user->identity->is_admin === true) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Portrait has been successfully modified.');
                return $this->redirect(['portrait/index']); 
            }
    
            return $this->render('update', [
                'model' => $model,
            ]);
        }
        Yii::$app->session->setFlash('error', 'You can only update your own portrait.');
        return $this->redirect(['view', 'id' => $model->id]); 
    }

    /**
     * Displays a single Portrait model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model_portrait = $this->findModel($id);
        $model_user = Users::findOne(['id' => $model_portrait->us_id]);
        if ($model_user->is_deleted === true) {
            Yii::$app->session->setFlash('error', 'User has been deleted.');
            return $this->redirect(['/query/index']); 
        }
        if (Yii::$app->user->id !== null) {
            if (Portrait::find()->where(['us_id' => Yii::$app->user->id])->one() !== null) {
                $user_portrait = Portrait::find()->where(['us_id' => Yii::$app->user->id])->one()['id'];
            } else {
                $user_portrait = null;
            }
        } else {
            $user_portrait = null;
        }
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'user_portrait' => $user_portrait,
        ]);
    }

    /**
     * Deletes an existing Portrait model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $portrait_id = Portrait::find()->where(['us_id' => Yii::$app->user->id])->one()['id'];
        if ($id == $portrait_id || Yii::$app->user->identity->is_admin === true) {
            $model_user = Users::findOne(['id' => $portrait_id]);
            $model_user->is_deleted = true;
            Yii::$app->session->setFlash('success', 'User has been successfully deleted.');
            return $this->redirect(['/query/index']);
        }
        Yii::$app->session->setFlash('error', 'You can only delete your own portrait.');
        return $this->redirect(['view', 'id' => $id]); 
    }

    /**
     * Finds the Portrait model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Portrait the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Portrait::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Register a User.
     * If registration is successful, the browser will be return user id.
     * @return int
     */
    private function createUser() {
        $id = Users::find()->max('id');
        $id++;
        if ((Portrait::find()->max('us_id') + 1) === $id)  {
            $model_user = new Users(['id' => $id, 'is_deleted' => false]);
            $model_user->save();
            return $id;
        } else {
            $id--;
            $model_user = Users::findOne(['id' => $id]);
            $model_user->delete();
            return $id; 
        }  
    }
}
