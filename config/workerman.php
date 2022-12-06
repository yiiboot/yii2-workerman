<?php

return [
    'workerman' => [
        // The file to store master process PID.
        'pidFile' => '@runtime/workerman.pid',
        // Log file.
        'logFile' => '@runtime/workerman.log',
        // Stdout file.
        'stdoutFile' => '@runtime/workerman.stdout.log',
        // The file used to store the master process status file.
        'statusFile' => '@runtime/workerman.status',
        // EventLoopClass
        'eventLoop' => '',
        // After sending the stop command to the child process stopTimeout seconds, if the process is still living then forced to kill.
        'stopTimeout' => null,
        // Default maximum acceptable packet size, default 10485760
        'maxPackageSize' => null,
        // 进程
        'process' => [
            [
                'name' => 'Yii2 Workerman Server',
                'class' => \yiiboot\workerman\WebProcess::class,
                'listen' => 'http://0.0.0.0:8080',
                'config' => [
                    // the application config
                ]
            ],
            [
                'handler' => [
                    'class' => \yiiboot\workerman\process\Monitor::class,
                    '__construct()' => [
                        // the monitor dirs
                        'resource' => ['@project'],
                        // the file name patterns
                        'patterns' => ['*.php', '*.yaml', '*.html', '*.htm', '*.twig'],
                        // to exclude dirs
                        'exclude' => ['./vendor', './runtime', './data'],
                    ]
                ]
            ]
        ]
    ]
];