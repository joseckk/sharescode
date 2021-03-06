<?php

namespace app\controllers;

use Yii;
use app\models\Answer;
use app\models\AnswerSearch;
use app\models\Dislikes;
use app\models\Likes;
use app\models\Portrait;
use app\models\Prestige;
use app\models\Query;
use app\models\Reminder;
use app\models\TypePrestige;
use app\models\Users;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;

/**
 * AnswerController implements the CRUD actions for Answer model.
 */
class AnswerController extends Controller
{
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
                'only' => ['create', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $user_id = Yii::$app->user->id;
                            $portrait = $this->findPortrait($user_id);
                            if ($portrait !== null) {
                                $portrait_id = $portrait->id;
                            } else {
                                $portrait_id = null;
                                return $this->redirect(['portrait/create', 'id' => $user_id]);
                            }
                            return $portrait_id !== null;
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Answer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AnswerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Answer model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Answer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        if (Yii::$app->request->isAjax) {
            $users_id = Yii::$app->user->id;
            $model_portrait = $this->findPortrait($users_id);
            $username = $model_portrait->nickname;
            $img = Portrait::devolverImg($model_portrait);
            $model_portrait->sex === 'Men'
            ? $url = '@web/img/men.svg'
            : $url = '@web/img/woman.svg';
            $img_response = Html::img($url, ['class'=> 'img-answer']);
            $urlPortrait = Url::toRoute(['portrait/view', 'id' => $users_id]);
            $date_created = date('Y-m-d H:i:s');
            $content = Yii::$app->request->post('content');
            $content = $this->changeToHtml($content);
            $sending_user_id = Query::findOne(['id' => $id])['users_id'];

            $model = new Answer([
                'content' => $content,
                'date_created' => $date_created,
                'likes' => 0,
                'dislikes' => 0,
                'query_id' => $id,
                'users_id' => $users_id,
            ]);

            if ($model->save()) {
                $this->createReminder($id, $sending_user_id);
            }

            $answer_id = $model->id;
            $likes = $model->likes . ' likes';
            $dislikes = $model->dislikes . ' dislikes';

            if ($model->likes === null) {
                $likes = 0 . ' likes';
            }

            if ($model->dislikes === null) {
                $dislikes = 0 . ' dislikes';
            }

            $deleteButton = '<button type="button" id="delete-' . $answer_id . '" class="btn btn-danger btn-sm delete">' .
                                '<i class="fas fa-minus-circle">' .
                                '</i>' .
                                ' Delete' .
                            '</button>';

            $updateButton = '<button type="button" id="update-' . $answer_id . '" class="btn btn-primary btn-sm update"
                                data-toggle="modal" data-target="#ex-' . $answer_id . '">' .
                                '<i class="far fa-edit">' .
                                '</i>' .
                                ' Update' .
                            '</button>';

            return $this->asJson([
                'response' => $this->builderResponse($img, $urlPortrait, $username, Query::formatDate($date_created), $content, $deleteButton, $updateButton, '', '', $likes, $dislikes, $answer_id),
                'answer_id' => $answer_id,
                'reminders' => $this->builderReminders(),
                'modal' => $this->builderModal($answer_id, $img_response),
            ]);
        }
    }

