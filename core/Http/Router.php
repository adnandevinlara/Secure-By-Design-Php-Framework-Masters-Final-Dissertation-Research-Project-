<?php

namespace Core\Http;

class Router
{
    private array $routes = [];
    private Request $request;
    private Response $response;

    // 1. Inject the Request and Response objects when the Router is created
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $uri, callable|array $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, callable|array $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    private function addRoute(string $method, string $uri, callable|array $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'uri'    => $uri,
            'action' => $action
        ];
    }

    // 2. The dispatch method no longer needs arguments, it gets them from $this->request
    public function dispatch(): void
    {
        $parsedUri = $this->request->getUri();
        $requestMethod = $this->request->getMethod();

        foreach ($this->routes as $route) {
            if ($route['uri'] === $parsedUri && $route['method'] === $requestMethod) {
                
                $action = $route['action'];

                // 3a. If the route is a simple function (closure)
                if (is_callable($action)) {
                    // Pass the request and response objects into the function
                    call_user_func($action, $this->request, $this->response);
                    return;
                }

                // 3b. If the route is pointing to a Controller Class (e.g., [UserController::class, 'index'])
                if (is_array($action) && count($action) === 2) {
                    [$class, $method] = $action;
                    
                    if (class_exists($class) && method_exists($class, $method)) {
                        $controller = new $class();
                        // Pass the request and response objects into the controller method
                        call_user_func([$controller, $method], $this->request, $this->response);
                        return;
                    }
                }
            }
        }

        // 4. If no route matches, use the Response object to send a clean 404
        $this->response->setStatusCode(404);
        echo "404 - Secure Route Not Found";
    }
}