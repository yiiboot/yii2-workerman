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
     * {@inheritDoc}
     */
    public function preInit(&$config)
    {
        if ($this->preInited) {
            $coreComponents = array_keys($this->coreComponents());

            foreach ($config['components'] as $name => $component) {
                if (!in_array($name, $coreComponents)) {
                    unset($config['components'][$name]);
                }
            }
        }

        parent::preInit($config);

        $this->preInited = true;
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
    protected function terminate(ResponseInterface $response): ResponseInterface
    {
        $response = parent::terminate($response);

        $this->module = null;
        $this->controller = null;
        $this->requestedRoute = null;

        $coreComponentNames = array_keys($this->coreComponents());

        foreach ($coreComponentNames as $id) {
            $this->clear($id);
        }

        return $response;
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