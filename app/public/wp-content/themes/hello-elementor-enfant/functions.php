<?php


function mon_theme_enfant_enqueue_styles() {
  wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'mon_theme_enfant_enqueue_styles');

function add_admin_link_to_menu($items, $args) {
    if (is_user_logged_in() && ($args->theme_location === 'primary' || $args->menu_id === 'ast-hf-mobile-menu')) {
        $class = ($args->menu_id === 'ast-hf-mobile-menu') ? 'menu-item menu-item-type-post_type menu-item-object-page' : '';
        $admin_link = '<li id="menu-item-22" class="menu-item-22 menu-item ' . $class . '"><a class="menu-link" href="' . admin_url() . '">Admin</a></li>';

        $items_array = explode('</li>', $items);
        array_splice($items_array, 1, 0, $admin_link);
        $items = implode('</li>', $items_array);
    }
    return $items;
}

add_filter('wp_nav_menu_items', 'add_admin_link_to_menu', 10, 2);
