<?php

/*
Plugin Name: My Custom API
Description: This plugin is developed to test multiple API's with this single plugin, Just activate the plugin and test your API's.
Version: 2.0
Requires at least: 5.0
Requires PHP: 5.2
Author: Gagan Verma - Developer Bazaar Technologies.
Author URI: developerbazaar.com
License: GPLv2 or later
*/




// API to show categories
function custom_api_endpoint_init() {
    register_rest_route('custom/v1', '/categories/', array(
        'methods' => 'GET',
        'callback' => 'custom_api_callback'
    ));
}
add_action('rest_api_init', 'custom_api_endpoint_init');

function custom_api_callback($request) {

    $orderby = 'name';
    $order = 'asc';
    $hide_empty = false ;
    $cat_args = array(
        'orderby'    => $orderby,
        'order'      => $order,
        'hide_empty' => $hide_empty,
    );
    $product_categories = get_terms( 'product_cat', $cat_args );
    if( !empty($product_categories) ){
         $categoryNameArray = array();
                foreach ($product_categories as $key => $category) {
           
                array_push($categoryNameArray, $category->name);
        }       
}

    // Your custom logic to fetch and return data

    $response['status'] = true;
    $response['code'] = 200;
    $response['message'] = 'Success';
    $response['data'] = $categoryNameArray;
    
    // $json_response = json_encode($response);
    
    return rest_ensure_response($response);

}
// END of API to show categories


/**********************************************************************/

// API to show all products
function custom_api_show_all_product() {
    register_rest_route('custom/v1', '/allproducts/', array(
        'methods' => 'GET',
        'callback' => 'custom_api_show_all_product_callback'
    ));
}
add_action('rest_api_init', 'custom_api_show_all_product');

function custom_api_show_all_product_callback($request) {

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 1
    );
    $loop = new WP_Query( $args );

    $a = array();
    if ( $loop->have_posts() ): while ( $loop->have_posts() ): $loop->the_post();

        global $product;
        //print_r($product);die();
      
        $product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );

    
        $product_cats = wp_get_post_terms($product->id, 'product_cat');

        $cat_array = array();
        foreach($product_cats as $key => $cat)
            {
               
                array_push($cat_array, $cat->name);
                
            }  


        $pro_single = array(
            'name' => $product->name,
            'price' => $product->price,
            'sku' => $product->sku,
            'image' => $product_image,
            'category' => $cat_array,

        ); 
array_push($a, $pro_single);
          
    endwhile; endif; wp_reset_postdata();
   
    $response['status'] = true;
    $response['code'] = 200;
    $response['message'] = 'Success';
    $response['data'] = $a;
    
    // $json_response = json_encode($response);
    
    return rest_ensure_response($response);


}
// END of API to show all products


/**********************************************************************/

// API to show product Description
function custom_api_product_desc() {
    register_rest_route('custom/v1', '/productdesc/', array(
        'methods' => 'GET',
        'callback' => 'custom_api_product_desc_callback'
    ));
}
add_action('rest_api_init', 'custom_api_product_desc');

function custom_api_product_desc_callback($request) {

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1
    );
    $loop = new WP_Query( $args );

    $a = array();
    if ( $loop->have_posts() ): while ( $loop->have_posts() ): $loop->the_post();

        global $product;

        $pro_single = array(
            'name' => $product->name,
            'description' => $product->description,
            

        ); 
array_push($a, $pro_single);
          
    endwhile; endif; wp_reset_postdata();
   
    $response['status'] = true;
    $response['code'] = 200;
    $response['message'] = 'Success';
    $response['data'] = $a;
    
    // $json_response = json_encode($response); 
    
    return rest_ensure_response($response);


}
// END of API to show product Description


// API to show category wise product
add_action( 'rest_api_init', function () {
 register_rest_route( 'productfiltercat/v1', '/product/category/(?P<slug>[^/]+)', array(
    'methods' => 'GET',
    'callback' => 'therich_func',
  ) );
} );
function therich_func( $data ) {

  $p = wc_get_products(array('status' => 'publish', 'posts_per_page' => -1, 'category' => $data['slug']));
     $products = array();

     foreach ($p as $product) {

    $products[] = $product->get_data();

     }

     return new WP_REST_Response($products, 200);
}
// END of API to show category wise product
 



// -------------------------------------
// -------------  pk start  --------------

// api for filter products

add_action('rest_api_init', 'register_woocommerce_filter_api');

function register_woocommerce_filter_api() {
    register_rest_route('wp/v2', '/products', array(
        'methods' => 'GET',
        'callback' => 'get_filtered_products',
    ));
}

