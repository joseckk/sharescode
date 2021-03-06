<?php

use hail812\adminlte3\yii\grid\ActionColumn as GridActionColumn;
use yii\bootstrap4\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row justify-content-center">
    <div class="users-index form col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="query/index">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Users List</li>
            </ol>
        </nav>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                'is_deleted:boolean',
                [
                    'class' => GridActionColumn::class,
                    'template' => '{view} {update} {delete}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            $urlPortrait = Url::toRoute(['portrait/view', 'id' => $key]);
                            return Html::a('', $urlPortrait, ['class' => 'fas fa-eye btn-sm btn-success', 'id' => 'view']);
                        },
                        'update' => function ($url, $model, $key) {
                            $urlPortrait = Url::toRoute(['portrait/update', 'id' => $key]);
                            return Html::a('', $urlPortrait, ['class' => 'fas fa-user-edit btn-sm btn-primary', 'id' => 'update']);
                        },
                        'delete' => function ($url, $model, $key) {
                            $urlPortrait = Url::toRoute(['portrait/delete', 'id' => $key]);
                                return Html::a('', $urlPortrait, [
                                    'class' => 'fas fa-trash-alt btn-sm btn-danger',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to delete this user?',
                                        'method' => 'post',
                                    ],
                                ]);
                        },
                    ],
                ],
            ],
        ]); ?>


    </div>
</div>