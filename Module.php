<?php

namespace fgh151\modules\backup;

class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{
   public $controllerNamespace = 'fgh151\modules\backup\controllers';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }


    public function registerTranslations()
    {
        \Yii::$app->i18n->translations['modules/backup/*'] = [
            'class' => '\yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'ru',
            'basePath' => '@app/modules/backup/messages',
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return \Yii::t('modules/backup/' . $category, $message, $params, $language);
    }

    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'fgh151\modules\backup\commands';
        }
    }
}
