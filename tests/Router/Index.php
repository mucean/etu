<?php
namespace Tests\Router;

class Index
{
    public function get()
    {
        $this->response->write('Hello, world!');

        return $this->response;
    }
}
