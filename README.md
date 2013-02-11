WebGlue
=======

WebGlue is a tiny web framework. Actually it's nothing more than some glue between the
[Symfony HttpFoundation](https://github.com/symfony/HttpFoundation) Request and Response, and a very simple
router.

##Usage

```php
$app = new WebGlue;

$app->get('/', function($app, $request, $response){
    
    $response->setContent('hello world');
});

$app->run();
```
