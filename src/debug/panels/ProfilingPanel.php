<?php

namespace stack\workerman\debug\panels;

use Yii;
use yii\log\Logger;

/**
 * 修改 yii2-debug 模块，替换 YII_BEGIN_TIME
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/7 15:45
 */
class ProfilingPanel extends \yii\debug\panels\ProfilingPanel
{
    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $messages = $this->getLogMessages(Logger::LEVEL_PROFILE);
        return [
            'memory' => memory_get_peak_usage(),
            'time' => microtime(true) - Yii::$app->params['YII_BEGIN_TIME'] ?? YII_BEGIN_TIME,
            'messages' => $messages,
        ];
    }
}