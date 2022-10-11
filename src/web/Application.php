<?php

namespace stack\workerman\web;

use ReflectionMethod;
use stack\events\CreateControllerEvent;

/**
 * the psr7 web application
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/6 16:25
 */
class Application extends \yii\Psr7\web\Application
{
    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    protected function bootstrap()
    {
        // Call the bootstrap method in \yii\base\Application instead of \yii\web\Application
        $method = new ReflectionMethod(\yii\base\Application::class, 'bootstrap');
        // $method->setAccessible(true);
        $method->invoke($this);
    }

    /**
     * {@inheritDoc}
     */
    public function createController($route)
    {
        $event = new CreateControllerEvent($route);

        $this->trigger(CreateControllerEvent::EVENT_NAME, $event);

        return $event->controller ?: parent::createController($route);
    }
}