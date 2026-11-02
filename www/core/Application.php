<?php

namespace PHPFramework;

class Application
{

    protected string $uri;

    public Request $request;


    public function __construct()
    {
        $this->uri = $_SERVER['QUERY_STRING'];

        $this->request = new Request($this->uri);
    }


}