function get_filtered_products($request) {
    // Get query parameters from the request
    $params = $request->get_params();
    
    if (isset($params['category'])) {
        $category1 = $params['category'];
    }else{
        $category1 = '';
    }
    
    
    if (isset($params['store_id'])) {
        $store_id1 = $params['store_id'];
    }else{
        $store_id1 = '';
    }
    
    // Process and filter the products based on your criteria
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'category' => $category1,
        'author' => $store_id1,
         // Adjust the number of products per page as needed
        // Add more conditions based on your filtering requirements
        // 'orderby' => 'date', // Default sorting by latest
        // 'order' => 'desc', // Default order is descending (latest first)

    );


    // $category_id = $request->get_param('category_id');
    // if (isset($params['category'])) {
    //     $args = array(
    //         'category' => $params['category'],
    //     );
    // }
    // Query WooCommerce products by category
    
    
    // Apply additional filters based on parameters
    if (isset($params['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $params['category'],
            ),
        );
    }
    
     if (isset($params['min_price']) && isset($params['max_price'])) {
        $args['meta_query'] = array(
            array(
                'key' => '_regular_price',
                'value' => array($params['min_price'], $params['max_price']),
                'type' => 'numeric',
                'compare' => 'BETWEEN',
            ),
        );
    }
    
    if (isset($params['sort']) && $params['sort'] === 'latest') {
        $args['orderby'] = 'date';
        $args['order'] = 'desc';
    }
    
    // Add sorting by latest if specified
    if (isset($params['sort']) && $params['sort'] === 'latest') {
        $args['orderby'] = 'date';
        $args['order'] = 'desc';
    }
    
    // Add sorting by price high to low if specified
    if (isset($params['sort']) && $params['sort'] === 'price_high_low') {
        $args['meta_key'] = '_regular_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'desc';
    }
    
    // Add sorting by price low to high if specified
    if (isset($params['sort']) && $params['sort'] === 'price_low_high') {
        $args['meta_key'] = '_regular_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'asc';
    }
    
    
    // Add sorting by popularity if specified
    if (isset($params['sort']) && $params['sort'] === 'popularity') {
        $args['meta_key'] = 'total_sales';
        $args['orderby'] = 'meta_value_num';
    }
    
    // Add search functionality if search term is provided
    if (isset($params['search']) && !empty($params['search'])) {
        $args['s'] = sanitize_text_field($params['search']);
    }
    
     // Pagination
    if (isset($params['page']) && is_numeric($params['page']) && $params['page'] > 0) {
        $args['paged'] = $params['page'];
    }

    // Number of products per page
    if (isset($params['per_page']) && is_numeric($params['per_page']) && $params['per_page'] > 0) {
        $args['posts_per_page'] = $params['per_page'];
    }
    
  


    // Run the query  wc_get_products
    $products = get_posts($args);   
    
    // Format and prepare the data to be sent in the API response
     // Format and prepare the data to be sent in the API response
    $data = array();
    foreach ($products as $product) {   
        $product_data = wc_get_product($product->ID);
        

        $product_images = array();
        $product_images[] = array(
            'src' => get_the_post_thumbnail_url($product->ID, 'full'),
            'alt' => get_post_meta($product->ID, '_wp_attachment_image_alt', true),
        );

        // Fetch gallery images if available
        $gallery_images = array();
        $gallery_ids = $product_data->get_gallery_image_ids();
        foreach ($gallery_ids as $gallery_id) {
            $gallery_images[] = array(
                'src' => wp_get_attachment_url($gallery_id),
                'alt' => get_post_meta($gallery_id, '_wp_attachment_image_alt', true),
            );
        }
        
        // Get product categories
        $product_categories = array();
        $categories = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'names'));
        foreach ($categories as $category) {
            $product_categories[] = $category;
        }
        
    
    // related ids fetch
    $related_products = wc_get_related_products( $product->ID, 10 );
    
    // vendor info
    $vendor_id = get_post_field('post_author', $product->ID); 
    $vendor = get_userdata( $vendor_id ); 
    $vendor = array(
        'vendor_email' => $vendor->user_email,
        'vendor_display_name' => $vendor->display_name,
        'vendor_user_login' => $vendor->user_login,
        'vendor_storename' => $vendor->user_nicename,
        );
    
    
    
    // shipping info
    // $product = wc_get_product($product->ID); 
    $shipping_data = $product_data->get_shipping_class();

        $data[] = array(
            'id' => $product->ID,
            'name' => $product->post_title,
            'description' => $product->post_content,
            'regular_price' => $product_data->get_regular_price(),
            'sale_price' => $product_data->get_sale_price(),
            'is_on_sale' => $product_data->is_on_sale(),
            'images' => $product_images,
            'gallery_images' => $gallery_images,
            'categories' => $product_categories,
            'related_ids' => $related_products,
            'vendor' => $vendor,
            'shipping' =>$shipping_data,
            // 'is_free_shipping' => $product->is_free_shipping(),
            // Add more fields you want to include in the response
        );
    }


    // Return the data as JSON
    return rest_ensure_response($data);
}

// user register
function custom_user_register_endpoint() {
    register_rest_route('wp/v2', '/user-register', array(
        'methods' => 'POST',
        'callback' => 'custom_user_register_callback',
    ));
}
add_action('rest_api_init', 'custom_user_register_endpoint');

function custom_user_register_callback($request) {
    $params = $request->get_json_params();
    $username = sanitize_text_field($params['username']);
    $email = sanitize_email($params['email']);
    $password = sanitize_text_field($params['password']);

    // Check if the username or email is already in use
    if (username_exists($username) || email_exists($email)) {
        return new WP_Error('registration_failed', __('Username or email already in use.', 'text-domain'), array('status' => 400));
    }

    // Create the user
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        return new WP_Error('registration_failed', __('User registration failed.', 'text-domain'), array('status' => 400));
    }

    // Generate and return a JWT token
    // $jwt_token = jwt_auth_generate_token($user_id);
    $request_args = array(
        'method' => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'username' => $email,
            'password' => $password,
        )),
    );

    // Make the API request
    $response = wp_safe_remote_request(
        rest_url('jwt-auth/v1/token'),
        $request_args
    );

    // Check for a successful response
    if (is_wp_error($response)) {
        // Handle error
    } else {
        $body = wp_remote_retrieve_body($response);
        $token_data = json_decode($body);   
        $jwt_token = $token_data->data->token; // Extract the token from the response
        
        $userdata = array(
            'token' =>$jwt_token,
            'id' =>$token_data->data->id,
            'display-name' =>$token_data->data->displayName,
            'email' =>$token_data->data->email,
           
        );
    }
    
    $expiration_time = time() + 43200; // Token will expire in 1 hour
    update_user_meta( $user_id, 'api_token', $jwt_token );
    update_user_meta( $user_id, 'api_token_expiration', $expiration_time );
    
    $response1['status'] = true;
    $response1['code'] = 200;
    $response1['message'] = 'Success';
    $response1['data'] = $userdata;

    return array($response1);
}

