WebGlue
=======

WebGlue is a tiny web framework. Actually it's nothing more than some glue between the
[Symfony HttpFoundation](https://github.com/symfony/HttpFoundation) Request and Response object, and a very simple
router.

##Hello Willem

```php
$app = new WebGlue;

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
