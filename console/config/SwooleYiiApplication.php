<?php

use yii\helpers\ArrayHelper;

class SwooleYiiApplication {
    public function __construct()
    {
        defined('YII_DEBUG') or define('YII_DEBUG', false);
        defined('YII_ENV') or define('YII_ENV', 'prod');
        defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
        defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
        $config = ArrayHelper::merge(
            require(__DIR__ . '/../common/config/main.php'),
            require(__DIR__ . '/../common/config/main-local.php'),
            require(__DIR__ . '/config/main.php'),
            require(__DIR__ . '/config/main-local.php')
        );
        $this->app = new \yii\console\Application($config);
    }
}
?>