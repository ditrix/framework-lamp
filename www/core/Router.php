<?php

namespace PHPFramework;

class Router
{

    // protected Request $request;
    // protected Response $response;

    protected array $routes = [];
    protected array $route_params = [];

    // public function __construct(Request $request, Response $response)
    // {
    //     $this->request = $request;
    //     $this->response = $response;
    // }


    public function __construct(
        protected Request $request,
        protected Response $response
    ) {}

    public function add(string $path, $callback, $method): self
    {
        // $this->routes[$method][$path] = $callback;
        $path = trim($path, '/');

        // приводим все к верхнему регистру + удаляем слеши в начале и конце
        // $this->routes[strtoupper($method)][$path] = $callback;
        if (is_array($method)) {
            $method = array_map('strtoupper', $method);
        } else {
            $method = strtoupper($method);
        }

        $this->routes[] = [
            'path' => "/$path",
            'callback' => $callback,
            'middleware' => null,
            'method' => $method
        ];

        return $this;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function get(string $path, $callback): self
    {
        return $this->add($path, $callback, 'GET');
    }

    public function post(string $path, $callback): self
    {
        return $this->add($path, $callback, 'POST');
    }

    public function dispatch()
    {
        // $uri = $this->request->uri;
        // $method = $this->request->getMethod();

        // foreach ($this->routes as $route) {
        //     if ($route['path'] === "/$uri" && (is_array($route['method']) ? in_array($method, $route['method']) : $route['method'] === $method)) {
        //         if (is_callable($route['callback'])) {
        //             return call_user_func($route['callback']);
        //         } elseif (is_array($route['callback'])) {
        //             [$controllerClass, $action] = $route['callback'];
        //             if (class_exists($controllerClass)) {
        //                 $controller = new $controllerClass();
        //                 if (method_exists($controller, $action)) {
        //                     return call_user_func([$controller, $action]);
        //                 }
        //             }
        //         }
        //     }
        // }

        // http_response_code(404);
        // return '404 Not Found';
        echo "Dispatching route for URI: " . $this->request->uri . "<br>";
    }
}
