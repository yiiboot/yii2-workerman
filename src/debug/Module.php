<?php

namespace yiiboot\workerman\debug;

use yiiboot\workerman\debug\panels\ProfilingPanel;
use yiiboot\workerman\debug\panels\TimelinePanel;
use yii\helpers\Url;

/**
 * 修改 yii2-debug 模块，替换 YII_BEGIN_TIME
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/7 15:45
 */
class Module extends \yii\debug\Module
{
    /**
     * {@inheritdoc}
     */
    public function setDebugHeaders($event)
    {
        if (!$this->checkAccess()) {
            return;
        }
        $url = Url::toRoute([
            '/' . $this->getUniqueId() . '/default/view',
            'tag' => $this->logTarget->tag,
        ]);
        $event->sender->getHeaders()
            ->set('X-Debug-Tag', $this->logTarget->tag)
            ->set('X-Debug-Duration', number_format((microtime(true) - (\Yii::$app->params['YII_BEGIN_TIME'] ?? YII_BEGIN_TIME)) * 1000 + 1))
            ->set('X-Debug-Link', $url);
    }

    /**
     * {@inheritdoc}
     */
    protected function corePanels(): array
    {
        $panels = parent::corePanels();
        $panels['profiling'] = ['class' => ProfilingPanel::class];
        $panels['timeline'] = ['class' => TimelinePanel::class];
        return $panels;
    }
}