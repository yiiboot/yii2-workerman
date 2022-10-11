<?php

namespace stack\workerman\web;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http;
use Workerman\Psr7\Response;
use Workerman\Psr7\ServerRequest;
use Yii;
use yii\helpers\FileHelper;
use function Workerman\Psr7\response_to_string;

/**
 * 处理接收到的请求
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/7 11:48
 */
class RequestHandler
{
    private Application $application;
    private FinfoMimeTypeDetector $detector;

    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->detector = new FinfoMimeTypeDetector();
        Http::requestClass(ServerRequest::class);
    }

    /**
     * @throws \Exception
     */
    public function onMessage(TcpConnection $connection, ServerRequest $psrRequest): void
    {
        $checkFile = Yii::getAlias("@webroot/{$psrRequest->getUri()->getPath()}");
        $checkFile = FileHelper::normalizePath($checkFile);

        if (is_file($checkFile)) {
            $code = file_get_contents($checkFile);
            $psrResponse = new Response(200, [
                'Content-Type' => $this->detector->detectMimeType($checkFile, $code),
                'Last-Modified' => gmdate('D, d M Y H:i:s', filemtime($checkFile)) . ' GMT',
            ], $code);
            $connection->send(response_to_string($psrResponse), true);
            return;
        }

        // 注入真实IP
        $psrRequest->setHeaders([
            'REMOTE_ADDR' => $connection->getRemoteIp(),
            'REMOTE_PORT' => $connection->getRemotePort(),
        ]);

        try {
            $response = $this->application->handle($psrRequest);
            $connection->send(response_to_string($response), true);

            if ($this->application->clean() && DIRECTORY_SEPARATOR === '/') {
                echo "application clean and reload\n";
                posix_kill(posix_getppid(), SIGUSR1);
            }
        } catch (\Throwable $e) {
            $connection->send(response_to_string(new Response(500, [], $e->getMessage())), true);
        }
    }
}