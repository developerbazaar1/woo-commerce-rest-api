<?php
/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */

require get_template_directory() . '/inc/init.php';


/**
 * Note: It's not recommended to add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * Learn more here: http://codex.wordpress.org/Child_Themes
 */






if( function_exists('acf_add_options_page') ) {

    acf_add_options_page(array(
        'page_title'    => 'API Settings',
        'menu_title'    => 'API Settings',
        'menu_slug'     => 'api-general-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));

    acf_add_options_sub_page(array(
        'page_title'    => 'Banner Settings',
        'menu_title'    => 'Banner Section',
        'parent_slug'   => 'api-general-settings',
    ));

   
}



 
function banner_settings() {
    $labels = array(
        'name'                => _x( 'Banner', 'Post Type General Name', 'twentytwenty' ),
        'singular_name'       => _x( 'Banner', 'Post Type Singular Name', 'twentytwenty' ),
        'menu_name'           => __( 'Banner', 'twentytwenty' ),
        'parent_item_colon'   => __( 'Parent Banner', 'twentytwenty' ),
        'all_items'           => __( 'All Banner', 'twentytwenty' ),
        'view_item'           => __( 'View Banner', 'twentytwenty' ),
        'add_new_item'        => __( 'Add New Mobile Banner', 'twentytwenty' ),
        'add_new'             => __( 'Add New Mobile Banner', 'twentytwenty' ),
        'edit_item'           => __( 'Edit Mobile Banner', 'twentytwenty' ),
        'update_item'         => __( 'Update Mobile Banner', 'twentytwenty' ),
        'search_items'        => __( 'Search Mobile Banner', 'twentytwenty' ),
        'not_found'           => __( 'Not Found', 'twentytwenty' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwenty' ),

    );
     
     
    $args = array(
        'label'               => __( 'Banner', 'twentytwenty' ),
        'description'         => __( 'Description for Banner', 'twentytwenty' ),
        'labels'              => $labels,
     
        'supports'            => array( 'title', 'thumbnail', 'custom-fields'),
       
       'taxonomies'          => array('geners' ),
        
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
        'menu_icon'           => 'dashicons-format-image',

 
    );
     
    register_post_type( 'Banner', $args );
 
}

add_action( 'init', 'banner_settings', 0 );




/*status*/ 


add_action( 'woocommerce_order_status_changed', 'grab_order_old_status', 10, 4 );
function grab_order_old_status( $order_id, $status_from, $status_to, $order ) {
    if ( $order->get_meta('_old_status') ) {
        // Grab order status before it's updated
        update_post_meta( $order_id, '_old_status', $status_from );
    } else {
        // Starting status in Woocommerce (empty history)
        update_post_meta( $order_id, '_old_status', 'pending' );
    }
}

 

/*status*/ 