// user login api
function custom_user_login_endpoint() {
    register_rest_route('wp/v2', '/user-login', array(
        'methods' => 'POST',
        'callback' => 'custom_user_login_callback',
    ));
}
add_action('rest_api_init', 'custom_user_login_endpoint');

function custom_user_login_callback($request) {
    $params = $request->get_json_params();
    $username = sanitize_text_field($params['username']);
    $password = sanitize_text_field($params['password']);

    $user = wp_authenticate($username, $password);

    if (is_wp_error($user)) {
        return new WP_Error('authentication_failed', __('Invalid username or password.', 'text-domain'), array('status' => 401));
    }

    // Generate and return a JWT token
    $request_args = array(
        'method' => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'username' => $username,
            'password' => $password,
        )),
    );

    // Make the API request
    $response = wp_safe_remote_request(
        rest_url('jwt-auth/v1/token'),
        $request_args
    );

    // Check for a successful response
    if (is_wp_error($response)) {
        // Handle error
    } else {
        $body = wp_remote_retrieve_body($response);
        $token_data = json_decode($body);   
        $jwt_token = $token_data->data->token; // Extract the token from the response
        $user_id = $token_data->data->id;
        $userdata = array(
            'token' =>$jwt_token,
            'id' =>$user_id,
            'display-name' =>$token_data->data->displayName,
            'email' =>$token_data->data->email,
           
        );
    }
    
     // update the token and expiration time in user_meta
    $expiration_time = time() + 43200; // Token will expire in 1 hour
    update_user_meta( $user_id, 'api_token', $jwt_token );
    update_user_meta( $user_id, 'api_token_expiration', $expiration_time );
    
    
    $response1['status'] = true;
    $response1['code'] = 200;
    $response1['message'] = 'Success';
    $response1['data'] = $userdata;

    return array($response1);
}


function custom_user_order() {
    register_rest_route('wp/v2', '/user-order', array(
        'methods' => 'POST',
        'callback' => 'verify_api_token',
    ));
}
add_action('rest_api_init', 'custom_user_order');

