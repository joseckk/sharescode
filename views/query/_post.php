<?php

use app\models\Answer;
use app\models\Portrait;
use app\models\Query;
use yii\helpers\Html;
use yii\helpers\Url;

$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js', ['position' => $this::POS_END]);
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', ['position' => $this::POS_END]);
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css', ['position' => $this::POS_HEAD]);

$urlPortrait = Url::to(['portrait/view', 'id' => $model->users_id]);
$username = Query::findUserName($model->id);
$img = Query::findUserImage($model->id);
$answers_list = $model->answers;

if (Yii::$app->user->id !== null) {
    $user_actually_id = Yii::$app->user->id;
    $model_portrait = Portrait::findOne(['id' => $user_actually_id]);
    $model_portrait->sex === 'Men'
    ? $url = '@web/img/men.svg'
    : $url = '@web/img/woman.svg';
    $img_response = Html::img($url, ['class'=> 'img-answer']);
} else {
    $user_actually_id = null;
}

$url_create = Url::to(['answer/create', 'id' => $model->id]);
$url_delete = Url::to(['answer/delete']);
$url_update = Url::to(['answer/update']);
$createAnswer = <<<EOT
    $('#content-$model->id').keydown(function (ev) {
        if (ev.keyCode == 13) { 
            ev.preventDefault();
            var content = ev.target.value;
            $.ajax({
                type: 'POST',
                url: '$url_create',
                data: {
                    content: content,
                }
            })
            .done(function (data) {
                let newAnswer = $(data.response);
                newAnswer.hide();
                $('#answers-$model->id').append(newAnswer);
                newAnswer.fadeIn('fast');
                $('#content-$model->id').val('');
                $('#modals-$model->id').append(data.modal);
                console.log($('#modals-$model->id'));

                let deleteButton = $('#delete-' + data.answer_id);
                let updateButton = $('#update-' + data.answer_id);

                deleteButton.click(function (ev) {
                    ev.preventDefault();
                    var id = data.answer_id;

                    $.ajax({
                        type: 'POST',
                        url: '$url_delete',
                        data: {
                            id: id,
                        }
                    })
                    .done(function (data) {
                        let container = deleteButton.parent().parent();
                        container.fadeOut('fast', function() {
                            container.remove();
                        });
                        
                        $('li.dropdown').remove();
                        $('#notifications').append(data.reminders);
                    });
                    return false;
                })

                updateButton.click(function (ev) {
                    ev.preventDefault();
                    let id = data.answer_id;
                    
                    $('#con-'+id).keydown(function (ev) {
                        console.log(2);
                        if (ev.keyCode == 13) {
                            ev.preventDefault();
                            var content = ev.target.value;
        
                            $.ajax({
                                type: 'POST',
                                url: '$url_update',
                                data: {
                                    id: id,
                                    content: content,
                                }
                            })
                            .done(function (data) {
                                let container = $('.current');
                                container.fadeOut('fast', function() {
                                    container.remove();
                                });
        
                                let answer_id = data.answer_id;
                                let oldAnswer = $('#update-'+answer_id).parent().parent();
                                oldAnswer.fadeOut('fast', function() {
                                    oldAnswer.hide();
                                });
        
                                let newAnswer = $(data.response);
                                
                                let father_id = $('#update-'+answer_id).parent().parent().parent().attr("id");
                                $('#'+father_id).append(newAnswer);
                                newAnswer.fadeIn('fast');
        
                                $('li.dropdown').remove();
                                $('#notifications').append(data.reminders);
                            })
                            return false;
                        }
                    });
                })
                $('li.dropdown').remove();
                $('#notifications').append(data.reminders);
            });
            return false;
        }
    });
EOT;

$deleteAnswer = <<<EOT
    var list = [];
    $(".delete").each(function(index) {
        list.push($(this).attr("id"));
    });

    $.each(list, function (ind, elem) {
        $('#'+elem).click(function (ev) {
            ev.preventDefault();
            var id = elem.substring(7);

            $.ajax({
                type: 'POST',
                url: '$url_delete',
                data: {
                    id: id,
                }
            })
            .done(function (data) {
                let container = $('#'+elem).parent().parent();
                container.fadeOut('fast', function() {
                    container.remove();
                });

                $('li.dropdown').remove();
                $('#notifications').append(data.reminders);
            });
            return false;
        });
    });
EOT;

