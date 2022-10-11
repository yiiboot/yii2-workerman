<?php

namespace stack\workerman;

interface ProcessInterface
{
    /**
     * 进程名称
     *
     * @return string
     */
    public function getName(): string;

    /**
     * 监听的协议 ip 及端口 （可选）
     *
     * @return string
     */
    public function getListen(): string;

    /**
     * 进程数
     *
     * @return int
     */
    public function getCount(): int;

    /**
     * 进程运行用户 （可选，默认当前用户）
     *
     * @return string
     */
    public function getUser(): string;

    /**
     * 进程运行用户组 （可选，默认当前用户组）
     *
     * @return string
     */
    public function getGroup(): string;

    /**
     * 当前进程是否支持reload （可选，默认true）
     *
     * @return bool
     */
    public function isReloadable(): bool;

    /**
     * 是否开启reusePort （可选，此选项需要php>=7.0，默认为true）
     *
     * @return bool
     */
    public function isReusePort(): bool;

    /**
     * transport (可选，当需要开启ssl时设置为ssl，默认为tcp)
     *
     * @return string
     */
    public function getTransport(): string;

    /**
     * context （可选，当transport为是ssl时，需要传递证书路径）
     *
     * @return array
     */
    public function getContext(): array;

    /**
     * 获取进程处理器
     *
     * @return object|string
     */
    public function getHandler(): object|string|null;

    /**
     * 获取服务进程
     *
     * @return ProcessInterface[]
     */
    public function getServices(): array;

    /**
     * 启动
     *
     * @return mixed
     */
    public function start();
}