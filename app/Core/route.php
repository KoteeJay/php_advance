<?php

namespace App\Core;

use App\Core\MiddleWare;

class Route extends MiddleWare
{
    public static $routes = [];

    //route handler
    public static function routeHandler($uri, $controlargs, $method, $middleware = null)
    {
        //convert uri to preg
        if (preg_match_all('/\{[a-zA-Z0-9-_@]+\}/', $uri, $matches)) {
            //convert to dynamic regex
            $uri2 = preg_replace('/\{[a-zA-Z0-9-_@]+\}/', '([a-zA-Z0-9-_@]+)', $uri);
            //escape /
            $uri2 = str_replace('/', '\/', $uri2);
            //add start and end
            $uri2 = '/^' . $uri2 . '$/';
        } else {
            $uri2 = $uri;
            $matches = false;
        }
        //save route
        self::$routes[] = [
            'uri' => $uri,
            'preg' => $uri2,
            'matches' => $matches,
            'controlargs' => $controlargs,
            'method' => $method,
            'middleware' => $middleware
        ];
    }

    //validate URL
    public static function validateURL($uri, $controlargs, $middleware)
    {
        //check if uri has parameters
        if (preg_match_all('/\{[a-zA-Z0-9-_@]+\}/', $uri, $matches)) {
            //convert to dynamic regex
            $uri2 = preg_replace('/\{[a-zA-Z0-9-_@]+\}/', '([a-zA-Z0-9-_@]+)', $uri);
            //escape /
            $uri2 = str_replace('/', '\/', $uri2);
            //add start and end
            $uri2 = '/^' . $uri2 . '$/';
            //pass as a variable
            $matches_data = [];
            //loop through matches
            foreach ($matches[0] as $match) {
                //remove {}
                $match = str_replace(['{', '}'], '', $match);
                //add to array
                $matches_data[$match] = null;
            }
            //check if uri matches preg pattern
            if (preg_match($uri2, Request::uri(), $matches)) {
                //process middleware
                self::processMiddleware($middleware);
                //check if $controlargs is callable
                if (is_callable($controlargs)) {
                    //remove first match
                    array_shift($matches);
                    $matches = array_combine(array_keys($matches_data), $matches);
                    //call method
                    $controlargs(
                        new Request,
                        $matches
                    );
                    return true;
                } else {
                    //check if controller exist
                    if (class_exists($controlargs[0])) {
                        //check if method exist
                        if (method_exists($controlargs[0], $controlargs[1])) {
                            //remove first match
                            array_shift($matches);
                            $matches = array_combine(array_keys($matches_data), $matches);
                            //call method
                            $controller = new $controlargs[0];
                            $controller->{$controlargs[1]}(
                                new Request,
                                $matches
                            );
                            return true;
                        } else {
                            self::methodNotFound($controlargs[1]);
                            return false;
                        }
                    } else {
                        self::classNotFound($controlargs[0]);
                        return false;
                    }
                }
            } else {
                return false;
            }
        } else {
            //check if uri matches preg pattern
            if ($uri == Request::uri()) {
                //process middleware
                self::processMiddleware($middleware);
                //check if $controlargs is callable
                if (is_callable($controlargs)) {
                    //call method
                    $controlargs(
                        new Request
                    );
                    return true;
                } else {
                    //check if controller exist
                    if (class_exists($controlargs[0])) {
                        //check if method exist
                        if (method_exists($controlargs[0], $controlargs[1])) {
                            //call method
                            $controller = new $controlargs[0];
                            $controller->{$controlargs[1]}(
                                new Request
                            );
                            return true;
                        } else {
                            self::methodNotFound($controlargs[1]);
                            return false;
                        }
                    } else {
                        self::classNotFound($controlargs[0]);
                        return false;
                    }
                }
            } else {
                return false;
            }
        }
    }

    //middleware
    public function middleware($middleware, $function)
    {
        //process middleware
        $function($middleware);
    }

