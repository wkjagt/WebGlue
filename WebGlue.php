<?php

class WebGlue implements ArrayAccess
{
    /**
     * Conversions from curly brace notations in the routes to regular expressions.
     * Available wildcards:
     *    {num}    : numeric values
     */
    public static $paramPatterns = array(
        '|{(\w+):num}|' => '(?P<$1>\d+)',
    );

    protected $routes = array();

    protected $container;

    public function __construct()
    {
        $this->container = new Pimple;
    }

    public function __call($method, $args)
    {
        $valid = in_array($method, array(
            'get', 
            'post',
            'put',
            'delete',
            'head',
            'options',
            'patch',
        ));
        if($valid) {
            $this->routes[] = (object) array(
                'method' => strtoupper($method),
                'pattern' => $args[0],
                'callback' => $args[1]
            );
        }
    }

    public function run()
    {
        $request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $response = Symfony\Component\HttpFoundation\Response::create();

        list($s, $r) = array(
            array_keys(static::$paramPatterns),
            array_values(static::$paramPatterns)
        );

        $pathFound = false;

        foreach($this->routes as $route) {

            $pattern = ':^' . preg_replace($s, $r, $route->pattern) . '$:';

            if(preg_match($pattern, $request->getPathInfo(), $matches)) {

                $pathFound = true;

                if($route->method == $request->getMethod()) {

                    foreach($matches as $key => $match) {
                        if(!is_numeric($key)) {
                            $request->attributes->set($key, $match);
                        }
                    }

                    $callback = $route->callback;

                    if(is_string($callback)) {
                        $callback($this, $request, $response);                        
                    } elseif(is_array($callback) && count($callback) == 2) {
                        call_user_func_array($callback, array($this, $request, $response));
                    }

                    $response->send();
                    exit;
                }
            }
        }
        $response->setStatusCode($pathFound ? 405 : 404)->send();
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}
