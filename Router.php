<?php

namespace TheFold\WordPress;

class Router {

    function __construct(array $url_callbacks, $priority = 5) {

        add_filter('rewrite_rules_array', function($rules) use ($url_callbacks) {

            foreach (array_keys($url_callbacks) as $look_for_in_url) {

                if(is_array($look_for_in_url)){
                    list($method, $route) = each($look_for_in_url);
                } else {
                    $method = '*';
                    $route = $look_for_in_url;
                }

                $newRule = ['^'.trim($route,'/').'/?$' => 'index.php?'.$this->query_var_name($look_for_in_url).'='.$method];
                $rules = $newRule + $rules;
            }

            return $rules;
        });

        add_filter('query_vars', function($qvars) use ($url_callbacks) {
            
            foreach (array_keys($url_callbacks) as $look_for_in_url) {
                
                $var = $this->query_var_name($look_for_in_url);
                $qvars[] = $var; 
            }
            return $qvars;
        });
        
        add_action( 'template_redirect', function() use ($url_callbacks) {

            global $wp_query;

            foreach ($url_callbacks as $url_key => $callback) {

                if ($wp_query->get( $this->query_var_name($url_key))) {

                    if(is_array($url_key)){
                        list($method, $route) = $url_key;
                    } else {
                        $method = '*';
                        $route = $url_key;
                    }

                    if($method == '*' || $method == $_REQUEST['REQUEST_METHOD']){

                        $wp_query->is_home = false;

                        $params = null;

                        preg_match('#'.trim($route,'/').'#',$_SERVER['REQUEST_URI'],$params);

                        $res = call_user_func_array($callback,$params);

                        if($res === false)
                            $this->send_404();
                        else{
                            exit();
                        }
                    }
                }
            }
        }, get_option('url_access_priority',$priority) );
       
        add_action('init', function() use ($url_callbacks) {
            $this->maybe_flush_rewrites($url_callbacks);
        }, 99);
    }

    protected function maybe_flush_rewrites($url_callbacks) {

        $current = md5(json_encode(array_keys($url_callbacks)));

        $cached = get_option(get_called_class(), null );

        if ( empty( $cached ) ||  $current !== $cached ) {
            flush_rewrite_rules();
            update_option(get_called_class(), $current );
        }
    }
  
    protected function query_var_name($route) {

        static $cache;

        if (!isset($cache[$route])) {
            $cache[$route] = md5(json_encode($route));
        }

        return $cache[$route]; 
    }

    protected function send_404() {

        global $wp_query;

        status_header('404');

        $wp_query->set_404();
    }
}
