<?php

namespace TheFold\WordPress;

class Dispatch {

    static function routes(array $url_callbacks, $priority = 5) {

        add_filter('rewrite_rules_array', function($rules) use ($url_callbacks) {
            return array_reduce( array_keys($url_callbacks), function($rules, $route) {
                $newRule = ['^'.trim($route,'/').'/?$' => 'index.php?'.static::query_var_name($route).'=1'];
                return $newRule + $rules;
            }, $rules );
        });

        add_filter('query_vars', function($qvars) use ($url_callbacks) {
            return array_reduce(array_keys($url_callbacks), function($qvars, $route) {
                $qvars[] = static::query_var_name($route);
                return $qvars;
            },$qvars);
        });

        add_action( 'template_redirect', function() use ($url_callbacks) {
            global $wp_query;
            foreach ($url_callbacks as $route => $callback) {
                if ($wp_query->get( static::query_var_name($route))) {
                    $wp_query->is_home = false;
                    $params = null;
                    preg_match('#'.trim($route,'/').'#',$_SERVER['REQUEST_URI'],$params);
                    $res = call_user_func_array($callback,$params);
                    if($res === false) {
                        static::send_404();
                    } else {
                        exit();
                    }
                }
            }
        }, get_option('thefold/router-priority',$priority) );

        add_action('init', function() use ($url_callbacks) {
            static::maybe_flush_rewrites($url_callbacks);
        }, 99);
    }

    static protected function maybe_flush_rewrites($url_callbacks) {
        $current = md5(json_encode(array_keys($url_callbacks)));
        $cached = get_option(get_called_class(), null );
        if ( empty( $cached ) ||  $current !== $cached ) {
            flush_rewrite_rules();
            update_option(get_called_class(), $current );
        }
    }

    static protected function query_var_name($route) {
        static $cache;
        if (!isset($cache[$route])) {
            $cache[$route] = md5($route);
        }
        return $cache[$route];
    }

    static protected function send_404() {
        global $wp_query;
        status_header('404');
        $wp_query->set_404();
    }
}
