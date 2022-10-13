<?php

use yii\helpers\ArrayHelper;
use yii\Psr7\web\ErrorHandler;
use yii\Psr7\web\Request;
use yii\Psr7\web\Response;

return function (array $config, array $params) {
    return ArrayHelper::merge($config, [
        'components' => [
            'request' => [
                'class' => Request::class,
                'csrfParam' => '_csrf',
                // !!! insert a secret key in the following (if it is empty) - this
                // is required by cookie validation
                'cookieValidationKey' => $params['request.cookieValidationKey'],
                'parsers' => [
                    'application/json' => yii\web\JsonParser::class
                ]
            ],
            'response' => [
                'class' => Response::class,
                // 'format' => yii\web\Response::FORMAT_JSON
            ],
            'errorHandler' => [
                'class' => ErrorHandler::class,
                'errorAction' => 'site/error',
            ],
        ]
    ]);
};

