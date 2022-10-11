<?php

namespace stack\workerman\web;

use GuzzleHttp\Psr7\Utils;

/**
 * 重写 sendStreamAsFile 以便支持下载文件和stream
 *
 * @author niqingyang<niqy@qq.com>
 * @date 2022/10/8 00:55
 */
class Response extends \yii\Psr7\web\Response
{
    /**
     * {@inheritDoc}
     */
    public function sendStreamAsFile($handle, $attachmentName, $options = [])
    {
        $response = parent::sendStreamAsFile($handle, $attachmentName, $options);
        $this->stream = Utils::streamFor($this->stream[0]);
        return $response;
    }
}