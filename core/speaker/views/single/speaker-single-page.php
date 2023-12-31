<?php
if( wp_is_block_theme() ){
    block_header_area();
    wp_head();
}else{
    get_header();
}


//check if single page template is overriden from theme
//if overriden, then the overriden template has higher priority
if ( file_exists( get_stylesheet_directory() . \Wpeventin::theme_templates_dir() . 'single-speaker.php' ) ) {
    require_once get_stylesheet_directory() . \Wpeventin::theme_templates_dir() . 'single-speaker.php';
} elseif ( file_exists( get_template_directory() . \Wpeventin::theme_templates_dir() . 'single-speaker.php' ) ) {
    require_once get_template_directory() . \Wpeventin::theme_templates_dir() . 'single-speaker.php';
} else if ( file_exists( \Wpeventin::templates_dir() . 'single-speaker.php' ) ) {
    require_once \Wpeventin::templates_dir() . 'single-speaker.php';
}


if( wp_is_block_theme() ){
    block_footer_area();
    wp_footer();
}else{
    get_footer();
}
