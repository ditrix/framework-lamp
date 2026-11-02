<?php

namespace PHPFramework;

class Request
{

    public string $uri;

    public function __construct($uri)
    {

        $this->uri = $uri;

        var_dump($this->uri);
    //    throw new \Exception('Not implemented');
    }


}