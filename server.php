<?php

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use RingCentral\Psr7\Response;
use function WyriHaximus\psr7_response_decode;
use function WyriHaximus\psr7_response_encode;
use function WyriHaximus\psr7_server_request_decode;
use function WyriHaximus\psr7_server_request_encode;
use WyriHaximus\React\Parallel\Finite;

require 'vendor/autoload.php';

$loop = Factory::create();
echo 'Using the following event loop: ', get_class($loop), PHP_EOL;

$pool = new Finite($loop, 64);
$server = new HttpServer(function (ServerRequestInterface $request) use ($pool) {
    return $pool->run(function ($jsonRequest) {
        $request = psr7_server_request_decode($jsonRequest); // Not using this but it's have to give a full view

        $response = new Response(200, [], 'Hello world!');

        return psr7_response_encode($response);
    }, [psr7_server_request_encode($request)])->then(function ($response) {
        return psr7_response_decode($response);
    });
});

$socket = new SocketServer('0.0.0.0:7331', $loop);
$server->listen($socket);
$server->on('error', function (Throwable $throwable) {
    echo (string)$throwable;
});

$handler = function () use (&$handler, $loop, $socket) {
    $loop->removeSignal(SIGTERM, $handler);
    $socket->close();
};

$loop->addSignal(SIGTERM, $handler);

echo 'Starting loop!', PHP_EOL;
$loop->run();