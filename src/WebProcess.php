<?php

namespace yiiboot\workerman;

use yiiboot\workerman\web\Application;
use yiiboot\workerman\web\RequestHandler;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\Psr7\web\monitor\AbstractMonitor;

/**
 * 应用进程
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/8 22:06
 */
class WebProcess extends Process
{

    /**
     * 应用程序类型
     *
     * @var string
     */
    public string $applicationClass = Application::class;

    /**
     * 初始化应用的配置
     *
     * @var array
     */
    public array $config = [];

    /**
     * AbstractMonitor[]
     *
     * @var array
     */
    public array $monitors = [];

    /**
     * @throws InvalidConfigException
     */
    public function getHandler(): object|string|null
    {
        $this->handler = parent::getHandler();

        if ($this->handler == null) {

            $config = $this->getAppConfig();

            if (!empty($this->monitors)) {
                $config['monitors'] = [];
                foreach ($this->monitors as $monitor) {
                    if (!is_object($monitor)) {
                        $monitor = \Yii::createObject($monitor);
                    }
                    if ($monitor instanceof AbstractMonitor) {
                        $config['monitors'][] = $monitor;
                    } else {
                        throw new InvalidConfigException('monitor must instanceof ' . AbstractMonitor::class);
                    }
                }
            }

            // 实例化应用
            $application = \Yii::createObject([
                'class' => $this->applicationClass,
                '__construct()' => [
                    $config
                ]
            ]);

            if ($application instanceof Application) {
                if (isset($config['basePath'])) {
                    $application->basePath = $config['basePath'];
                }
                $this->handler = new RequestHandler($application);
            } else {
                throw new InvalidConfigException('process error: applicationClass must instanceof ' . Application::class);
            }
        }

        return $this->handler;
    }

    /**
     * 获取应用配置
     *
     * @return array
     */
    protected function getAppConfig(): array
    {
        return ArrayHelper::merge([
            'components' => [
                'request' => [
                    'class' => \yii\Psr7\web\Request::class,
                    'ipHeaders' => [
                        'X-Forwarded-For', // Common,
                        'REMOTE_ADDR'
                    ],
                    'portHeaders' => [
                        'X-Forwarded-Port', // Common
                        'REMOTE_PORT'
                    ]
                ],
                'response' => [
                    'class' => \yiiboot\workerman\web\Response::class,
                ],
                'errorHandler' => [
                    'class' => \yii\Psr7\web\ErrorHandler::class,
                ],
            ],
        ], $this->config);
    }
}