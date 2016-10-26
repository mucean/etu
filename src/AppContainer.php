<?php
namespace Etu;

use Etu\Http\Context;
use Etu\Http\Request;
use Etu\Http\Response;
use Etu\Handlers\Error;
use Etu\Handlers\NotFound;

class AppContainer extends Container
{
    protected $defaultSetting = [
        'showErrorDetails' => false
    ];

    public function __construct(array $items = [])
    {
        parent::__construct($items);

        $this->registerDefaultServices();
    }

    protected function registerDefaultServices()
    {
        if (!$this->has('context')) {
            $this->add('context', function () {
                return new Context($_SERVER);
            }, false);
        }

        if (!$this->has('request')) {
            $this->add('request', function () {
                $context = $this->get('context');
                return Request::buildFromContext($context);
            });
        }

        if (!$this->has('response')) {
            $this->add('response', function () {
                return new Response();
            }, false);
        }

        if (!$this->has('router')) {
            $this->add('router', function ($path = '/Controller', $namespace = '\\') {
                return new Router($path, $namespace, $this);
            }, false);
        }

        if (!$this->has('errorHandler')) {
            $this->add('errorHandler', function () {
                $setting = $this->get('setting');
                return new Error($setting->get('showErrorDetails', false));
            });
        }

        if (!$this->has('notFoundHandler')) {
            $this->add('notFoundHandler', function () {
                $setting = $this->get('setting');
                return new NotFound($setting->get('showErrorDetails', false));
            }, false);
        }
    }
}
