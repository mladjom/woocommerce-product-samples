<?php

/*
  Plugin Name: WooCommerce Product Samples
  Plugin URI: http://milentijevic.com/wordpress-plugins/wocommerce-product-samples/
  Version: 0.3.0
  Description: Sell or Give Samples of WooCommerce Products.
  Author: Mladjo
  Author URI: http://milentijevic.com
  Text Domain: wcs
  Domain Path: /languages/

  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
// If this file is called directly, abort.
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Check if WooCommerce is active
 * */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {


    /**
     * New class
     * */
    if (!class_exists('WC_Samples')) {

        class WC_Samples {

            public static function getInstance() {
                static $_instance;
                if (!$_instance) {
                    $_instance = new WC_Samples();
                }
                return $_instance;
            }

            /**
             * Construct the plugin.
             */
            public function __construct() {
                add_action('init', array(&$this, 'init'));
                add_action('plugins_loaded', array(&$this, 'load_localisation'));
                // Include required files
                $this->includes();
            }

            /**
             * Initialize the plugin.
             */
            public function init() {
                add_filter('get_terms', array(&$this,'get_subcategory_terms'), 10, 3);
                add_action('pre_get_posts', array(&$this, 'custom_pre_get_posts_query'));
                $this->multiple_select_categories();
            }
	/**
	 * Returns the upsell product ids.
	 *
	 * @return array
	 */
	public function get_upsells() {
		return (array) maybe_unserialize( $this->upsell_ids );
	}
            /**
             * load_localisation function.
             *
             * @access public
             * @since 1.0.0
             * @return void
             */
            public function load_localisation() {
                load_plugin_textdomain('wcs', false, dirname(plugin_basename(__FILE__)) . '/languages');
            }

            // End load_localisation()

            /**
             * Include required core files used in admin and on the frontend.
             */
            private function includes() {
                include_once( 'admin/writepanel-product_data.php' );
                include_once( 'admin/class-wcs-admin-settings.php' );
                //include_once( 'admin/quick-edit.php' );
                include_once( 'templates/sample-product.php' );
                include_once( 'frontend/frontend.php' );
            }

            // Exclude Categories from Shop
            public function get_subcategory_terms($terms, $taxonomies, $args) {

                $new_terms = array();

                // if a product category and on the shop page
                if (in_array('product_cat', $taxonomies) && !is_admin() && is_shop()) {

                    foreach ($terms as $key => $term) {

                        if (!in_array($term->slug, get_option('wcs_chosen_categories'))) {
                            $new_terms[] = $term;
                        }
                    }

                    $terms = $new_terms;
                }

                return $terms;
            }

            //Exclude products from a particular category on the shop page
            public function custom_pre_get_posts_query($q) {
                if (!$q->is_main_query())
                    return;
                if (!$q->is_post_type_archive())
                    return;
                if (!is_admin() && is_shop()) {
                    $q->set('tax_query', array(array(
                            'taxonomy' => 'product_cat',
                            'field' => 'slug',
                            'terms' => get_option('wcs_chosen_categories'), // Don't display products in the categories on the shop page
                            'operator' => 'NOT IN'
                    )));
                }
                remove_action('pre_get_posts', 'custom_pre_get_posts_query');
            }

            public static function multiple_select_categories() {
                $category_list_items = get_terms('product_cat');
                if ($category_list_items) {
                    $slugs = array();
                    $names = array();
                    foreach ($category_list_items as $category_list_item) {
                        if (!empty($category_list_item->slug)) {
                            $slugs[] = $category_list_item->slug;
                            $names[] = $category_list_item->name;
                        }
                    }
                    return $categories = array_combine($slugs, $names);
                }
            }

        }

        WC_Samples::getInstance();
    }
}
                         