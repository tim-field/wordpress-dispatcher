# WordPress Dispatcher

URL endpoints in WordPress

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
