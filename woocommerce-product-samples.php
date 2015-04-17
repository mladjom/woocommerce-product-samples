<?php
/*
  Plugin Name: WooCommerce Product Samples
  Plugin URI: http://milentijevic.com/wordpress-plugins/wocommerce-product-samples/
  Version: 0.2.2
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
                add_filter('woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50);
                add_action('woocommerce_settings_tabs_settings_tab_wcs', __CLASS__ . '::settings_tab');
                add_action('woocommerce_update_options_settings_tab_wcs', __CLASS__ . '::update_settings');
                add_action('pre_get_posts', array(&$this, 'custom_pre_get_posts_query'));
                $this->multiple_select_categories();
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
                //include_once( 'admin/quick-edit.php' );
                include_once( 'templates/sample-product.php' );
                include_once( 'frontend/frontend.php' );
            }

            /**
             * Add a new settings tab to the WooCommerce settings tabs array.
             *
             * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
             * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
             */
            public static function add_settings_tab($settings_tabs) {
                $settings_tabs['settings_tab_wcs'] = __('Product Samples', 'woocommerce-settings-tab-wcs');
                return $settings_tabs;
            }

            /**
             * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
             *
             * @uses woocommerce_admin_fields()
             * @uses self::get_settings()
             */
            public static function settings_tab() {

//                	if ( ! class_exists( 'WC_Admin_Settings' ) )
//		include 'class-wc-admin-settings.php';
//
//	WC_Admin_Settings::output_fields( $options );


                woocommerce_admin_fields(self::get_settings());
            }

            /**
             * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
             *
             * @uses woocommerce_update_options()
             * @uses self::get_settings()
             */
            public static function update_settings() {
                woocommerce_update_options(self::get_settings());
            }

            /**
             * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
             *
             * @return array Array of settings for @see woocommerce_admin_fields() function.
             */
            public static function get_settings() {
                $settings = array(
                    'section_title' => array(
                        'name' => __('Samples', 'wcs'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'wcs_title'
                    ),
                    'wcs_id' => array(
                        'name' => __('Sample Product ID', 'wcs'),
                        'type' => 'text',
                        'desc' => __('Write Sample Product ID', 'wcs'),
                        'id' => 'wcs_id'
                    ),
                    'wcs_categories' => array(
                        'title' => __('Product Categories', 'wcs'),
                        'desc' => __('Select categories to exclude from main loop.', 'wcs'),
                        'id' => 'wcs_chosen_categories',
                        'default' => 'all',
                        'type' => 'multiselect',
                        'class' => 'chosen_select',
                        'css' => 'min-width: 350px;',
                        'desc_tip' => true,
                        'options' => self::multiple_select_categories(),
                    ),
                    'wcs_custom_css' => array(
                        'title' => __('Custom CSS', 'wcs'),
                        'type' => 'textarea',
                        'id' => 'wcs_custom_css',
                        'css' => 'width:50%; height: 75px;',
                        'desc' => __('Apply your own custom CSS. CSS is automatically wrapped with <style></style> tags', 'wcs'),
                        'default' => __('.sample-products {margin-top: 20px;}', 'wcs'),
                        'desc_tip' => true,
                    ),
                    'section_end' => array(
                        'type' => 'sectionend',
                        'id' => 'wcs_end'
                    )
                );
                return apply_filters('wc_settings_tab_wcs_settings', $settings);
            }

            //Exclude products from a particular category on the shop page
            function custom_pre_get_posts_query($q) {
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
                         