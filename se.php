<?php 
/**
 * Plugin Name: SwagEasy - Easy E-Commerce
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Your online store in 60 seconds
 * Version: 1.0.1
 * Author: SwagEasy
 * Author URI: http://swageasy.com
 * License: MIT
 */

add_action( 'admin_menu', 'setup_se_menu' );
add_action('wp_footer', 'se_button_js');
add_filter( 'wp_nav_menu_items', 'nav_with_swageasy' );
add_filter('wp_page_menu','nav_ul_with_swageasy');
add_se_shortcode();


function nav_with_swageasy ($items) {
  $user = get_user_by( 'email', get_option('admin_email'));
  $txt = get_user_meta($user->ID, 'se_btn_text', true);

  $homelink = '<li><a swageasy href="' . home_url( '/#!/store' ) . '">' . $txt . '</a></li>';
  return $items . $homelink;
}

function nav_ul_with_swageasy ($items) {
  $user = get_user_by( 'email', get_option('admin_email'));
  $txt = get_user_meta($user->ID, 'se_btn_text', true);

  $homelink = '<li><a swageasy href="' . home_url( '/#!/store' ) . '">' . $txt . '</a></li>';
  return str_replace('</ul>', $homelink . '</ul>', $items);
}

function se_button_js () {
  $script = plugins_url('btn.js','swageasy');
  wp_register_script( 'btnjs', plugins_url( '/btn.js', __FILE__ ) );
  wp_enqueue_script( 'btnjs' );

  $user = get_user_by( 'email', get_option('admin_email'));
  echo "<script id='swagscript' subdomain='" . get_user_meta($user->ID, 'se_subdomain', true) . "'></script>";
} 

function insert_se_button () {
  $user_id = get_current_user_id();

  if(no_se_integration($user_id)) {
    return '<button class="swag-button no-store">Shop Now</button>';
  } else {
    $btn_text = get_user_meta($user_id, 'se_btn_text', true);
    return '<button class="swag-button">' . $btn_text . '</button>';
  }
}

function add_se_shortcode () {
  $tag = 'swageasy';
  $func = 'insert_se_button';
  add_shortcode( $tag , $func );
}


function setup_se_menu () {
  $pagetitle = 'SwagEasy - Your Online Store In 60 Seconds';
  $menu_item_title = 'SwagEasy';
  $iconpath = '';
  $position = 6;
  $slug = 'swageasy';
  $render_options = 'se_options'; // Function to render the options page
  $capabilities = 'manage_options';

  add_menu_page( $pagetitle, $menu_item_title, $capabilities, $slug, $render_options, $iconpath, $position );
}

function se_options () {
  $user_id = get_current_user_id();

  // If there's a subdomain in the get variable, we need to handle it
  check_subdomain_getvar();

  // Include CSS
  wp_register_style( 'secss', plugins_url('css/se.css', __FILE__) );
  wp_enqueue_style( 'secss' );

  // Render the options page
  echo "<div class='se-page'>";
  echo "<h1>SwagEasy - Your Online Store In 60 Seconds</h1>";
  
  se_buttons($user_id);

  echo "</div>";


  // Include JS
  wp_register_script( 'sejs', plugins_url( '/se.js', __FILE__ ), '', '1.0', true );
  wp_enqueue_script( 'sejs' );
}

function check_subdomain_getvar () {
  $err = error_reporting();
  error_reporting(0);
  $subdomain = $_GET['subdomain'];
  $btn_text = $_GET['btn_text'];
  if (!isset($subdomain) || trim($subdomain)==='' || $subdomain==='none') {
    error_reporting($err);
    return false;
  } else {
    add_confirmation();
    add_se_data($subdomain, $btn_text);
  }
  error_reporting($err);
}

function no_se_integration ($user_id) {
  $subdomain = get_user_meta($user_id, 'se_subdomain', true);
  return (!isset($subdomain) || trim($subdomain)==='');
}

function add_confirmation () {
  echo '<label class="se-confirmed">';
  echo 'Your change was confirmed!';
  echo '</label>';
}

function add_se_data ($subdomain, $btn_text) {
  $user_id = get_current_user_id();
  $meta_key = 'se_subdomain';
  $meta_value = $subdomain;

  if(no_se_integration()){
    update_user_meta( $user_id, $meta_key, $meta_value );
    update_user_meta( $user_id, 'se_btn_text', $btn_text );  
  } else {
    $sub = get_user_meta($user_id, 'se_subdomain', true);
    $txt = get_user_meta($user_id, 'se_btn_text', true);
    update_user_meta( $user_id, $meta_key, $meta_value , $sub);
    update_user_meta( $user_id, 'se_btn_text', $btn_text, $txt);
  }
}



function se_buttons ($user_id) {
  $signup_link = 'http://swageasy.com/r/plugin/wordpress';

  if (no_se_integration($user_id)) {
    echo "<a class='se-button' href='" . $signup_link . "'>Sign up with SwagEasy</a>";
    echo "<a style='display:block;margin-left:35px;margin-top:10px;' id='hazaccount' href='?page=swageasy&subdomain=none'>Already have an account?</a>";
    echo '<form id="se-form" style="display:none"><p>Enter your SwagEasy subdomain</p>';
  }  else {
    echo '<form id="se-form" style=""><p>Enter your SwagEasy subdomain</p>';
  }
  
  echo '<input type="text" name="subdomain" id="subdomain" placeholder="subdomain" value="' . get_user_meta($user_id, 'se_subdomain', true) . '" />';
  echo '<label for="subdomain">.swageasy.com</label>';
  echo '<p>Enter the text you\'d like to appear on SwagEasy buttons:</p>';
  echo '<input type="text" name="btn_text" id="btn_text" value="' . get_user_meta($user_id, 'se_btn_text', true) . '" placeholder="Shop Now" />';
  echo '<br><input type="submit" value="Save Changes"><br>';
  echo '<a href="https://www.swageasy.com/r/plugin/wordpress" target="_blank">Don\'t have an account?</a></form>';
}

?>