<?php

add_action( 'wp_enqueue_scripts', 'wpchild_enqueue_styles' );
function wpchild_enqueue_styles(){
  wp_enqueue_style( 'wpm-elementor-style', get_template_directory_uri() . '/style.css' );
}

function add_admin_link_to_menu($items, $args) {

	if (is_user_logged_in() && $args->theme_location === 'primary') {
		
		$admin_link = '<li id="menu-item-22" class="menu-item"><a href="' . admin_url() . '">Admin</a></li>';
		
		
		$items_array = explode('</li>', $items);
		
		
		array_splice($items_array, 1, 0, $admin_link);
  
		
		$items = implode('</li>', $items_array);
	}
	return $items;
  }


  add_filter('wp_nav_menu_items', 'add_admin_link_to_menu', 10, 2);
  