$updateAnswer = <<<EOT
    var list = [];
    $(".update").each(function(index) {
        list.push($(this).attr("id"));
    });

    $.each(list, function (ind, elem) {
        $('#'+elem).click(function (ev) {
            ev.preventDefault();
            let id = elem.substring(7);
            
            $('#con-'+id).keydown(function (ev) {
                if (ev.keyCode == 13) {
                    ev.preventDefault();
                    var content = ev.target.value;

                    $.ajax({
                        type: 'POST',
                        url: '$url_update',
                        data: {
                            id: id,
                            content: content,
                        }
                    })
                    .done(function (data) {
                        let container = $('.current');
                        container.fadeOut('fast', function() {
                            container.remove();
                        });

                        let answer_id = data.answer_id;
                        let oldAnswer = $('#update-'+answer_id).parent().parent();
                        oldAnswer.fadeOut('fast', function() {
                            oldAnswer.hide();
                        });

                        let newAnswer = $(data.response);
                        
                        let father_id = $('#update-'+answer_id).parent().parent().parent().attr("id");
                        $('#'+father_id).append(newAnswer);
                        newAnswer.fadeIn('fast');

                        $('#modals-'+answer_id).append(data.modal);
                        console.log($('#modals-'+answer_id));

                        let deleteButton = $('#delete-' + data.answer_id);
                        console.log(deleteButton);
                        
                        deleteButton.click(function (ev) {
                            
                            var id = data.answer_id;
                            console.log(1);
                            $.ajax({
                                type: 'POST',
                                url: '$url_delete',
                                data: {
                                    id: id,
                                }
                            })
                            .done(function (data) {
                                console.log(2);
                                let container = deleteButton.parent().parent();
                                container.fadeOut('fast', function() {
                                    container.remove();
                                });
                                
                                $('li.dropdown').remove();
                                $('#notifications').append(data.reminders);
                            });
                            return false;
                        })

                        $('li.dropdown').remove();
                        $('#notifications').append(data.reminders);
                    })
                    return false;
                }
            });
        });
    });
    
EOT;
if (!Yii::$app->user->isGuest) {
    $this->registerJs($createAnswer);
    $this->registerJs($deleteAnswer);
    $this->registerJs($updateAnswer);
}
?>
<div class="row justify-content-center mt-5">
    <div class="col-md-9 card card-widget">
        <div class="card-header">
            <div class="user-block">
                <div class="img-circle" alt="User Image">
                    <?= $img ?>
                </div>
                    <span class="username"><a href=<?= $urlPortrait ?>><?= Html::encode($username) ?></a></span>
                    <span class="description">Created ago - <?= $model->date_created ?> </span>
            </div>
        <!-- /.user-block -->
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
            <span class="text-primary"><?= $model->title ?></span>
        </div>
        <!-- /.card-tools -->
        </div>
              
        <!-- /.card-header -->
        <div class="card-body">
            <!-- post text -->
            <h3 class="text-success"><?= $model->title ?></h3>
            <!-- Attachment -->
            <div class="attachment-block clearfix">
                <div class="attachment-text">
                    <p><?= $model->explanation ?></p>
                </div>
            </div>
            <!-- /.attachment-block -->
        </div>
            <div id="answers-<?= $model->id ?>">
                <?php foreach ($answers_list as $answer): ?>
                    <!-- /.card-body -->
                    <div class="card-footer card-comments">
                        <div class="card-comment">
                            <!-- User image -->
                            <div class="img-circle" alt="User Image">
                                <?= Answer::findUserImage($answer->users_id) ?>
                            </div>

                            <div class="comment-text">
                                <span class="username">
                                    <a href=<?= Answer::findUserPortrait($answer->users_id) ?>><?= Answer::findUserName($answer->users_id) ?></a>
                                <span class="text-muted float-right"><?= $answer->date_created ?></span>
                                </span><!-- /.username -->
                                <?= $answer->content ?>
                            </div>
                            <hr>
                            <?php if ($answer->users_id === Yii::$app->user->id): ?>
                                <!-- Delete or update answer -->
                                <button type="button" id="delete-<?= $answer->id ?>" class="btn btn-danger btn-sm delete"><i class="fas fa-minus-circle"></i> Delete</button>
                                <a href="#ex-<?= $answer->id ?>" id="update-<?= $answer->id ?>" class="btn btn-primary btn-sm update" rel="modal:open"><i class="far fa-edit"></i> Update</a>
                            <?php endif ?>
                            <!-- Social sharing buttons -->
                            <button type="button" class="btn btn-success btn-sm"><i class="fas fa-share"></i> Share</button>
                            <button type="button" class="btn btn-default btn-sm"><i class="far fa-thumbs-up"></i> Like</button>
                            <span class="float-right text-muted">45 likes - 2 comments</span>
                            <!-- /.comment-text -->
                        </div>
                        <!-- /.card-comment -->
                    </div>
                    <div id="modals-<?= $answer->id ?>">
                        <?php if ($user_actually_id): ?>
                            <div id="ex-<?= $answer->id ?>" class="modal">
                                <!-- /.card-footer -->
                                <div class="card-footer mb-3">
                                        <!-- User image -->
                                        <div class="img-fluid img-circle img-sm">
                                            <?= $img_response ?>
                                        </div>
                                        <!-- .img-push is used to add margin to elements next to floating images -->
                                        <div class="img-push">
                                            <input type="text" id="con-<?= $answer->id ?>" class="form-control form-control-sm" placeholder="Press enter to post comment">
                                        </div>
                                </div>
                                <!-- /.card-footer -->
                            </div>
                        <?php endif ?>
                    </div>
                <?php endforeach ?>
            </div>
        <?php if ($user_actually_id): ?>
            <!-- /.card-footer -->
            <div class="card-footer mb-3">
                <form action=<?= $url_create ?> method="post">
                    <!-- User image -->
                    <div class="img-fluid img-circle img-sm">
                        <?= $img_response ?>
                    </div>
                    <!-- .img-push is used to add margin to elements next to floating images -->
                    <div class="img-push">
                        <input type="text" id="content-<?= $model->id ?>" class="form-control form-control-sm" placeholder="Press enter to post comment">
                    </div>
                </form>
            </div>
            <!-- /.card-footer -->
        <?php endif ?>
    </div>
</div>
