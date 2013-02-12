WebGlue
=======

WebGlue is a tiny web framework. Actually it's nothing more than some glue between the
[Symfony HttpFoundation](https://github.com/symfony/HttpFoundation) Request and Response object, and a very simple
router. I find it helps me a lot for quick prototyping and small projects.

#Usage

Very simple:

1. Instantiate WebGlue
2. Add services to instance using array notation *(optional)*
3. Add routes using following syntax `$app-><method>(<route>, <callback>);`
4. Your callback will get 3 arguments passed to it: the WebGlue instance, a request object, a response object
5. Modify the response to your needs. You don't need to return it.

#Examples

##Hello Willem

```php
$app = new WebGlue;

// for routes accepting POST, use $app->post, etc.
$app->get('/', function($app, $request, $response){
    $response->setContent('hello world');
});

$app->get('/greet/{name:string}', function($app, $request, $response){
    $name = $request->attributes->get('name');
    $response->setContent('hello ' . $name);
});

$app->run();
```

##Add services

```php
$app = new WebGlue;

// add the excellent Twig templating engine
$app['twig'] = new Twig_Environment(new Twig_Loader_Filesystem(__DIR__.'/templates'));

$app->get('/', function($app, $request, $response){
    $response->setContent(app['twig']->render('hello_world.html.twig'));
});
```

##Use composer

Only master for now...

```json
{
    "require" : {
        "webglue/webglue": "dev-master"
    }
}
```

and 

```php
include __DIR__.'/vendor/autoload.php';
```
