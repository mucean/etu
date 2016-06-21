<?php
namespace Etu;

use Etu\Traits\ArrayPropertyAllAccess;
use Etu\Traits\Singleton;
use Etu\Http\Context;
use Etu\Http\Request;
use Etu\Http\Response;
use Etu\Handlers\Error;
use Etu\Router;
use Closure;
use InvalidArgumentException;

class Container
{
    use Singleton, ArrayPropertyAllAccess {
        ArrayPropertyAllAccess::get as protected getProperty;
        ArrayPropertyAllAccess::has as protected hasProperty;
        ArrayPropertyAllAccess::set as protected setProperty;
        ArrayPropertyAllAccess::unset as protected unsetProperty;
    }

    protected $container = [];

    protected $mantain = [];

    protected $calls = [];

    protected $defaultSetting = [
        'showErrorDetails' => false
    ];

    protected function __construct(array $items)
    {
        $this->registerPropertyAccess('container', true);

        $this->registerPropertyAccess('mantain', true);

        $this->registerPropertyAccess('calls', true);

        $setting = $this->defaultSetting;
        if (isset($item['setting'])) {
            $setting = array_merge($setting, $item['setting']);
            unset($item['setting']);
        }

        $this->add(
            'setting',
            function () use ($setting) {
                return new ArrayAccess($setting);
            },
            false
        );

        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException(
                    'Container construct each item of array argument must be an array type'
                );
            }
            call_user_func_array([$this, 'add'], $item);
        }

        $this->registerDefaultServices();
    }

    public function get($id, $arguments = [])
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Identifier %s is not found', $id));
        }

        $value = $this->getProperty('container', [$id]);

        if (is_callable($value) && !$this->hasProperty('calls', [$id])) {
            $call = $value;
            $value = call_user_func_array($value, $arguments);
            if (!$this->hasProperty('mantain', [$id])) {
                $this->setProperty('calls', [$id], $call);
                $this->setProperty('container', [$id], $value);
            }
        }

        return $value;
    }

    public function has($id)
    {
        return $this->hasProperty('container', [$id]);
    }

    public function add($id, $value, $bindThis = true)
    {
        if ($this->has($id)) {
            $this->remove($id);
        }

        if ($bindThis && is_callable($value) && ($value instanceof Closure)) {
            $value = $value->bindTo($this);
        }

        $this->setProperty('container', [$id], $value);
    }

    public function update($id, $value)
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Identifier %s is not found', $id));
        }

        $this->setProperty('container', [$id], $value);
    }

    public function remove($id)
    {
        $this->unsetProperty('container', [$id]);
        $this->unsetProperty('mantain', [$id]);
        $this->unsetProperty('calls', [$id]);
    }

    public function getCalledCall($id)
    {
        if (!$this->hasProperty('calls', [$id])) {
            throw new InvalidArgumentException(
                sprintf('Identifier %s is not found or not called', $id)
            );
        }

        return $this->getProperty('calls', [$id]);
    }

    public function mantain($id)
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Identifier %s is not found', $id));
        }

        if ($this->hasProperty('calls', [$id])) {
            throw new InvalidArgumentException('service has been called, can not mantain');
        }

        $value = $this->getProperty('container', [$id]);
        if (!is_callable($value)) {
            throw new InvalidArgumentException('mantain service must be a callable function or object');
        }

        $this->setProperty('mantain', [$id], true);
    }

    protected function registerDefaultServices()
    {
        $this->add('context', function () {
            return new Context($_SERVER);
        }, false);

        $this->add('request', function () {
            $context = $this->get('context');
            return Request::buildFromContext($context);
        });

        $this->add('response', function () {
            return new Response();
        }, false);

        $this->add('router', function ($path = '/Controller', $namespace = '\\') {
            return new Router($path, $namespace);
        }, false);

        $this->add('errorHandler', function () {
            $setting = $this->get('setting');
            return new Error($setting->get('showErrorDetails', false));
        });
    }
}
