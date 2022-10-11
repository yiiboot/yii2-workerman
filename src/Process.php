<?php

namespace stack\workerman;

use Workerman\Worker;
use Yii;
use yii\base\BaseObject;

/**
 * workerman 进程
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/8 21:57
 */
class Process extends BaseObject implements ProcessInterface
{
    /**
     * 进程名称
     * @var string
     */
    public string $name = '';

    /**
     * 监听的协议 ip 及端口 （可选）
     *
     * @var string
     */
    public string $listen = '';

    /**
     * 进程数 （可选，默认1）
     *
     * @var int
     */
    public ?int $count = null;

    /**
     * 进程运行用户 （可选，默认当前用户）
     *
     * @var string
     */
    public string $user = '';

    /**
     * 进程运行用户组 （可选，默认当前用户组）
     *
     * @var string
     */
    public string $group = '';

    /**
     * 当前进程是否支持reload （可选，默认true）
     *
     * @var bool
     */
    public bool $reloadable = true;

    /**
     * 是否开启reusePort （可选，此选项需要php>=7.0，默认为true）
     *
     * @var bool
     */
    public bool $reusePort = false;

    /**
     * transport (可选，当需要开启ssl时设置为ssl，默认为tcp)
     *
     * @var string
     */
    public string $transport = 'tcp';

    /**
     * context （可选，当transport为是ssl时，需要传递证书路径）
     *
     * @var array
     */
    public array $context = [];

    /**
     * 进程处理器
     *
     * @var object|array|string|null
     */
    public object|array|string|null $handler = null;

    /**
     * @var ProcessInterface[]
     */
    public array $services = [];

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getListen(): string
    {
        return $this->listen;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount(): int
    {
        if ($this->count == null) {
            $this->count = YII_ENV_PROD ? $this->getCpuCount() : 1;
        }
        return $this->count;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * {@inheritDoc}
     */
    public function isReloadable(): bool
    {
        return $this->reloadable;
    }

    /**
     * {@inheritDoc}
     */
    public function isReusePort(): bool
    {
        return $this->reusePort;
    }

    /**
     * {@inheritDoc}
     */
    public function getTransport(): string
    {
        return $this->transport;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     */
    public function getHandler(): object|string|null
    {
        if (is_callable($this->handler)) {
            $this->handler = call_user_func($this->handler);
        } else if (is_array($this->handler) && isset($this->handler['class'])) {
            $this->handler = Yii::createObject($this->handler);
        } else if (is_string($this->handler) && class_exists($this->handler)) {
            $this->handler = Yii::createObject([
                'class' => $this->handler
            ]);
        }

        return $this->handler;
    }

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * 获取 CPU 核心数
     *
     * @return int
     */
    public function getCpuCount(): int
    {
        // Windows does not support the number of processes setting.
        if (\DIRECTORY_SEPARATOR === '\\') {
            return 1;
        }
        $count = 4;
        if (\is_callable('shell_exec')) {
            if (\strtolower(PHP_OS) === 'darwin') {
                $count = (int)\shell_exec('sysctl -n machdep.cpu.core_count');
            } else {
                $count = (int)\shell_exec('nproc');
            }
        }
        return $count > 0 ? $count : 4;
    }

    /**
     * {@inheritDoc}
     */
    public function start()
    {
        $worker = new Worker($this->listen ?? null, $this->context);

        $worker->name = $this->getName();
        $worker->count = $this->getCount();
        $worker->user = $this->getUser();
        $worker->group = $this->getGroup();
        $worker->reloadable = $this->isReloadable();
        $worker->reusePort = $this->isReusePort();
        $worker->transport = $this->getTransport();

        $worker->onWorkerStart = function ($worker) {
            foreach ($this->services as $server) {

                if (is_array($server)) {
                    if (!isset($server['class'])) {
                        $server['class'] = Process::class;
                    }
                    $server = \Yii::createObject($server);
                }

                if (!($server instanceof ProcessInterface)) {
                    continue;
                }

                $handler = $server->getHandler();

                if (is_string($handler) && !\class_exists($handler)) {
                    echo "process error: class {$handler} not exists\r\n";
                    continue;
                }

                $listen = new Worker($server->getListen() ?? null, $server->getContext());

                if ($server->getListen()) {
                    echo "listen: {$server->getListen()}\n";
                }

                $this->bind($listen, $handler);

                $listen->listen();
            }

            $handler = $this->getHandler();

            if ($handler) {
                $this->bind($worker, $handler);
            }
        };
    }

    protected function bind(Worker $worker, object|string $handler): void
    {
        $callback_map = [
            'onConnect',
            'onMessage',
            'onClose',
            'onError',
            'onBufferFull',
            'onBufferDrain',
            'onWorkerStop',
            'onWebSocketConnect'
        ];
        foreach ($callback_map as $name) {
            if (\method_exists($handler, $name)) {
                $worker->$name = [$handler, $name];
            }
        }
        // 触发 onWorkerStart 事件
        if (\method_exists($handler, 'onWorkerStart')) {
            \call_user_func([$handler, 'onWorkerStart'], $worker);
        }
    }
}