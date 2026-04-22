<?php
/**
 * 路由类
 */

namespace Core;

use Exception;

class Router
{
    private static $routes = [];
    private static $currentRoute = null;

    public static function get($path, $handler, $middleware = [])
    {
        self::addRoute('GET', $path, $handler, $middleware);
    }

    public static function post($path, $handler, $middleware = [])
    {
        self::addRoute('POST', $path, $handler, $middleware);
    }

    public static function put($path, $handler, $middleware = [])
    {
        self::addRoute('PUT', $path, $handler, $middleware);
    }

    public static function delete($path, $handler, $middleware = [])
    {
        self::addRoute('DELETE', $path, $handler, $middleware);
    }

    public static function any($path, $handler, $middleware = [])
    {
        self::addRoute('*', $path, $handler, $middleware);
    }

    private static function addRoute($method, $path, $handler, $middleware = [])
    {
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $pattern = self::convertPathToPattern($path);

        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    private static function convertPathToPattern($path)
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public static function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = rtrim($uri, '/');
        if ($path === '') {
            $path = '/';
        }

        foreach (self::$routes as $route) {
            if ($route['method'] !== '*' && $route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                self::$currentRoute = $route;

                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }

                foreach ($route['middleware'] as $middleware) {
                    $result = self::executeMiddleware($middleware);
                    if ($result !== true) {
                        if (is_array($result)) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode($result, JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                        return;
                    }
                }

                self::executeHandler($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => 404,
            'message' => '路由不存在',
            'data' => null,
            'timestamp' => time(),
        ], JSON_UNESCAPED_UNICODE);
    }

    private static function executeMiddleware($middleware)
    {
        if (is_callable($middleware)) {
            return call_user_func($middleware);
        }

        if (is_string($middleware)) {
            $middlewareClass = "Middleware\\{$middleware}";
            if (class_exists($middlewareClass)) {
                $instance = new $middlewareClass();
                if (method_exists($instance, 'handle')) {
                    return $instance->handle();
                }
            }
        }

        return true;
    }

    private static function executeHandler($handler, $params = [])
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controllerClass, $method) = explode('@', $handler);
            
            $controllerClass = "Controllers\\{$controllerClass}";
            
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                
                if (method_exists($controller, $method)) {
                    call_user_func_array([$controller, $method], $params);
                    return;
                }
            }
        }

        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => 500,
            'message' => '处理器不存在',
            'data' => null,
            'timestamp' => time(),
        ], JSON_UNESCAPED_UNICODE);
    }

    public static function getCurrentRoute()
    {
        return self::$currentRoute;
    }
}
