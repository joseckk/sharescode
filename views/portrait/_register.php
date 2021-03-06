<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Url;

$this->registerCssFile('@web/css/password-strength.css', ['position' => $this::POS_HEAD]);
$this->registerJsFile('@web/js/password-strength.js', ['position' => $this::POS_END]);

$urlNickname = Url::to(['portrait/looking-for-nickname-ajax']);
$urlEmail = Url::to(['portrait/looking-for-email-ajax']);
$urlRepository = Url::to(['portrait/looking-for-repository-ajax']);

$validation = <<<EOT
    $('#portrait-nickname').val('');
    $('#portrait-password_repeat').val('');
    $('#portrait-password').val('');
    $('#portrait-email').val('');
    $('#portrait-repository').val('');

    $('#portrait-nickname').blur(function (ev) {
        var nickname = $(this).val();

        $.ajax({
            type: 'GET',
            url: '$urlNickname',
            data: {
                nickname: nickname
            }
        })
        .done(function (data) {
            if (data.find) {
                $('#nickname').show();
                $('#nickname').html('Error: nickname is already in use');
                $('#nickname').addClass('text-danger');
            } else {
                $('#nickname').html(data.nickname);
                $('#nickname').hide();
            }
        });
    });

    $('#portrait-email').blur(function (ev) {
        var email = $(this).val();

        $.ajax({
            type: 'GET',
            url: '$urlEmail',
            data: {
                email: email
            }
        })
        .done(function (data) {
            if (data.find) {
                $('#email').show();
                $('#email').html('Error: email is already in use');
                $('#email').addClass('text-danger');
            } else {
                $('#email').html(data.email);
                $('#email').hide();
            }
        });
    });

    $('#portrait-repository').blur(function (ev) {
        var repository = $(this).val();

        $.ajax({
            type: 'GET',
            url: '$urlRepository',
            data: {
                repository: repository
            }
        })
        .done(function (data) {
            if (data.find) {
                $('#repository').show();
                $('#repository').html('Error: repository is already in use');
                $('#repository').addClass('text-danger');
            } else {
                $('#repository').html(data.repository);
                $('#repository').hide();
            }
        });
    });
EOT;
$this->registerJs($validation);

$checkPassword = <<<EOT
$('#portrait-password_repeat').keyup(function(event) {
    let container = $('#check-password');
    container.fadeIn('fast', function() {
        container.show();
    });

    var password = $('#portrait-password_repeat').val();
    checkPasswordStrength(password);
});
EOT;
$this->registerJs($checkPassword);

$sex = ['Men' => 'Men',
        'Woman' => 'Woman'];
?>
<div class="row justify-content-center">
    <div class="users-form form col-md-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="query/index">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Register</li>
            </ol>
        </nav>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'nickname')->textInput(['maxlength' => true]) ?>

        <?= Html::label('', '', [
            'id' => 'nickname',
        ]) ?>

        <?= $form->field($model, 'password_repeat')->passwordInput(['maxlength' => true])->label('Password') ?>

        <div id="check-password">
            <hr>
            <ul class="pswd_info" id="passwordCriterion">
                <li data-criterion="length" class="invalid">8-15 <strong>Characters</strong></li>
                <li data-criterion="capital" class="invalid">At least <strong>one capital letter</strong></li>
                <li data-criterion="small" class="invalid">At least <strong>one small letter</strong></li>
                <li data-criterion="number" class="invalid">At least <strong>one number</strong></li>
                <li data-criterion="special" class="invalid">At least <strong>one Specail Characters </strong></li>
            </ul>
            <div id="password-strength-status"></div>
            <hr>
        </div>

        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true])->label('Password Repeat')  ?>

        <?= $form->field($model, 'date_register')->hiddenInput(['value' => date('Y-m-d H:i:s')])->label(false) ?>

        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

        <?= Html::label('', '', [
            'id' => 'email',
        ]) ?>

        <?= $form->field($model, 'repository')->textInput(['maxlength' => true]) ?>

        <?= Html::label('', '', [
            'id' => 'repository',
        ]) ?>

        <?= $form->field($model, 'prestige_port')->hiddenInput(['maxlength' => true, 'value' => 'Initiate'])->label(false) ?>

        <?= $form->field($model, 'sex')->textInput(['maxlength' => true])->dropDownList($sex)  ?>

        <div class="form-group">
            <?= Html::submitButton('Register', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>