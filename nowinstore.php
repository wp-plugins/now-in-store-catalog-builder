<?php
   /*
   Plugin Name: WooCommerce - Now In Store Catalog Builder
   Plugin URI: http://www.nowinstore.com
   Description: Create beautiful product catalogs that you can print or share online.
   Version: 1.0.1
   Author: Now In Store Inc.
   Author URI: https://www.nowinstore.com
   License: GPL2
   */
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   require_once dirname(__FILE__) . '/WP_NowInStore_Init.php';
   function plugin_action_links( $actions )
    {
      $baseUrl = urlencode (get_site_url().'/');
      $actions[] = '<a href="https://www.nowinstore.com/auth/woocommerce/callback?baseUrl='.$baseUrl.'" target="_blank">Open</a>';
      return $actions;
    }
    add_filter( 'plugin_action_links_'. plugin_basename( __FILE__ ), 'plugin_action_links' );

   new NowInStore_CatalogBuilder();

?>
