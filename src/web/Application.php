<?php

namespace stack\workerman\web;

use Psr\Http\Message\ResponseInterface;
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
    private array $monitors = [];

    private bool $preInited = false;

    public function __construct(array $config = [])
    {
        // 截留 monitors
        if (isset($config['monitors'])) {
            $this->monitors = $config['monitors'] ?: [];
            unset($config['monitors']);
        }
        parent::__construct($config);
    }

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
    public function monitors(): array
    {
        $monitors = parent::monitors();
        if (empty($this->monitors)) {
            return $monitors;
        }
        return [...$monitors, ...$this->monitors];
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