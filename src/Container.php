<?php
namespace Etu;

use Etu\Traits\ArrayPropertyAllAccess;
use Etu\Traits\Singleton;

class Container
{
    use Singleton, ArrayPropertyAllAccess {
        ArrayPropertyAllAccess::get as protected getProperty;
        ArrayPropertyAllAccess::has as protected hasProperty;
        ArrayPropertyAllAccess::set as protected setProperty;
        ArrayPropertyAllAccess::unset as protected unsetProperty;
    }

    protected $container = [];

    protected function __construct(array $items)
    {
        $this->registerPropertyAccess('container', true);
        foreach ($items as $id => $value) {
            $this->add($id, $value);
        }
    }

    public function get($id)
    {
        if (!$this->hasProperty('container', [$id])) {
            throw new InvalidArgumentException(sprintf('Identifier %s is not found', $id));
        }

        $value = $this->getProperty('container', [$id]);

        if (is_callable($value)) {
            $value = call_user_func_array($value);
            $this->add($id, $value);
        }

        return $value;
    }

    public function has($id)
    {
        return $this->hasProperty('container', [$id]);
    }

    public function add($id, $value)
    {
        $this->setProperty('container', [$id], $value);
    }

    public function remove($id)
    {
        $this->unsetProperty('container', [$id]);
    }
}
