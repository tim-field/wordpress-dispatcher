# WordPress Dispatcher / Router

Easily add custom URL endpoints in WordPress. Map a url to a function.

##Example

```
use \TheFold\WordPress\Router;

Router::routes([

    'testing-a-url' => function(){
        echo 'Hello Ted';
    },

    'hello-([a-z]+)' => function($request, $name){
        echo "Hello $name";
    }
]);
```

/testing-a-url & /hello-dougle will now be accessable in your WordPress site.


##Install

###Composer

composer require thefold/wordpress-dispatcher