function verify_api_token( $request ) {
    $headers = getallheaders();
    $api_token = isset( $headers['Authorization'] ) ? str_replace( 'Bearer ', '', $headers['Authorization'] ) : '';
    
    $user_id1 = 0; // Default value if the token doesn't match any user
    // Loop through all users and find the one with a matching token
    $users = get_users(['meta_key' => 'api_token', 'meta_value' => $api_token]);
    if (!empty($users)) {
        $user = $users[0];
        $user_id1 = $user->ID;
    }
    
    $user_id = $user_id1;
    $stored_token = get_user_meta( $user_id, 'api_token', true );
    $expiration_time = get_user_meta( $user_id, 'api_token_expiration', true );

    if ( ! empty( $stored_token ) && hash_equals( $stored_token, $api_token ) && $expiration_time > time() ) {
        
        $cart_items = WC()->cart->get_cart();
        
        $order_data = $request->get_json_params(); 
        $order = wc_create_order();
        foreach ($cart_items as $cart_item_key => $cart_item) {
            // Add products to the order
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
        
            $order->add_product($product, $quantity, array(
                'variation' => $product->get_variation_id(),
            ));
        }
    
        $order->set_address($order_data['billing_address'], 'billing');
        $order->set_address($order_data['shipping_address'], 'shipping');
        // $order->add_coupon('Fresher','10','2'); // accepted param $couponcode, $couponamount,$coupon_tax
        $order->calculate_totals();
    
        // Set payment method
        $payment_method = $order_data['payment_method'];
        $order->set_payment_method($payment_method);
       
        foreach ($order_data as $meta_key => $meta_value) {
            add_post_meta($order_id, $meta_key, $meta_value, true);
        }
        
        // Save order
        $order_id = $order->save(); 
        

        $order = wc_get_order($order_id);

        if ($order) {
           
            $billing[] = array(
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'company' => $order->get_billing_company(),
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'postcode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
            );
            
            $shipping[] = array(
                'first_name' => $order->get_shipping_first_name(),
                'last_name' => $order->get_shipping_last_name(),
                'company' => $order->get_shipping_company(),
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city' => $order->get_shipping_city(),
                'state' => $order->get_shipping_state(),
                'postcode' => $order->get_shipping_postcode(),
                'country' => $order->get_shipping_country(),
            );
            
            $items = $order->get_items();
            $productArray = array();
                
            foreach ($items as $item_id => $item) {
                $product_id = $item->get_product_id();
                $product = wc_get_product($product_id);
        
                $product1['product_name'] = $product->get_name();
                $product1['product_sku'] = $product->get_sku();
                $product1['product_price'] = $item->get_subtotal();
                $product1['quantity'] = $item->get_quantity();
                
                
                 // Check if the product has a vendor
                if (function_exists('wcpv_get_product_vendors')) {
                    $product_vendors = wcpv_get_product_vendors($product_id);
                    
                    if (!empty($product_vendors)) {
                        $vendor = $product_vendors[0];
                        $product1['store_name'] = $vendor->user_data->display_name;
                        
                    }
                }
             
        
                // Get featured image URL
                $featured_image_id = $product->get_image_id();
                $product1['featured_image_url'] = wp_get_attachment_image_url($featured_image_id, 'full');
                
                // vendor info
                $vendor_id = get_post_field('post_author', $product_id); 
               
                if ( wcfm_is_vendor( $vendor_id ) ) {
                    $store_user = wcfmmp_get_store( $vendor_id );
                    $shop_url = $store_user->get_shop_url();
                    $shop_logo = $store_user->get_avatar();
                    $store_name = $store_user->get_store_name();
                    $store_email = $store_user->get_store_email();
                    
                     $product1['vendor'] = array(
                        'vendor_id' => $vendor_id,
                        'vendor_email' => $store_email,
                        'vendor_shopurl' => $shop_url,
                        'vendor_logo' => $shop_logo,
                        'vendor_storename' => $store_name,
                    );
                }

                array_push($productArray, $product1);
            }
            
            $order_placed_date = $order->get_date_created();

            $order_accepted_date = $order->get_date_completed();
            if ($order_accepted_date) {
                $accepted_date = $order_accepted_date->date_i18n('Y-m-d H:i:s');
            }else{
                $accepted_date = '';
            }
            
            
            // $activity[] = array(
            //     'order_placed_date' =>  $order_placed_date->date_i18n('Y-m-d H:i:s'),
            //     'accepted_date' =>  $accepted_date,
               
            // );
            
    
           
            $formatted_orders[] = array(
                'id' => $order->get_id(),
                'status' => $order->get_status(),
                'subtotal' => $order->get_subtotal(),
                'shipping_amount' => $order->get_shipping_total(),
                'total' => $order->get_total(),
                'currency' => $order->get_currency(),
                'order_key' => $order->get_order_key(),
                'billing' => $billing,
                'shipping' => $shipping,
                // 'items' => $productArray,
                // 'activity' => $activity,
                // Add more order data as needed
            );  
        }
        
        $groupedProducts = [];
    
        // Iterate through the products and group them by shop
        foreach ($productArray as $productdata) {
            $shopId = $productdata['vendor']['vendor_id'];
            $shopemail = 
        
            $vendor_details = array(
                'vendor_id' =>  $shopId,   
                'vendor_email' =>  $productdata['vendor']['vendor_email'], 
                'vendor_shopurl' =>  $productdata['vendor']['vendor_shopurl'], 
                'vendor_logo' =>  $productdata['vendor']['vendor_logo'],
                'vendor_storename' =>  $productdata['vendor']['vendor_storename'], 
            );
            
            $shipping_details = array(
                'shipping' =>  $productdata['shipping'],   
            );
        
            // If the shop's array doesn't exist in $groupedProducts, create it
            if (!isset($groupedProducts[$shopId])) {
                $groupedProducts[$shopId] = [];
                $groupedProducts[$shopId]['vendor_details'][] = $vendor_details;
                $groupedProducts[$shopId]['shipping_details'][] = $shipping_details;
            }
        
            // Add the product to the shop's array
            $groupedProducts[$shopId]['products'][] = $productdata;
        }
       
        
        
        WC()->cart->empty_cart();
        $cart_items1 = WC()->cart->get_cart();
       
        $response1['status'] = true;
        $response1['code'] = 200;
        $response1['message'] = 'Thank you for your order! Your order has been successfully completed.';
        $response1['data'] = $formatted_orders;
        $response1['items'] = $groupedProducts;
        return array($response1);
    
    } else {
        $response1['status'] = false;
        $response1['code'] = 401;
        $response1['message'] = 'Invalid token or expired token.';
        
        return array($response1);
        // Invalid token or expired token, return error response
    }
}



// Add a custom REST API endpoint
add_action('rest_api_init', 'register_orders_api_endpoint');

function register_orders_api_endpoint() {
    register_rest_route('wp/v2', '/orders', array(
        'methods' => 'GET',
        'callback' => 'get_orders',
        'permission_callback' => 'check_bearer_token',
    ));
}

// Check bearer token validity
function check_bearer_token($request) {
    // $token = $request->get_header('Authorization');

    $headers = getallheaders();
    $api_token = isset( $headers['Authorization'] ) ? str_replace( 'Bearer ', '', $headers['Authorization'] ) : '';
    
    $user_id1 = 0; // Default value if the token doesn't match any user
    // Loop through all users and find the one with a matching token
    $users = get_users(['meta_key' => 'api_token', 'meta_value' => $api_token]);
    if (!empty($users)) {
        $user = $users[0];
        $user_id1 = $user->ID;
    }
    
    $user_id = $user_id1; 
    $stored_token = get_user_meta( $user_id, 'api_token', true );
    $expiration_time = get_user_meta( $user_id, 'api_token_expiration', true );

    if ( ! empty( $stored_token ) && hash_equals( $stored_token, $api_token ) && $expiration_time > time() ) {

    return true; // Return true if token is valid, false if not
    
    } else {
        $response1['status'] = false;
        $response1['code'] = 401;
        $response1['message'] = 'Invalid token or expired token.';
        
        return array($response1);
        // Invalid token or expired token, return error response
    }
    
}

