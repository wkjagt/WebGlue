<?php

class WebGlue implements ArrayAccess
{
    /**
     * Conversions from curly brace notations in the routes to regular expressions.
     * Available wildcards:
     *    {<arg-name>:num}    : numeric values
     *    {<arg-name>:string} : string values
     */
    public static $routeParamPatterns = array(
        '|{(\w+):num}|' => '(?P<$1>\d+)',
        '|{(\w+):string}|' => '(?P<$1>\w+)',
    );

    /** 
     * Array of routes
     */
    protected $routes = array();

    /**
     * Pimple. Dependencey injection container
     */
    protected $container;

    /**
     * Constructor to set Pimple DIC
     */
    public function __construct()
    {
        $this->container = new Pimple;
    }

    /**
     * Magin __call method, catching all "route" methods. All valid
     * HTTP methods (in lower case) are valid method calls.
     *
     * @param string pattern
     *    the url pattern to match for this route. For syntax see
     *    documentation for the $paramPatterns property
     * @param closure|array
     *    the callback to execute for this route. A callback can be either
     *    a closure, or an array that's accepted as the callback for
     *    user_func_array: (class, method)
     *
     * @throws UnexpectedValueException
     *    if the method is not valid or the number of arguments is < 2
     */
    public function __call($method, $args)
    {
        $valid = in_array($method, array(
            'get', 'post', 'put', 'delete', 'head', 'options', 'patch',
        )) && count($args) >= 2;

        if($valid) {
            $this->routes[] = (object) array(
                'method' => strtoupper($method),
                'pattern' => $args[0],
                'callback' => $args[1]
            );
            return;
        }
        throw new UnexpectedValueException('Route error');
    }

    /**
     * Run the app
     */
    public function run()
    {
        $request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $response = Symfony\Component\HttpFoundation\Response::create('', 404);

        // build search and replace arrays to replace curly brace
        // notation with regex notation
        list($s, $r) = array(
            array_keys(static::$routeParamPatterns),
            array_values(static::$routeParamPatterns)
        );

        foreach($this->routes as $route) {

            // turn pattern into regex pattern
            $pattern = ':^' . preg_replace($s, $r, $route->pattern) . '$:';

            if(preg_match($pattern, $request->getPathInfo(), $matches)) {

                $response->setStatusCode(405);

                if($route->method == $request->getMethod()) {

                    // get the named groups and set them as request attributes
                    foreach($matches as $key => $match) {
                        if(!is_numeric($key)) {
                            $request->attributes->set($key, $match);
                        }
                    }
                    if(is_callable($route->callback)) {
                        $response->setStatusCode(200);
                        call_user_func_array($route->callback, array($this, $request, $response));
                    } else {
                        $response->setStatusCode(500);
                    }
                }
            }
        }
        $response->send();
    }

    /**
     * To satisfy ArrayAccess interface
     * Sets a value on the dependency injection container
     *
     * @param string $offset
     *    the name of the service added to the DIC
     * @param mixed $value
     *    the serice to add
     */
    public function offsetSet($offset, $value) {
        $this->container[$offset] = $value;
    }

    /**
     * To satisfy ArrayAccess interface
     */
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    /**
     * To satisfy ArrayAccess interface
     * Remove a service from the container
     *
     * @param string $offset
     *    the name of the service to remove from the DIC
     */
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    /**
     * To satisfy ArrayAccess interface
     * Get a service from the DIC
     *
     * @param string $offset
     *    the name of the service to get from the DIC
     */
    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}