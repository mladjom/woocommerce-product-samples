<?php
// Add to our admin_init function
add_filter('manage_product_posts_columns', 'wcs_add_post_columns');

function wcs_add_post_columns($columns) {
    $columns['sample_enabled'] = 'Sample Enabled';
    return $columns;
}

// Make column sortable
add_filter('manage_edit-product_sortable_columns', 'wcs_filter_post_columns');

function wcs_filter_post_columns($sortable_columns) {

// Let's  make tour column sortable
    $sortable_columns['sample_enabled'] = '_wcs_sample_enable';
    return $sortable_columns;
}

// Fill our new column with the relevant meta-data values
add_action('manage_product_posts_custom_column', 'wcs_render_post_columns', 10, 2);

function wcs_render_post_columns($column_name, $post_id) {
    switch ($column_name) {
// show sample enabled
        case 'sample_enabled':
            echo '<div id="_wcs_sample_enable-' . $post_id . '">' . get_post_meta($post_id, '_wcs_sample_enable', true) . '</div>';
            break;
    }
}

//add_action('pre_get_posts', 'wcs_qe_pre_get_posts', 1);

function wcs_qe_pre_get_posts($query) {
    /**
     * We only want our code to run in the main WP query
     * AND if an orderby query variable is designated.
     */
    if ($query->is_main_query() && ( $orderby = $query->get('orderby') )) {
        switch ($orderby) {

            case 'sample_enabled':
// set our query's meta_key, which is used for custom fields
                $query->set('meta_key', '_wcs_sample_enable');
                /**
                 * Tell the query to order by our custom field/meta_key's
                 *
                 * If your meta value are numbers, change
                 * 'meta_value' to 'meta_value_num'.
                 */
                $query->set('orderby', 'meta_value');
                break;
        }
    }
}

//add_filter('posts_clauses', 'manage_wp_posts_be_qe_posts_clauses', 1, 2);

function manage_wp_posts_be_qe_posts_clauses($pieces, $query) {
    global $wpdb;
    /**
     * We only want our code to run in the main WP query
     * AND if an orderby query variable is designated.
     */
    if ($query->is_main_query() && ( $orderby = $query->get('orderby') )) {
// Get the order query variable - ASC or DESC
        $order = strtoupper($query->get('order'));
// Make sure the order setting qualifies. If not, set default as ASC
        if (!in_array($order, array('ASC', 'DESC')))
            $order = 'ASC';
        switch ($orderby) {
// If we're ordering by release_date
            case 'sample_enabled':
                /**
                 * We have to join the postmeta table to include
                 * our release date in the query.
                 */
                $pieces['join'] .= " LEFT JOIN $wpdb->postmeta wp_rd ON wp_rd.post_id = {$wpdb->posts}.ID AND wp_rd.meta_key = '_wcs_sample_enable'";
// Then tell the query to order by our date
                $pieces['orderby'] = "STR_TO_DATE( wp_rd.meta_value,'%m/%d/%Y' ) $order, " . $pieces['orderby'];
                break;
        }
    }
    return $pieces;
}

add_action('bulk_edit_custom_box', 'manage_wp_posts_be_qe_bulk_quick_edit_custom_box', 10, 2);
add_action('quick_edit_custom_box', 'manage_wp_posts_be_qe_bulk_quick_edit_custom_box', 10, 2);

function manage_wp_posts_be_qe_bulk_quick_edit_custom_box($column_name, $post_type) {
    switch ($post_type) {
        case 'product':
            switch ($column_name) {

                case 'sample_enabled':
                    ?><fieldset class="inline-edit-col-left">
                        <label>
                            <span class="title"><?php _e('Sample Enabled', 'wcs'); ?></span>
                            <span class="input-text-wrap">
                                <select class="_wcs_sample_enable" name="_wcs_sample_enable">
                                    <?php
                                    $options = array(
                                        '' => __('— No Change —', 'wcs'),
                                        'yes' => __('Yes', 'wcs'),
                                        'no' => __('No', 'wcs')
                                    );
                                    foreach ($options as $key => $value) {
                                        echo '<option value="' . esc_attr($key) . '">' . $value . '</option>';
                                    }
                                    ?>
                                </select>
                            </span>
                        </label>
                    </fieldset><?php
                    break;
            }
    }
}

add_action('admin_print_scripts-edit.php', 'manage_wp_posts_be_qe_enqueue_admin_scripts');

function manage_wp_posts_be_qe_enqueue_admin_scripts() {
// if code is in theme functions.php file
//wp_enqueue_script( 'manage-wp-posts-using-bulk-quick-edit', trailingslashit( get_bloginfo( 'stylesheet_directory' ) ) . 'bulk_quick_edit.js', array( 'jquery', 'inline-edit-post' ), '', true );
// if using code as plugin
    wp_enqueue_script('manage-wp-posts-using-bulk-quick-edit', trailingslashit(plugin_dir_url(__FILE__)) . 'bulk_quick_edit.js', array('jquery', 'inline-edit-post'), '', true);
}

/**
 * Saving your 'Quick Edit' data is exactly like saving custom data
 * when editing a post, using the 'save_post' hook. With that said,
 * you may have already set this up. If you're not sure, and your
 * 'Quick Edit' data is not saving, odds are you need to hook into
 * the 'save_post' action.
 *
 * The 'save_post' action passes 2 arguments: the $post_id (an integer)
 * and the $post information (an object).
 */
add_action('save_post', 'manage_wp_posts_be_qe_save_post', 10, 2);

function manage_wp_posts_be_qe_save_post($post_id, $post) {
// pointless if $_POST is empty (this happens on bulk edit)
    if (empty($_POST))
        return $post_id;
// verify quick edit nonce
    if (isset($_POST['_inline_edit']) && !wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce'))
        return $post_id;
// don't save for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;
// dont save for revisions
    if (isset($post->post_type) && $post->post_type == 'revision')
        return $post_id;
    switch ($post->post_type) {
        case 'product':
            /**
             * Because this action is run in several places, checking for the array key
             * keeps WordPress from editing data that wasn't in the form, i.e. if you had
             * this post meta on your "Quick Edit" but didn't have it on the "Edit Post" screen.
             */
            $custom_fields = array('_wcs_sample_enable');
            foreach ($custom_fields as $field) {
                if (array_key_exists($field, $_POST))
                    update_post_meta($post_id, $field, $_POST[$field]);
            }
            break;
    }
}

/**
 * Saving the 'Bulk Edit' data is a little trickier because we have
 * to get JavaScript involved. WordPress saves their bulk edit data
 * via AJAX so, guess what, so do we.
 *
 * Your javascript will run an AJAX function to save your data.
 * This is the WordPress AJAX function that will handle and save your data.
 */
add_action('wp_ajax_manage_wp_posts_using_bulk_quick_save_bulk_edit', 'manage_wp_posts_using_bulk_quick_save_bulk_edit');

function manage_wp_posts_using_bulk_quick_save_bulk_edit() {
// we need the post IDs
    $post_ids = ( isset($_POST['post_ids']) && !empty($_POST['post_ids']) ) ? $_POST['post_ids'] : NULL;
    // if we have post IDs
    if (!empty($post_ids) && is_array($post_ids)) {
// get the custom fields
        $custom_fields = array('_wcs_sample_enable',);
        foreach ($custom_fields as $field) {
// if it has a value, doesn't update if empty on bulk
            if (isset($_POST[$field]) && !empty($_POST[$field])) {
// update for each post ID
                foreach ($post_ids as $post_id) {
                    update_post_meta($post_id, $field, $_POST[$field]);
                }
            }
        }
    }
}
