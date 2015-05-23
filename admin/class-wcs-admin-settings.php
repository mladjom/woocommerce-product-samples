<?php

/**
 * WooCommerce Product Samples Admin Settings

 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WCS Admin Settings class.
 */
class WCS_Admin_Settings {

    /**
     * Initialize the admin settings actions.
     */
    public function __construct() {
        add_filter('woocommerce_settings_tabs_array', array(&$this, 'add_settings_tab'), 50);
        add_action('woocommerce_settings_tabs_settings_tab_wcs', array(&$this, 'settings_tab'));
        add_action('woocommerce_update_options_settings_tab_wcs', array(&$this, 'update_settings'));
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
                'options' => WC_Samples::multiple_select_categories(),
            ),
            'wcs_add_to_cart_button_text' => array(
                'title' => __('Add to Cart Button Text', 'wcs'),
                'desc' => __('This controls the add to cart button text on single product pages for products that have sample enabled.', 'wcs'),
                'desc_tip' => true,
                'id' => 'wcs_add_to_cart_button_text',
                'default' => __('Add Sample to cart', 'wcs'),
                'type' => 'text',
            ),
            'wcs_button_position' => array(
                'title' => __('Button position', 'wcs'),
                'desc' => __('This option lets you choose button position.', 'wcs'),
                'id' => 'wcs_button_position',
                'default' => '31',
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'css' => 'min-width: 350px;',
                'desc_tip' => true,
                'options' => array(
                    '6' => __('After Title', 'wcs'),
                    '11' => __('After Price', 'wcs'),
                    '21' => __('After Excerpt', 'wcs'),
                    '31' => __('After Add to cart', 'wcs'),
                    '41' => __('After Meta', 'wcs'),
                )
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

}

new WCS_Admin_Settings();