// Callback function for the API endpoint
function get_orders($request) {
    
    $params = $request->get_params();
    
    
    
    $headers = getallheaders();
    $api_token = isset( $headers['Authorization'] ) ? str_replace( 'Bearer ', '', $headers['Authorization'] ) : '';
    
    $user_id1 = 0; // Default value if the token doesn't match any user
    $users = get_users(['meta_key' => 'api_token', 'meta_value' => $api_token]);
    if (!empty($users)) {
        $user = $users[0];
        $user_id1 = $user->ID;
    }
    
    $user_id = $user_id1; 
    
    $user_data = get_userdata($user_id);

    if ($user_data) {
        $user_email = $user_data->user_email;
          
        if (isset($params['status'])) {
            $args['status'] = $params['status'];
            
            $args = array(
                'customer' => $user_email,
                'status' => $args['status'], // Fetch orders with any status
                'limit' => -1,     // Fetch all orders
            );
        }else{
            $args = array(
                'customer' => $user_email,
                'status' => 'any', // Fetch orders with any status
                'limit' => -1,     // Fetch all orders
            );
        }
        
        
        // Run the query  wc_get_products
        $orders = wc_get_orders($args);  
        
        $formatted_orders = array();
    
        foreach ($orders as $order) { 
            $billing[] = array(
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'company' => $order->get_billing_company(),
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'postcode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
            );
            
            $shipping[] = array(
                'first_name' => $order->get_shipping_first_name(),
                'last_name' => $order->get_shipping_last_name(),
                'company' => $order->get_shipping_company(),
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city' => $order->get_shipping_city(),
                'state' => $order->get_shipping_state(),
                'postcode' => $order->get_shipping_postcode(),
                'country' => $order->get_shipping_country(),
            );
            
            $items = $order->get_items();
            $productArray = array();
                
            foreach ($items as $item_id => $item) {
                $product_id = $item->get_product_id();
                $product = wc_get_product($product_id);
        
                $product1['product_name'] = $product->get_name();
                $product1['product_sku'] = $product->get_sku();
                $product1['product_price'] = $item->get_subtotal();
                $product1['quantity'] = $item->get_quantity();
                
                
                 // Check if the product has a vendor
                if (function_exists('wcpv_get_product_vendors')) {
                    $product_vendors = wcpv_get_product_vendors($product_id);
                    
                    if (!empty($product_vendors)) {
                        $vendor = $product_vendors[0];
                        $product1['store_name'] = $vendor->user_data->display_name;
                        
                    }
                }
        
                // Get featured image URL
                $featured_image_id = $product->get_image_id();
                $product1['featured_image_url'] = wp_get_attachment_image_url($featured_image_id, 'full');

                // vendor info
                $vendor_id = get_post_field('post_author', $product_id); 
               
                if ( wcfm_is_vendor( $vendor_id ) ) {
                    $store_user = wcfmmp_get_store( $vendor_id );
                    $shop_url = $store_user->get_shop_url();
                    $shop_logo = $store_user->get_avatar();
                    $store_name = $store_user->get_store_name();
                    $store_email = $store_user->get_store_email();
                    
                     $product1['vendor'] = array(
                        'vendor_id' => $vendor_id,
                        'vendor_email' => $store_email,
                        'vendor_shopurl' => $shop_url,
                        'vendor_logo' => $shop_logo,
                        'vendor_storename' => $store_name,
                    );
                }
                
                array_push($productArray, $product1);
            }
        
           
            $formatted_orders[] = array(
                'id' => $order->get_id(),
                'status' => $order->get_status(),
                'currency' => $order->get_currency(),
                'order_key' => $order->get_order_key(),
                'billing' => $billing,
                'shipping' => $shipping,
                'items' => $productArray,
              
                // Add more order data as needed
            );
        }
        
        
       
        $status_counts = array();

        // Define order statuses you want to count
        $order_statuses = array('pending', 'completed', 'cancelled', 'refunded', 'accepted', 'placed', 'shipped');
        
        foreach ($order_statuses as $status) {
            $count = 0;
    
            // Query to count orders with the specific status and user email
            $orders = wc_get_orders(array(
                'status' => $status,
                'customer' => $user_email,
                'return' => 'ids',
            ));
    
            if (!empty($orders)) {
                $count = count($orders);
            }
    
            $status_counts[$status] = $count;
        }
    
    
    }else{
        $response1['status'] = false;
        $response1['code'] = 401;
        $response1['message'] = 'Invalid user details, try again';
        
        return array($response1);
    }
    
    
    $response1['status'] = true;
    $response1['code'] = 200;
    $response1['message'] = 'Successfully fetch all orders data';
    $response1['data'] = $formatted_orders;
    $response1['items'] = $groupedProducts;
    $response1['status_counts'] = $status_counts;
    return array($response1);
    // return rest_ensure_response($formatted_orders);
}


// order details 
function custom_user_order_details() {
    register_rest_route('wp/v2', '/order-details', array(
        'methods' => 'GET',
        'callback' => 'verify_api_token_for_order_details',
    ));
}
add_action('rest_api_init', 'custom_user_order_details');

