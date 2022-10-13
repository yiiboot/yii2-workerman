<?php

namespace stack\workerman;

use stack\workerman\commands\WorkermanController;
use stack\workerman\debug\log\Logger;
use stack\workerman\debug\Module;
use Yii;
use yii\base\BootstrapInterface;
use yii\console\Application;

/**
 * Workerman Web Server Yii2 Extensions
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/6 16:07
 */
class WorkermanBootstrap implements BootstrapInterface
{
    /**
     * Debug 模块名称
     *
     * @var string
     */
    public string $debugModuleName = 'debug';

    /**
     * @param $app
     * @return void
     */
    public function bootstrap($app): void
    {
        // 记录开始时间
        $app->params['YII_BEGIN_TIME'] = microtime(true);

        if ($app instanceof Application) {
            // Activates the circular reference collector
            gc_enable();
            // 注入命令
            $app->controllerMap['workerman']['class'] = WorkermanController::class;
        }

        // 变更 debugger
        if ($app->modules[$this->debugModuleName]) {
            $app->setModule($this->debugModuleName, [
                'class' => Module::class,
                'basePath' => '@vendor/yiisoft/yii2-debug/src',
                'controllerPath' => '@vendor/yiisoft/yii2-debug/controllers',
                'controllerNamespace' => 'yii\debug\controllers',
            ]);
        }

        // 变更 Logger
        Yii::$container->set(\yii\log\Logger::class, Logger::class);
    }
}