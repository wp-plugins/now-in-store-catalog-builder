<?php

class NowInStore_CatalogBuilder
{
    public function __construct()
    {
      add_filter( 'plugin_action_links_'. plugin_basename( __FILE__ ), array($this,'plugin_action_links') );
      add_action( 'init', array($this,'add_rewrite_rules'));
      add_filter( 'query_vars', array($this,'add_query_vars' ));
      register_activation_hook( __FILE__, array($this,'wpa_install') );
      register_deactivation_hook( __FILE__, array($this,'wpa_uninstall')  );
      add_action( 'template_redirect', array($this,'add_template_redirect'));
    }

    public function plugin_action_links( $actions )
    {
      $baseUrl = urlendcode (get_site_url().'/');
    	$actions[] = '<a href="https://www.nowinstore.com/auth/woocommerce/callback?baseUrl='.$baseUrl.'">Open</a>';
    	return $actions;
    }

    public function add_rewrite_rules() {
      // add_rewrite_endpoint('nowinstore', EP_PERMALINK | EP_PAGES );
      add_rewrite_rule('index.php/catalogbuilder/(.+?)/(.+?)?$', 'index.php?nowinstore_resource=$matches[1]&action=$matches[2]', 'top');
      add_rewrite_rule('index.php/catalogbuilder/(.+?)?$', 'index.php?nowinstore_resource=$matches[1]', 'top');
           flush_rewrite_rules();
    }

    public function add_query_vars($vars){
        $vars[] = "nowinstore_resource";
        $vars[] = "page";
        $vars[] = "category_id";
        $vars[] = "keywords";
        $vars[] = "action";
        return $vars;
    }

    public function wpa_install() {
         flush_rewrite_rules();
    }

    public function wpa_uninstall() {
      flush_rewrite_rules();
    }

    public function add_template_redirect() {

      $page = (get_query_var('page')) ? get_query_var('page') : 1;
      	if ( get_query_var( 'nowinstore_resource' ) == 'products' ) {
    	    header( 'Content-Type: application/json' );

          $args = array( 'post_type' => 'product', 'posts_per_page' => 50,
            'orderby' => 'title', 'order'   => 'ASC', 'paged' => $page );
            if (get_query_var('category_id')) {
              $cat = get_term( get_query_var('category_id'), 'product_cat' );
              $args["product_cat"] = $cat->slug;
            }

            if (get_query_var('keywords')) {
              $args["s"] = get_query_var('keywords');
            }
          $loop = new WP_Query( $args );


          if (get_query_var('action') == 'count') {
            echo json_encode(array("count" => intval($loop->found_posts)));
            exit;
          } else {

            $iso_currency_code = get_woocommerce_currency();
            $products = [];
            while ( $loop->have_posts() ) : $loop->the_post();
            global $product;
            // $attributes = $product->get_attributes();
            // $variations = $product->get_available_variations();
            // $formatted_attributes = array();
            // foreach($attributes as $attr=>$attr_deets){
            //
            //     $attribute_label = wc_attribute_label($attr);
            //
            //     if ( isset( $attributes[ $attr ] ) || isset( $attributes[ 'pa_' . $attr ] ) ) {
            //
            //         $attribute = isset( $attributes[ $attr ] ) ? $attributes[ $attr ] : $attributes[ 'pa_' . $attr ];
            //
            //         if ( $attribute['is_taxonomy'] ) {
            //
            //             $formatted_attributes[$attribute_label] = implode( ', ', wc_get_product_terms( $product->id, $attribute['name'], array( 'fields' => 'names' ) ) );
            //
            //         } else {
            //
            //             $formatted_attributes[$attribute_label] = $attribute['value'];
            //         }
            //
            //     }
            // }
            array_push($products, [
                    "id" => get_the_ID(),
                    "title" => $product->get_title(),
                    "sku" => $product->get_sku(),
                    "price" => $product->get_price(),
                    "main_image" =>  wp_get_attachment_image_src( $product->get_image_id(), "full" )[0],
                    "thumbnail_image" =>  wp_get_attachment_image_src( $product->get_image_id(), array(75,75) )[0],
                    "iso_currency_code" => $iso_currency_code,
                    "url" => get_permalink(),
                    "variations" => $formatted_attributes
            ]);
            endwhile;

            wp_reset_query();
            echo json_encode($products);
            exit;
        }
      } else if ( get_query_var( 'nowinstore_resource' ) == 'categories' ) {
        header( 'Content-Type: application/json' );
        $categories = [];
        $product_categories = get_terms( 'product_cat', array('orderby' => 'name', 'order'   => 'ASC', 'hide_empty' => true) );
        foreach($product_categories as $product_category) {
          if ($product_category->parent != 0) {
            array_push($categories, [
                    "id" => $product_category->term_id,
                    "title" => $product_category->name
            ]);
          }
        }
        echo json_encode($product_categories);
        exit;
      } else if ( get_query_var( 'nowinstore_resource' ) == 'profile' ) {
        header( 'Content-Type: application/json' );
        echo json_encode(array(
                "business_name" => get_option('blogname'),
                "email" => get_option('admin_email'),
                "baseUrl" =>  get_site_url().'/'
        ));
        exit;
      }
    }
}



?>
