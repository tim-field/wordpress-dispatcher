# WordPress Dispatcher

Easily add custom URL endpoints in WordPress. Map a url to a function.

##Example

```
new \TheFold\WordPress\Dispatch([

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

<https://packagist.org/packages/thefold/wordpress-dispatcher>
