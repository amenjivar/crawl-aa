<?php

namespace app\controllers;

use app\models\form\CrawlForm;
use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $form = new CrawlForm();

        // populate the model with input data (if any)
        if ($form->load(Yii::$app->request->post())) {
            $results = $form->crawl();
        }

        return $this->render('index', [
            'model' => $form,
            'results' => $results ?? false,
        ]);
    }
}
