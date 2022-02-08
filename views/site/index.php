<?php

use app\models\form\CrawlForm;
use yii\web\View;

/* @var $this View */
/* @var $model CrawlForm */
/* @var $results array|boolean */

$this->title = Yii::$app->name;

?>
<div class="site-index">
    <div class="jumbotron text-center bg-transparent">
        <h1 class="display-4">Web Crawler</h1>
        <?php
        echo $this->render('_form', [
            'model' => $model,
        ]); ?>
    </div>
    <div class="body-content">
        <?php
        if (is_array($results)) :
            echo $this->render('_results.php', [
                'results' => $results,
            ]);
        endif; ?>
    </div>
</div>
