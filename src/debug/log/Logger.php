<?php


namespace yiiboot\workerman\debug\log;

/**
 * 日志记录器 - 替换 YII_BEGIN_TIME
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/8 20:58
 */
class Logger extends \yii\log\Logger
{
    /**
     * Returns the total elapsed time since the start of the current request.
     * This method calculates the difference between now and the timestamp
     * defined by constant `YII_BEGIN_TIME` which is evaluated at the beginning
     * of [[\yii\BaseYii]] class file.
     * @return float the total elapsed time in seconds for current request.
     */
    public function getElapsedTime()
    {
        return microtime(true) - \Yii::$app->params['YII_BEGIN_TIME'] ?? YII_BEGIN_TIME;
    }
}