function verify_api_token_for_order_details( $request ) {
    $headers = getallheaders();
    $api_token = isset( $headers['Authorization'] ) ? str_replace( 'Bearer ', '', $headers['Authorization'] ) : '';
    
    $user_id1 = 0; // Default value if the token doesn't match any user

    $users = get_users(['meta_key' => 'api_token', 'meta_value' => $api_token]);
    if (!empty($users)) {
        $user = $users[0];
        $user_id1 = $user->ID;
    }
    
    $user_id = $user_id1;
    $stored_token = get_user_meta( $user_id, 'api_token', true );
    $expiration_time = get_user_meta( $user_id, 'api_token_expiration', true );

    if ( ! empty( $stored_token ) && hash_equals( $stored_token, $api_token ) && $expiration_time > time() ) {
        
        $order_id = $request->get_param('order_id');
        $order = wc_get_order($order_id);

        if ($order) {
           
            $billing[] = array(
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'company' => $order->get_billing_company(),
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'postcode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
            );
            
            $shipping[] = array(
                'first_name' => $order->get_shipping_first_name(),
                'last_name' => $order->get_shipping_last_name(),
                'company' => $order->get_shipping_company(),
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city' => $order->get_shipping_city(),
                'state' => $order->get_shipping_state(),
                'postcode' => $order->get_shipping_postcode(),
                'country' => $order->get_shipping_country(),
            );
            
            $items = $order->get_items();
            $productArray = array();
                
            foreach ($items as $item_id => $item) {
                $product_id = $item->get_product_id();
                $product = wc_get_product($product_id);
        
                $product1['product_name'] = $product->get_name();
                $product1['product_sku'] = $product->get_sku();
                $product1['product_price'] = $item->get_subtotal();
                $product1['quantity'] = $item->get_quantity();
                
                
                 // Check if the product has a vendor
                if (function_exists('wcpv_get_product_vendors')) {
                    $product_vendors = wcpv_get_product_vendors($product_id);
                    
                    if (!empty($product_vendors)) {
                        $vendor = $product_vendors[0];
                        $product1['store_name'] = $vendor->user_data->display_name;
                        
                    }
                }
             
        
                // Get featured image URL
                $featured_image_id = $product->get_image_id();
                $product1['featured_image_url'] = wp_get_attachment_image_url($featured_image_id, 'full');
                
                // vendor info
                $vendor_id = get_post_field('post_author', $product_id); 
               
                if ( wcfm_is_vendor( $vendor_id ) ) {
                    $store_user = wcfmmp_get_store( $vendor_id );
                    $shop_url = $store_user->get_shop_url();
                    $shop_logo = $store_user->get_avatar();
                    $store_name = $store_user->get_store_name();
                    $store_email = $store_user->get_store_email();
                    
                     $product1['vendor'] = array(
                        'vendor_id' => $vendor_id,
                        'vendor_email' => $store_email,
                        'vendor_shopurl' => $shop_url,
                        'vendor_logo' => $shop_logo,
                        'vendor_storename' => $store_name,
                    );
                }

                array_push($productArray, $product1);
            }
            
            $order_placed_date = $order->get_date_created();

            $order_accepted_date = $order->get_date_completed();
            if ($order_accepted_date) {
                $accepted_date = $order_accepted_date->date_i18n('Y-m-d H:i:s');
            }else{
                $accepted_date = '';
            }
            
            
            $activity[] = array(
                'order_placed_date' =>  $order_placed_date->date_i18n('Y-m-d H:i:s'),
                'accepted_date' =>  $accepted_date,
               
            );
            
    
           
            $formatted_orders[] = array(
                'id' => $order->get_id(),
                'status' => $order->get_status(),
                'subtotal' => $order->get_subtotal(),
                'shipping_amount' => $order->get_shipping_total(),
                'total' => $order->get_total(),
                'currency' => $order->get_currency(),
                'order_key' => $order->get_order_key(),
                'billing' => $billing,
                'shipping' => $shipping,
                // 'items' => $productArray,
                'activity' => $activity,
                // Add more order data as needed
            );  
        }
        
        $groupedProducts = [];
    
        // Iterate through the products and group them by shop
        foreach ($productArray as $productdata) {
            $shopId = $productdata['vendor']['vendor_id'];
            $shopemail = 
        
            $vendor_details = array(
                'vendor_id' =>  $shopId,   
                'vendor_email' =>  $productdata['vendor']['vendor_email'], 
                'vendor_shopurl' =>  $productdata['vendor']['vendor_shopurl'], 
                'vendor_logo' =>  $productdata['vendor']['vendor_logo'],
                'vendor_storename' =>  $productdata['vendor']['vendor_storename'], 
            );
            
            $shipping_details = array(
                'shipping' =>  $productdata['shipping'],   
            );
        
            // If the shop's array doesn't exist in $groupedProducts, create it
            if (!isset($groupedProducts[$shopId])) {
                $groupedProducts[$shopId] = [];
                $groupedProducts[$shopId]['vendor_details'][] = $vendor_details;
                $groupedProducts[$shopId]['shipping_details'][] = $shipping_details;
            }
        
            // Add the product to the shop's array
            $groupedProducts[$shopId]['products'][] = $productdata;
        }
       
        $response1['status'] = true;
        $response1['code'] = 200;
        $response1['message'] = 'order details fetch successfully';
        $response1['data'] = $formatted_orders;
        $response1['items'] = $groupedProducts;
        return array($response1);
    
    } else {
        $response1['status'] = false;
        $response1['code'] = 401;
        $response1['message'] = 'Invalid token or expired token.';
        
        return array($response1);
        // Invalid token or expired token, return error response
    }
}


// Logout functionality

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v2', '/logout/', array(
        'methods'             => 'GET',
        'callback'            => 'wp_oauth_server_logout'
    ) );
} );

function wp_oauth_server_logout() {
    
    $headers = getallheaders();
    $api_token = isset( $headers['Authorization'] ) ? str_replace( 'Bearer ', '', $headers['Authorization'] ) : '';
    
    $user_id1 = 0; // Default value if the token doesn't match any user
    // Loop through all users and find the one with a matching token
    $users = get_users(['meta_key' => 'api_token', 'meta_value' => $api_token]);
    if (!empty($users)) {
        $user = $users[0];
        $user_id1 = $user->ID;
    }
    
    $user_id = $user_id1;
    $stored_token = get_user_meta( $user_id, 'api_token', true );
    $expiration_time = get_user_meta( $user_id, 'api_token_expiration', true );

    if ( ! empty( $stored_token ) && hash_equals( $stored_token, $api_token ) && $expiration_time > time() ) {
        
    wp_logout();
    
    delete_user_meta($user_id, 'api_token');
    delete_user_meta($user_id, 'api_token_expiration');
    
    $response1['status'] = true;
    $response1['code'] = 200;
    $response1['message'] = 'User Logout successfully';
    return array($response1);
    }else{
        
        $response1['status'] = false;
        $response1['code'] = 401;
        $response1['message'] = 'Invalid user details, try again';
        
        return array($response1);
    }
}
// END of Logout functionality






