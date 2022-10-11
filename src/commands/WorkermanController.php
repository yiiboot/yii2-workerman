<?php

namespace stack\workerman\commands;

use stack\workerman\WebProcess;
use stack\workerman\Process;
use stack\workerman\ProcessInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;

/**
 * Workerman Commands
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/6 16:15
 */
class WorkermanController extends Controller
{
    /**
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        Worker::$onMasterReload = function () {
            if (function_exists('opcache_get_status')) {
                if ($status = \opcache_get_status()) {
                    if (isset($status['scripts']) && $scripts = $status['scripts']) {
                        foreach (array_keys($scripts) as $file) {
                            \opcache_invalidate($file, true);
                        }
                    }
                }
            }
        };

        $params = \Yii::$app->params['workerman'];

        $params = array_merge([
            'pidFile' => '@runtime/workerman.pid',
            'logFile' => '@runtime/workerman.log',
            'stdoutFile' => '@runtime/workerman.stdout.log',
            'statusFile' => '@runtime/workerman.status',
        ], $params);

        Worker::$pidFile = Yii::getAlias($params['pidFile']);
        Worker::$logFile = Yii::getAlias($params['logFile']);
        Worker::$stdoutFile = Yii::getAlias($params['stdoutFile']);

        if ($params['eventLoop']) {
            Worker::$eventLoopClass = $params['eventLoop'];
        }
        if ($params['maxPackageSize']) {
            TcpConnection::$defaultMaxPackageSize = $params['maxPackageSize'];
        }
        if ($params['status_file'] && property_exists(Worker::class, 'statusFile')) {
            Worker::$statusFile = Yii::getAlias($params['status_file']);
        }
        if ($params['stop_timeout'] && property_exists(Worker::class, 'stopTimeout')) {
            Worker::$stopTimeout = $params['stop_timeout'];
        }

        // 进程处理
        foreach ($params['process'] ?? [] as $name => $process) {

            if (!isset($process['class'])) {
                $process['class'] = Process::class;
            }

            $process['name'] = $process['name'] ?? $name;

            $process = Yii::createObject($process);

            if ($process instanceof ProcessInterface) {
                $process->start();
            } else {
                throw new InvalidConfigException('workerman process error: process must instanceof ' . ProcessInterface::class);
            }
        }

        Worker::runAll();

        return 0;
    }
}