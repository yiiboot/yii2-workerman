<?php

namespace yiiboot\workerman\debug\panels;

use Yii;

/**
 * 修改 yii2-debug 模块，替换 YII_BEGIN_TIME
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/7 15:45
 */
class TimelinePanel extends \yii\debug\panels\TimelinePanel
{
    /**
     * {@inheritdoc}
     */
    public function save()
    {
        return [
            'start' => Yii::$app->params['YII_BEGIN_TIME'] ?? YII_BEGIN_TIME,
            'end' => microtime(true),
            'memory' => memory_get_peak_usage(),
        ];
    }
}