// store_user_shipping_details api
function custom_store_user_shipping_details() {
    register_rest_route('wp/v2', '/store-shipping-details', array(
        'methods' => 'POST',
        'callback' => 'store_shipping_details',
    ));
}
add_action('rest_api_init', 'custom_store_user_shipping_details');

function store_shipping_details( $request ) {
    $headers = getallheaders();
    $api_token = isset( $headers['Authorization'] ) ? str_replace( 'Bearer ', '', $headers['Authorization'] ) : '';
    
    $user_id1 = 0; // Default value if the token doesn't match any user
    // Loop through all users and find the one with a matching token
    $users = get_users(['meta_key' => 'api_token', 'meta_value' => $api_token]);
    if (!empty($users)) {
        $user = $users[0];
        $user_id1 = $user->ID;
    }
    
    $user_id = $user_id1;
    $stored_token = get_user_meta( $user_id, 'api_token', true );
    $expiration_time = get_user_meta( $user_id, 'api_token_expiration', true );

    if ( ! empty( $stored_token ) && hash_equals( $stored_token, $api_token ) && $expiration_time > time() ) {

        $data1 = $request->get_json_params();
        update_user_meta($user_id, 'shipping_address', $data1['shipping_address']);
        
        $userdata = get_user_meta( $user_id, 'shipping_address', true );
        $response1['status'] = true;
        $response1['code'] = 200;
        $response1['message'] = 'Shipping details store successfully.';
        $response1['data'] = $userdata;
        
        return array($response1);
    
    } else {
        $response1['status'] = false;
        $response1['code'] = 401;
        $response1['message'] = 'Invalid token or expired token.';
        
        return array($response1);
        // Invalid token or expired token, return error response
    }
}

// -------------------------------------
// -------------  pk end  --------------



//Forgot password
function custom_user_forget_password($request)
{
    $email = $request->get_param('email');

    $userdata = get_user_by('email', $email);

    if (empty($userdata)) {
        $userdata = get_user_by('login', $email);
    }

    if (empty($userdata)) {
        return __('User not found');
    }

    $user = new WP_User(intval($userdata->ID));
    $reset_key = get_password_reset_key($user);
    $wc_emails = WC()->mailer()->get_emails();
    $wc_emails['WC_Email_Customer_Reset_Password']->trigger($user->user_login, $reset_key);

    return __('Password reset link has been sent to your registered email.');

}
add_action('rest_api_init', function () {
    register_rest_route('custom/v1/', '/forget_password', array(
        'methods' => 'POST',
        'callback' => 'custom_user_forget_password',
    ));
});
// END of Forgot password


// In functions.php or custom plugin
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/vendor_details', array(
        'methods' => 'GET',
        'callback' => 'get_vendor_details',
    ));
});
function get_vendor_details($request) {
    
    $params = $request->get_params();
    
    if (isset($params['product_id'])) {
        $product_id = $params['product_id'];
    }else{
        $response['status'] = false;
        $response['code'] = 401;
        $response['message'] = 'Product id required';
    }
    // vendor info
      
        
    $vendor_id = get_post_field('post_author', $product_id);  
    $vendor = get_userdata( $vendor_id ); 
    $vendor = array(
        'vendor_email' => $vendor->user_email,
        'vendor_display_name' => $vendor->display_name,
        'vendor_user_login' => $vendor->user_login,
        'vendor_storename' => $vendor->user_nicename,
        );
   
    $response['status'] = true;
    $response['code'] = 200;
    $response['message'] = 'Success';
    $response['data'] = $vendor;
    return array($response);
}




add_action('rest_api_init', 'register_vendor_products_api');