    //redirect
    public static function redirect($simpleurl, $args = [])
    {
        //get base url
        $baseurl = Request::baseurl();
        //check if $simpleurl has /
        if (!strpos($simpleurl, '/')) {
            $simpleurl = '/' . $simpleurl;
        }
        //final url
        $url = $baseurl . $simpleurl;
        //check if args is not empty
        if (!empty($args)) {
            //add ? to url
            $url .= '?';
            //loop through args
            foreach ($args as $key => $value) {
                //add to url
                $url .= $key . '=' . $value . '&';
            }
            //remove last &
            $url = rtrim($url, '&');
        }
        //redirect
        header("Location: $url");
        exit;
    }

    //process middleware
    public static function processMiddleware($middleware)
    {
        //check if middleware is not null
        if (!is_null($middleware)) {
            $response = self::action($middleware);
            //check if response is not 200
            if ($response['code'] != 200) {
                //check if redirect is set
                if (isset($response['redirect'])) {
                    //check if $response['error']
                    if (isset($response['error'])) {
                        $message = ['error' => $response['error']];
                    } else {
                        $message = [];
                    }
                    //redirect
                    redirect($response['redirect'], $message);
                } else {
                    //show error
                    throw new \Exception($response['error'], $response['code']);
                    exit;
                }
            }
            //do nothing for 200
        }
    }

    //get
    public static function get($uri, $controlargs, $middleware = null)
    {
        self::routeHandler($uri, $controlargs, 'GET', $middleware);
    }

    //post
    public static function post($uri, $controlargs)
    {
        self::routeHandler($uri, $controlargs, 'POST');
    }

    //put
    public static function put($uri, $controlargs)
    {
        self::routeHandler($uri, $controlargs, 'PUT');
    }

    //delete
    public static function delete($uri, $controlargs)
    {
        self::routeHandler($uri, $controlargs, 'DELETE');
    }

    //any
    public static function any($uri, $controlargs)
    {
        self::routeHandler($uri, $controlargs, 'ANY');
    }

    //404
    public static function notFound()
    {
        echo '404';
        exit;
    }

    //404 header
    public static function notFoundHeader()
    {
        echo "No route found for this request method";
        exit;
    }

    //class not found
    public static function classNotFound($class)
    {
        echo "'$class' not found";
        exit;
    }

    //mehtod not found
    public static function methodNotFound($method)
    {
        echo "'$method' not found";
        exit;
    }

    //run
    public static function run()
    {
        //check if route is empty
        if (empty(self::$routes)) {
            self::notFound();
            return;
        }
        //current method
        $method = Request::method();
        $uri = Request::uri();
        //page not found
        $pageNotFound = [];
        //loop through routes
        foreach (self::$routes as $route) {
            //check if matches is not false
            if ($route["matches"] !== false) {
                //check if uri matches preg
                if (preg_match($route["preg"], $uri, $matches)) {
                    //check if method matches
                    if ($route["method"] == $method || $route["method"] == 'ANY') {
                        //do uri validation
                        self::validateURL($route["uri"], $route["controlargs"], $route["middleware"]);
                    } else {
                        self::notFoundHeader();
                    }
                    //page found
                    $pageNotFound[] = false;
                } else {
                    $pageNotFound[] = true;
                    //continue loop
                    continue;
                }
            } else {
                //check if uri matches
                if ($route["uri"] == $uri) {
                    //check if method matches
                    if ($route["method"] == $method || $route["method"] == 'ANY') {
                        //do validation
                        self::validateURL($route["uri"], $route["controlargs"], $route["middleware"]);
                    } else {
                        self::notFoundHeader();
                    }
                    //page found
                    $pageNotFound[] = false;
                } else {
                    $pageNotFound[] = true;
                    //continue loop
                    continue;
                }
            }
        }
        //check if page not found
        if (in_array(false, $pageNotFound)) {
            //do nothing.
            return;
        } else {
            self::notFound();
        }
    }
}
