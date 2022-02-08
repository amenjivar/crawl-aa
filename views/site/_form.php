<?php

use app\models\form\CrawlForm;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CrawlForm */
/* @var $form ActiveForm */

?>
<div class="crawl-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'address', [
        'inputOptions' => [
            'class' => 'form-control',
            'placeholder' => 'https://website.com',
        ],
    ])->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