function register_vendor_products_api() {
    register_rest_route('wp/v2', '/vendor-products', array(
        'methods' => 'GET',
        'callback' => 'get_vendor_products',
        // 'permission_callback' => 'rest_permissions_check',
    ));
}
function get_vendor_products($request) {
    $product_ids = $request->get_param('product_ids'); 
    $vendors = array();

    if (!empty($product_ids)) {
        foreach ($product_ids as $product_id) {
            $product_data = wc_get_product($product_id);
            if ($product_data) {
    
                $product_images = array();
                $product_images[] = array(
                    'src' => get_the_post_thumbnail_url($product_id, 'full'),
                    'alt' => get_post_meta($product_id, '_wp_attachment_image_alt', true),
                );
        
                // Fetch gallery images if available
                $gallery_images = array();
                $gallery_ids = $product_data->get_gallery_image_ids();
                foreach ($gallery_ids as $gallery_id) {
                    $gallery_images[] = array(
                        'src' => wp_get_attachment_url($gallery_id),
                        'alt' => get_post_meta($gallery_id, '_wp_attachment_image_alt', true),
                    );
                }
                
                // Get product categories
                $product_categories = array();
                $categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'));
                foreach ($categories as $category) {
                    $product_categories[] = $category;
                }
                
            
                // related ids fetch
                $related_products = wc_get_related_products( $product_id, 10 );
                
                // vendor info
                $vendor_id = get_post_field('post_author', $product_id); 
                // $vendor = get_userdata( $vendor_id ); 
                // $vendor = array(
                //     'vendor_id' => $vendor_id,
                //     'vendor_email' => $vendor->user_email,
                //     'vendor_display_name' => $vendor->display_name,
                //     'vendor_user_login' => $vendor->user_login,
                //     'vendor_storename' => $vendor->user_nicename,
                //     );
                if ( wcfm_is_vendor( $vendor_id ) ) {
                    $store_user = wcfmmp_get_store( $vendor_id );
                    $shop_url = $store_user->get_shop_url();
                    $shop_logo = $store_user->get_avatar();
                    $store_name = $store_user->get_store_name();
                    $store_email = $store_user->get_store_email();
                    
                    $vendor = array(
                        'vendor_id' => $vendor_id,
                        'vendor_email' => $store_email,
                        'vendor_shopurl' => $shop_url,
                        'vendor_logo' => $shop_logo,
                        'vendor_storename' => $store_name,
                    );
                }
                  
                
                $product = get_post($product_id);
                
                // shipping info
                // $product = wc_get_product($product->ID); 
                $shipping_data = $product_data->get_shipping_class();
            
                    $productsdata[] = array(
                        'id' => $product_id,
                        'name' => $product->post_title,
                        'description' => $product->post_content,
                        'regular_price' => $product_data->get_regular_price(),
                        'sale_price' => $product_data->get_sale_price(),
                        'is_on_sale' => $product_data->is_on_sale(),
                        'images' => $product_images,
                        'gallery_images' => $gallery_images,
                        'categories' => $product_categories,
                        'related_ids' => $related_products,
                        'vendor' => $vendor,
                        'shipping' =>$shipping_data,
                        // 'is_free_shipping' => $product->is_free_shipping(),
                        // Add more fields you want to include in the response
                    );
                
            
               
            }
        }
    }
    
    $groupedProducts = [];

    // Iterate through the products and group them by shop
    foreach ($productsdata as $productdata) {
        $shopId = $productdata['vendor']['vendor_id'];
        $shopemail = 
    
        $vendor_details = array(
            'vendor_id' =>  $shopId,   
            'vendor_email' =>  $productdata['vendor']['vendor_email'], 
            'vendor_shopurl' =>  $productdata['vendor']['vendor_shopurl'], 
            'vendor_logo' =>  $productdata['vendor']['vendor_logo'],
            'vendor_storename' =>  $productdata['vendor']['vendor_storename'], 
        );
        
        $shipping_details = array(
            'shipping' =>  $productdata['shipping'],   
        );
    
        // If the shop's array doesn't exist in $groupedProducts, create it
        if (!isset($groupedProducts[$shopId])) {
            $groupedProducts[$shopId] = [];
            $groupedProducts[$shopId]['vendor_details'][] = $vendor_details;
            $groupedProducts[$shopId]['shipping_details'][] = $shipping_details;
        }
    
        // Add the product to the shop's array
        $groupedProducts[$shopId]['products'][] = $productdata;
    }

    return rest_ensure_response($groupedProducts);
}



function custom_promocode() {
    register_rest_route('wp/v2', '/apply-code', array(
        'methods' => 'POST',
        'callback' => 'apply_custom_promo_code',
    ));
}
add_action('rest_api_init', 'custom_promocode');

function apply_custom_promo_code( $request ) {
    $headers = getallheaders();
    $api_token = isset( $headers['Authorization'] ) ? str_replace( 'Bearer ', '', $headers['Authorization'] ) : '';
    
    $user_id1 = 0; // Default value if the token doesn't match any user
    // Loop through all users and find the one with a matching token
    $users = get_users(['meta_key' => 'api_token', 'meta_value' => $api_token]);
    if (!empty($users)) {
        $user = $users[0];
        $user_id1 = $user->ID;
    }
    
    $user_id = $user_id1;
    $stored_token = get_user_meta( $user_id, 'api_token', true );
    $expiration_time = get_user_meta( $user_id, 'api_token_expiration', true );

    if ( ! empty( $stored_token ) && hash_equals( $stored_token, $api_token ) && $expiration_time > time() ) {

        $data = $request->get_json_params();
        $promo_code = sanitize_text_field($data['code']);
        
        $promo_codes = array();

        // Use WooCommerce functions to retrieve promo codes
        $coupon_posts = get_posts(array(
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ));
    
        foreach ($coupon_posts as $coupon_post) {
            $coupon = new WC_Coupon($coupon_post->ID);
            if ($coupon->is_valid()) {
                $promo_codes[] = $coupon->get_code();
            }
        }
    
         
          
        // Your custom logic for promo code validation and application
        if (in_array($promo_code, $promo_codes)){
            
            global $woocommerce;

    // Check if the coupon code exists
    $coupon_id = wc_get_coupon_id_by_code($promo_code);
    
    if ($coupon_id) {
        // Get the coupon object
        $coupon = new WC_Coupon($coupon_id);

        // Get the coupon amount based on its type
        $discount_amount = 0;
        
        $discountdata = array();
        
        if ($coupon->get_discount_type() === 'fixed_cart') {
            $discount_amount = $coupon->get_amount();
            $discountdata =  array(
                'discount_type' => 'fixed_cart',
                'discount_amount' => $discount_amount,
            );
        } elseif ($coupon->get_discount_type() === 'percent') { 
            $subtotal = $woocommerce->cart->subtotal;
            $discount_amount = ($subtotal * $coupon->get_amount()) / 100;
            $discountdata =  array(
                'discount_type' => 'percent',
                'discount_percentage' => $coupon->get_amount(),
                'discount_amount' => $discount_amount,
            );
        }

        
    }


            // Apply a discount or custom logic here
            // Example: Apply a 10% discount to the cart
            WC()->cart->add_discount($promo_code);
            WC()->cart->calculate_totals();
            
            
            return array('success' => true, 'code' => 200, 'message' => 'Promo code applied successfully', 'discount_details' => $discountdata );
        } else {
            return array('success' => false, 'code' => 401, 'message' => 'Invalid promo code');
        }

    
    } else {
        $response1['status'] = false;
        $response1['code'] = 401;
        $response1['message'] = 'Invalid token or expired token.';
        
        return array($response1);
        // Invalid token or expired token, return error response
    }
}


