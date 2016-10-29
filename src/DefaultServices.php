<?php

namespace Etu;

use Etu\Http\Context;
use Etu\Http\Request;
use Etu\Http\Response;
use Etu\Handlers\Error;
use Etu\Handlers\NotFound;

class DefaultServices
{
    public static function register(Container $container)
    {
        if (!$container->has('context')) {
            $container->add('context', function () {
                return new Context($_SERVER);
            }, false);
        }

        if (!$container->has('request')) {
            $container->add('request', function () {
                $context = $this->get('context');
                return Request::buildFromContext($context);
            });
        }

        if (!$container->has('response')) {
            $container->add('response', function () {
                return new Response();
            }, false);
        }

        if (!$container->has('router')) {
            $container->add('router', function ($path = '/Controller', $namespace = '\\') {
                return new Router($path, $namespace, $this);
            });
        }

        if (!$container->has('errorHandler')) {
            $container->add('errorHandler', function () {
                $setting = $this->get('setting');
                return new Error($setting->get('showErrorDetails', false));
            });
        }

        if (!$container->has('notFoundHandler')) {
            $container->add('notFoundHandler', function () {
                $setting = $this->get('setting');
                return new NotFound($setting->get('showErrorDetails', false));
            });
        }
    }
}