<?php
namespace app\models\form;

use Yii;
use yii\base\Model;

/**
 * CrawlForm is the model class to validate URLs to be crawled
 */
class CrawlForm extends Model
{
    /**
     * @var string The URL to be crawled
     */
    public string $address = '';

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['address'], 'required'],
            [['address'], 'filter', 'filter' => 'trim'],
            [['address'], 'url', 'defaultScheme' => 'https'],
        ];
    }

    /**
     * Returns the results from crawling the address
     * @return bool|array
     */
    public function crawl()
    {
        if (! $this->validate() || empty(Yii::$app->crawler)) {
            return false;
        }

        return Yii::$app->crawler->run($this->address);
    }
}