    /**
     * Updates an existing Answer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $model = $this->findModel($id);
            $model->content = $this->changeToTextPlain($model->content);
            $users_id = Yii::$app->user->id;
            $model_portrait = $this->findPortrait($users_id);
            $username = $model_portrait->nickname;
            $img = Portrait::devolverImg($model_portrait);
            $urlPortrait = Url::toRoute(['portrait/view', 'id' => $users_id]);
            $date_created = date('Y-m-d H:i:s');
            $content = Yii::$app->request->post('content');
            $content = $this->changeToHtml($content);
            
            $model->content = $content;
            $model->date_created = $date_created;
            $model->likes = 0;
            $likes = 0 . ' likes';
            $model->dislikes = 0;
            $dislikes = 0 . ' dislikes';

            $model->save();

            $answer_id = $model->id;
            
            $votes = Likes::find()->where([
                'answer_id' => $answer_id,
            ])->all();

            foreach ($votes as $vote) {
                $vote->delete();
            }

            $deleteButton = '<button type="button" id="delete-' . $answer_id . '" class="btn btn-danger btn-sm delete">' .
                                '<i class="fas fa-minus-circle">' .
                                '</i>' .
                                ' Delete' .
                            '</button>';

            $updateButton = '<button type="button" id="update-' . $answer_id . '" class="btn btn-primary btn-sm update"
                                data-toggle="modal" data-target="#ex-' . $answer_id . '">' .
                                '<i class="far fa-edit">' .
                                '</i>' .
                                ' Update' .
                            '</button>';

            return $this->asJson([
                'response' => $this->builderResponse($img, $urlPortrait, $username, Query::formatDate($date_created), $content, $deleteButton, $updateButton, '', '', $likes, $dislikes, $answer_id),
                'answer_id' => $answer_id,
                'reminders' => $this->builderReminders(),
            ]);
        }
    }

    /**
     * Deletes an existing Answer model and Reminder model.
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $users_id = Yii::$app->user->id;
            if ($this->findOwnAnswer($id, $users_id) || Yii::$app->user->identity->is_admin === true) {
                $this->findModel($id)->delete();

                return $this->asJson([
                    'reminders' => $this->builderReminders(),
                ]);
            }
            Users::builderAlert('error', 'Error', 'You can only delete your own answer.');
        }
    }

    /**
     * Vote an existing Answer model.
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionLike()
    {
        if (Yii::$app->request->isAjax) {
            $answer_id = Yii::$app->request->post('id');
            $users_id = Yii::$app->user->id;
            $nickname = Portrait::findOne(['id' => $users_id])['nickname'];
            $model_likes = new Likes([
                'nickname' => $nickname,
                'answer_id' => $answer_id,
                'users_id' => $users_id,
            ]);
            $model_likes->save();

            $model_dislikes = Dislikes::findOne([
                'nickname' => $nickname,
                'answer_id' => $answer_id,
                'users_id' => $users_id,
            ]);
            if ($model_dislikes !== null) {
                $model_dislikes->delete();
            }
    
            $model_answer = $this->findModel($answer_id);
            $model_answer->likes += 1;
            if ($model_answer->dislikes !== 0) {
                $model_answer->dislikes -= 1;
            }
            $model_answer->save();

            $model_prestige = Prestige::findOne(['users_id' => $model_answer->users_id]);
            $model_prestige->puntuation += 1;

            if (TypePrestige::find()
                        ->where(['=', 'score', $model_prestige->puntuation])->one()) {
                $prestige = TypePrestige::find()
                                ->where(['=', 'score', $model_prestige->puntuation])->one()['prestige'];
            } else {
                $prestige = $model_prestige->title;
            }

            $model_prestige->title = $prestige;
            $model_prestige->save();

            $model_portrait = Portrait::findOne(['id' => $model_answer->users_id]);
            $model_portrait->prestige_port = $prestige;
            $model_portrait->save();

            $img = Portrait::devolverImg($model_portrait);
            $urlPortrait = Url::toRoute(['portrait/view', 'id' => $model_portrait->id]);
            $username = $model_portrait->nickname;
            $date_created = $model_answer->date_created;
            $content = $model_answer->content;
            $likes = $model_answer->likes . ' likes';
            $dislikes = $model_answer->dislikes . ' dislikes';

            $likeButton = '<button type="button" id="like-' . $answer_id . '" class="btn btn-success btn-sm liked">' .
                              '<i class="far fa-thumbs-up">' .
                              '</i>' .
                              ' Like' .
                          '</button>';

            $dislikeButton = '<button type="button" id="dislike-' . $answer_id . '" class="btn btn-default btn-sm dislike">' .
                                '<i class="far fa-thumbs-down">' .
                                '</i>' .
                                ' Dislike' .
                             '</button>';

            return $this->asJson([
                'response' => $this->builderResponse($img, $urlPortrait, $username, Query::formatDate($date_created), $content, '', '', $likeButton, $dislikeButton, $likes, $dislikes, $answer_id),
                'answer_id' => $answer_id,
            ]);
        }
    }

    /**
     * Vote an existing Answer model.
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDislike()
    {
        if (Yii::$app->request->isAjax) {
            $answer_id = Yii::$app->request->post('id');
            $users_id = Yii::$app->user->id;
            $nickname = Portrait::findOne(['id' => $users_id])['nickname'];
            $model_dislikes = new Dislikes([
                'nickname' => $nickname,
                'answer_id' => $answer_id,
                'users_id' => $users_id,
            ]);
            $model_dislikes->save();

            $model_likes = Likes::findOne([
                'nickname' => $nickname,
                'answer_id' => $answer_id,
                'users_id' => $users_id,
            ]);
            if ($model_likes !== null) {
                $model_likes->delete();
            }
            
            $model_answer = $this->findModel($answer_id);
            $model_answer->dislikes += 1;
            if ($model_answer->likes !== 0) {
                $model_answer->likes -= 1;
            }
            $model_answer->save();

            $model_portrait = Portrait::findOne(['id' => $model_answer->users_id]);

            $img = Portrait::devolverImg($model_portrait);
            $urlPortrait = Url::toRoute(['portrait/view', 'id' => $model_portrait->id]);
            $username = $model_portrait->nickname;
            $date_created = $model_answer->date_created;
            $content = $model_answer->content;
            $likes = $model_answer->likes . ' likes';
            $dislikes = $model_answer->dislikes . ' dislikes';

            $likeButton = '<button type="button" id="like-' . $answer_id . '" class="btn btn-default btn-sm like">' .
                              '<i class="far fa-thumbs-up">' .
                              '</i>' .
                              ' Like' .
                          '</button>';

            $dislikeButton = '<button type="button" id="dislike-' . $answer_id . '" class="btn btn-danger btn-sm disliked">' .
                                '<i class="far fa-thumbs-down">' .
                                '</i>' .
                                ' Dislike' .
                             '</button>';

            return $this->asJson([
                'response' => $this->builderResponse($img, $urlPortrait, $username, Query::formatDate($date_created), $content, '', '', $likeButton, $dislikeButton, $likes, $dislikes, $answer_id),
                'answer_id' => $answer_id,
            ]);
        }
    }

    /**
     * Finds the Answer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Answer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Answer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Finds the Portrait model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Portrait the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findPortrait($id)
    {
        if (($model = Portrait::find()
                        ->where(['id' => $id])
                        ->one()) !== null
            ) {
            return $model;
        }
        return null;
    }

    /**
     * Finds the Answer model based on its primary key value and users_id.
     * If the model is not found, a null.
     * @param integer $id && $users_id
     * @return mixed Answer || null
     */
    protected function findOwnAnswer($id, $users_id)
    {
        if (($model = Answer::find()
                        ->where([
                            'id' => $id,
                            'users_id' => $users_id,
                      ])->one()) !== null
            ) {
            return $model;
        }
        return null;
    }

