<?php

/**
 * The following hook will validate the name field
 */
//add_action('woocommerce_add_to_cart_validation', 'wcs_samples_name_validation', 1, 5);

function wcs_samples_name_validation() {
    if (empty($_REQUEST['wcs-specimen-parent'])) {
        wc_add_notice(__('Something went terribly wrong', 'wcs'), 'error');
        return false;
    }
    return true;
}

/**
 * This code will store the custom fields ( for the product that is being added to cart ) 
 * into session associated with cart item key.
 */
add_action('woocommerce_add_to_cart', 'save_name_on_parent_field', 1, 5);

function save_name_on_parent_field($cart_item_key, $product_id = null, $quantity = null, $variation_id = null, $variation = null) {
    if (!empty($_REQUEST['wcs-specimen-parent'])) {
        WC()->session->set($cart_item_key . '_name_of_parent', $_REQUEST['wcs-specimen-parent']);
    }

}
/**
 * The following hook will render the custom data in your cart page.
 */
add_filter('woocommerce_cart_item_name', 'render_meta_on_cart_item', 1, 3);

function render_meta_on_cart_item($title = null, $cart_item = null, $cart_item_key = null) {
    if ($cart_item_key && is_cart()) {
        echo $title . '<dl class="">
				 <dd class=""><p>' . WC()->session->get($cart_item_key . '_name_of_parent') . '</p></dd>
			  </dl>';
    } else {
        return $title;
    }
}

/**
 * The following hook will render the custom data in your checkout page ( order review section ).
 */
add_filter('woocommerce_checkout_cart_item_quantity', 'render_meta_on_checkout_order_review_item', 1, 3);

function render_meta_on_checkout_order_review_item($quantity = null, $cart_item = null, $cart_item_key = null) {
    if ($cart_item_key) {
        echo $quantity . '<dl class="">
				 <dd class=""><p>' . WC()->session->get($cart_item_key . '_name_of_parent') . '</p></dd>
			  </dl>';
    }
}

/**
 * The following hook will add your custom field with order meta.
 */
add_action('woocommerce_add_order_item_meta', 'parent_name_order_meta_handler', 1, 3);

function parent_name_order_meta_handler($item_id, $values, $cart_item_key) {
    wc_add_order_item_meta($item_id, "name_of_parent", WC()->session->get($cart_item_key . '_name_of_parent'));
}

/**
 * The following hook will force Woocommerce to treat each Add to Cart action as unique.
 */
add_filter('woocommerce_add_cart_item_data', 'parent_name_force_individual_cart_items', 10, 2);

function parent_name_force_individual_cart_items($cart_item_data, $product_id) {
    $unique_cart_item_key = md5(microtime() . rand());
    $cart_item_data['unique_key'] = $unique_cart_item_key;

    return $cart_item_data;
}

?>
