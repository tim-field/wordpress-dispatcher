<?php

namespace TheFold\WordPress;

class Render {

    static function template($slug, $name = null, array $view_params=[], $return=false, $default_path=null)
    {
        if(is_array($slug) && func_num_args()===1) {
            //Alternative params passed as single array
            extract($slug);
        }

        if($view_params) {
            // Don't permantly overwrite existing query vars

            global $wp_query;
            $old_globals = [];
            
            foreach($view_params as $key => $value){

                if($existing = $wp_query->get($key,null)){
                    $old_globals[$key] = $existing;
                }

                $wp_query->set($key, $value);
            }
        }

        if($return) ob_start();

        if($default_path) {
            //Default to loading from this location if not found

            $templates = [];
            $name = (string) $name;
            if ( '' !== $name )
                $templates[] = "{$slug}-{$name}.php";

            $templates[] = "{$slug}.php";

            if(!locate_template($templates,true,false) ){
                load_template($default_path.'.php',false);
            }
        
        } else {

            get_template_part($slug, $name);
        }

        if(isset($old_globals)){
            //Reset any query vars ( global variables ) back to how they were
            foreach($old_globals as $key => $old_value){
                $wp_query->set($key, $old_value);
            }
        }

        if($return) {
            return ob_get_clean();
        }
    }
   
    static function render_page($slug, array $view_params=[],$layout='layouts/default',$return=false)
    {
        if(is_array($slug) && func_num_args()===1) {
            //Alternative params passed as single array
            extract($slug);
        }

        return static::render_template([
            'slug' => $layout,
            'view_params' => array_merge(
                ['content_for_layout' => static::render_template($slug,null,$view_params,true)],
                $view_params
            ),
            'return' => $return
        ]);
    }
}