    /**
     *  A reminder will be created when creating or
     * modifying an answer referring to a query
     * @param integer $query_id && $users_id
     */
    protected function createReminder($query_id, $users_id)
    {
        $name_query = Query::findOne(['id' => $query_id])['title'];
        $date_created = date('Y-m-d H:i:s');
        $reminder = new Reminder(['title' => 'Se ha respondido a una de tus consultas',
                                  'dispatch' => "Se ha respondido a la consulta $name_query",
                                  'date_created' => $date_created,
                                  'users_id' => $users_id]);
        $reminder->save();
    }

    /**
     * Finds the Reminder model based on its primary key and users_id.
     * If the model is not found, a null.
     * @param integer $id && $users_id
     * @return mixed Reminder || null
     */
    protected function findReminder($id)
    {
        if (($model = Reminder::find()
                        ->where([
                            'id' => $id,
                      ])->one()) !== null
            ) {
            return $model;
        }
        return null;
    }

    /**
     *  Create the response as html container
     * to integrate it into the view
     */
    public function builderResponse($img, $urlPortrait, $username, $date_created, $content, $deleteButton, $updateButton, $likeButton, $dislikeButton, $likes, $dislikes, $id)
    {
        if (Yii::$app->user->isGuest) {
            return '';
        } else {
            return
            '<div id="container-answer-' . $id . '" class="card-footer card-comments">' .
                    '<div class="card-comment">' .
                        '<div class="img-circle" alt="User Image">' .
                            $img .
                        '</div>' .
                        '<div class="comment-text">' .
                            '<span class="username">' .
                                '<a href=' . $urlPortrait . '>' .
                                    $username .
                                '</a>' .
                                '<span class="text-muted float-right">' .
                                    $date_created .
                                '</span>' .
                            '</span>' .
                            $content .
                        '</div>' .
                        '<hr>' .
                        $deleteButton .
                        ' ' .
                        $updateButton .
                        ' ' .
                        $likeButton .
                        ' ' .
                        $dislikeButton .
                        ' ' .
                        '<span class="float-right text-muted">' .
                            $likes .
                            ' - ' .
                            $dislikes .
                        '</span>' .
                    '</div>' .
            '</div>' .
            '<div id="modals-' . $id . '">' .
            '</div>';
        }
    }

