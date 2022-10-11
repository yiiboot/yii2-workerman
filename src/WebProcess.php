<?php

namespace stack\workerman;

use stack\workerman\web\Application;
use stack\workerman\web\RequestHandler;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * 应用进程
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/8 22:06
 */
class WebProcess extends Process
{
    /**
     * 初始化应用的配置
     *
     * @var array
     */
    public array $config = [];

    /**
     * 应用程序类型
     *
     * @var string
     */
    public string $applicationClass = Application::class;

    /**
     * @throws InvalidConfigException
     */
    public function getHandler(): object|string|null
    {
        $this->handler = parent::getHandler();

        if ($this->handler == null) {

            $config = $this->getAppConfig();

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
                throw new InvalidConfigException('process error: applicationClass instanceof ' . Application::class);
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
                    'class' => \stack\workerman\web\Response::class,
                ],
                'errorHandler' => [
                    'class' => \yii\Psr7\web\ErrorHandler::class,
                ],
            ],
        ], $this->config);
    }
}