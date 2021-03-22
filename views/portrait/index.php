
<?php

use app\models\Portrait;
use yii\bootstrap4\Html;
use yii\widgets\DetailView;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PortraitSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mi perfil';
$this->params['breadcrumbs'][] = $this->title;
$d = date('Y-m-d H:m:s');
$date = Yii::$app->formatter->asDate($d); ?>
    <p>
        <?= Html::a('Editar perfil', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Eliminar perfil', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estas seguro de querer borrar su perfil?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <p>
        <?= $img = $model->devolverImg($model) ?>            
    </p>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
                'name_portrait',
                'last_name',
                [
                    'label' => 'Date register',
                    'value' => $date,
                ],
                'email:email',
                'repository:url',
                'prestige_port',
                'sex',
                [
                    'label' => 'User',
                    'value' => $nickname,
                ],
        ],
    ]); ?>