    /**
     *  Create the reminders as html container
     * to integrate it into the view
     */
    public function builderReminders()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        } else {
            $notifications_no_read = Users::countReminders();
            $notifications_time = Users::timeReminders();
            $urlReminder = Url::to(['reminder/index']);
            return
                '<li class="nav-item dropdown">' .
                    '<a class="nav-link" data-toggle="dropdown" href="#">' .
                        '<i class="far fa-bell"></i>' .
                        '<span class="badge badge-warning navbar-badge">' .
                            $notifications_no_read .
                        '</span>' .
                    '</a>' .
                    '<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">' .
                        '<span class="dropdown-header">' .
                            $notifications_no_read . ' Notifications' .
                        '</span>' .
                        '<div class="dropdown-divider"></div>' .
                        '<a href=' . $urlReminder . ' class="dropdown-item">' .
                            '<i class="fas fa-envelope mr-2"></i>' .
                                $notifications_no_read . ' new answers' .
                            '<span class="float-right text-muted text-sm">' .
                                $notifications_time .
                            '</span>' .
                        '</a>' .
                    '</div>' .
                '</li>';
        }
    }

    /**
     *  Create the modal window as html container
     * to integrate it into the view
     */
    public function builderModal($answer_id, $img_response)
    {
        if (Yii::$app->user->isGuest) {
            return '';
        } else {
            return
            '<div class="modal fade" id="ex-' . $answer_id . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">' .
                '<div class="modal-dialog" role="document">' .
                    '<div class="modal-content">' .
                        '<div class="modal-header">' .
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' .
                            '<span aria-hidden="true">&times;</span>' .
                            '</button>' .
                        '</div>' .
                        '<div class="modal-body">' .
                            '<div class="card-footer mb-3">' .
                                '<form action="" method="post">' .
                                    '<div class="mb-5 ml-5 form-group">' .
                                        '<label for="select-' . $answer_id . '">' .
                                            'Select a theme: ' .
                                        '</label>' .
                                        '<select class="form-control" name="theme" id="select-modal-' . $answer_id . '">' .
                                            '<option value="1">' .
                                                'abbott' .
                                            '</option>' .
                                            '<option value="2">' .
                                                'ambiance' .
                                            '</option>' .
                                            '<option value="3">' .
                                                'cobalt' .
                                            '</option>' .
                                            '<option value="4">' .
                                                'dracula' .
                                            '</option>' .
                                            '<option value="5">' .
                                                'eclipse' .
                                            '</option>' .
                                            '<option value="6">' .
                                                'elegant' .
                                            '</option>' .
                                            '<option value="7">' .
                                                'isotope' .
                                            '</option>' .
                                            '<option value="8">' .
                                                'lucario' .
                                            '</option>' .
                                            '<option value="9">' .
                                                'material' .
                                            '</option>' .
                                            '<option value="10">' .
                                                'neo' .
                                            '</option>' .
                                            '<option value="11">' .
                                                'night' .
                                            '</option>' .
                                            '<option value="12">' .
                                                'oceanic-next' .
                                            '</option>' .
                                            '<option value="13">' .
                                                'the-matrix' .
                                            '</option>' .
                                            '<option value="14">' .
                                                'abbott' .
                                            '</option>' .
                                        '</select>' .
                                    '</div>' .
                                '</form>' .
                                '<div class="img-fluid img-circle img-sm">' .
                                    $img_response .
                                '</div>' .
                            '<form action="" method="post" id="form-codemirror-modal-' . $answer_id . '">' .
                                '<div class="ml-5 img-push">' .
                                    '<textarea id="codemirror-modal-' . $answer_id . '">' .
                                    '</textarea>' .
                                '</div>' .
                                '<button type="submit" name="preview-form-submit-modal" id="preview-form-submit-modal-' . $answer_id . '" 
                                         value="Submit" class="mt-3 float-right btn btn-success">' .
                                         'Send reply' .
                                '</button>' .
                            '</form>' .
                        '</div>' .
                    '</div>' .
                '</div>' .
            '</div>';
        }
    }
    
    /**
     * Change plain text to Html code
     *
     * @return string
     */
    public function changeToHtml($content)
    {
        $content = str_replace(' ', '&nbsp', $content);
        $content = str_replace('>', '&gt', $content);
        $content = str_replace('<', '&lt', $content);
        $content = str_replace("\n", '<br>', $content);
        return $content;
    }

    /**
     * Change Html code to plain text
     *
     * @return string
     */
    public function changeToTextPlain($content)
    {
        $content = str_replace('<br>', "\n", $content);
        $content = str_replace('&nbsp', ' ', $content);
        $content = str_replace('&gt', '>', $content);
        $content = str_replace('&lt', '<', $content);
        return $content;
    }
}
