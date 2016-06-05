<?php
namespace Etu;

use Etu\Traits\ArrayPropertyAllAccess;
use Etu\Traits\Singleton;
use Etu\Http\Context;
use Etu\Http\Request;
use Etu\Http\Response;
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

    protected function __construct(array $items)
    {
        $this->registerPropertyAccess('container', true);

        $this->registerPropertyAccess('mantain', true);

        $this->registerPropertyAccess('calls', true);

        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException(
                    'Container construct each item of array argument must be an array type'
                );
            }
            call_user_func_array([$this, 'add'], $item);
        }
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Identifier %s is not found', $id));
        }

        $value = $this->getProperty('container', [$id]);

        if (is_callable($value) && !$this->hasProperty('calls')) {
            $call = $value;
            $value = call_user_func_array($value);
            if (!$this->hasProperty('mantain', $id)) {
                $this->setProperty('calls', $id, $call);
                $this->add($id, $value, false);
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
        if ($bindThis && is_callable($value) && ($value instanceof Closure)) {
            $value->bindTo($this);
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
            throw new InvalidArgumentException(sprintf('Identifier %s is not found', $id));
        }

        return $this->getProperty('calls', [$id]);
    }

    public function mantain($id)
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Identifier %s is not found', $id));
        }

        $value = $this->getProperty('container', [$id]);
        if (!is_callable($value)) {
            throw new InvalidArgumentException('mantain service must be a callable function or object');
        }

        $this->setProperty('mantain', $id, true);
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
    }
}
