<?php

namespace Etu;

use Etu\Http\Context;
use Etu\Http\Request;
use Etu\Http\Response;
use Etu\Handlers\Error;
use Etu\Handlers\NotFound;
use Etu\Interfaces\ContainerInterface;

class DefaultServices
{
    public static function register(ContainerInterface $container)
    {
        if (!$container->has('context')) {
            $container->add('context', function () {
                return new Context($_SERVER);
            });
        }

        if (!$container->has('request')) {
            $container->add('request', function () use ($container) {
                $context = $container->get('context');
                return Request::buildFromContext($context);
            });
        }

        if (!$container->has('response')) {
            $container->add('response', function () {
                return new Response();
            });
        }

        if (!$container->has('router')) {
            $container->add('router', function (ContainerInterface $container, $path = '/Controller', $namespace = '\\') {
                return new Router($path, $namespace, $container);
            });
        }

        if (!$container->has('errorHandler')) {
            $container->add('errorHandler', function (ContainerInterface $container) {
                $setting = $container->get('setting');
                return new Error($setting->get('showErrorDetails', false));
            });
        }

        if (!$container->has('notFoundHandler')) {
            $container->add('notFoundHandler', function (ContainerInterface $container) {
                $setting = $container->get('setting');
                return new NotFound($setting->get('showErrorDetails', false));
            });
        }
    }
}