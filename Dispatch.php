<?php

namespace TheFold\WordPress;

class Dispatch {

    function __construct(array $url_callbacks, $priority = 5) {

        add_filter('rewrite_rules_array', function($rules) use ($url_callbacks) {

            foreach (array_keys($url_callbacks) as $look_for_in_url) {

                $newRule = ['^'.trim($look_for_in_url,'/').'/?$' => 'index.php?'.$this->query_var_name($look_for_in_url).'=1'];
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

                    $wp_query->is_home = false;

                    $params = null;

                    preg_match('#'.trim($url_key,'/').'#',$_SERVER['REQUEST_URI'],$params);

                    $res = call_user_func_array($callback,$params);

                    if($res === false)
                        $this->send_404();
                    else{
                        exit();
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
  
    protected function query_var_name($rewrite) {

        static $cache;

        if (!isset($cache[$rewrite])) {
            $cache[$rewrite] = md5($rewrite);
        }

        return $cache[$rewrite]; 
    }

    protected function send_404() {

        global $wp_query;

        status_header('404');

        $wp_query->set_404();
    }
}
