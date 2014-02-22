<?php
/*
  Plugin Name: WordPress Lazy Load
  Description: Implements lazy loading for images below the fold.
  Version: 1.0
  Author: Chirag Swadia
  Author URI: http://madblogger.in
 */

$wpl_is_strict_lazyload = FALSE;

/* Custom Hooks */

/*
 * This is just for adding jQuery!
 */
add_action( 'wp_enqueue_scripts', 'wpl_lazyload_header_script' );
function wpl_lazyload_header_script() {
    wp_enqueue_script('jquery');
}


/*
 * PHP output buffer starts from here
 */
add_action( 'get_header', 'wpl_lazyload_buffer_start' );
function wpl_lazyload_buffer_start() {
    ob_start();
}

/*
 * Whole output buffer content ( markup ) is generated here and passed for filtering
 */
add_action( 'wp_footer', 'wpl_lazyload_buffer_process' );
function wpl_lazyload_buffer_process() {
    $echo = ob_get_contents();
    ob_clean();
    print wpl_lazyload_filter_content($echo);
    ob_end_flush();
}


/*
 * Lazy load styles and javascript is added to footer
 */
add_action( 'wp_footer', 'wpl_lazyload_load_footer', 11 );
function wpl_lazyload_load_footer() {
    wp_enqueue_style('lazy_load_style', wpl_lazyload_get_url("css/lazy-load-style.css"));
    wp_enqueue_script('lazy_load_js', wpl_lazyload_get_url("js/lazy-load.js"));

    // fallback for no javascript in browser
    echo '<noscript><style type="text/css">.wpl_lazyimg{display:none;}</style></noscript>';
}


/* Functions */

/*
 * This is just for getting img/js/css url so I don`t have to write plugins_url again and again.
 */
function wpl_lazyload_get_url($path = '') {
    return plugins_url(ltrim($path, '/'), __FILE__);
}


/*
 * This is where all the magic is performed and the <img> tags are added with required data attribute for lazy load
 */
function wpl_lazyimg_string_handler($matches) {
    
    // whether to check image extensions too while filtering.
    global $wpl_is_strict_lazyload;

    $wpl_lazyimg_string = $matches[0];

    // get transparent image as replacement for actual images ( below the fold in browser )
    $alt_image_src = wpl_lazyload_get_url("images/transparent.gif");

    if (stripos($wpl_lazyimg_string, "class=") === FALSE) {
        $wpl_lazyimg_string = preg_replace(
                "/<img(.*)>/i", '<img class="wpl_lazyimg"$1>', $wpl_lazyimg_string
        );
    } else {
        $wpl_lazyimg_string = preg_replace(
                "/<img(.*)class=['\"]([\w\-\s]*)['\"](.*)>/i", '<img$1class="$2 wpl_lazyimg"$3>', $wpl_lazyimg_string
        );
    }

    if ($wpl_is_strict_lazyload) {
        $regexp = "/<img([^<>]*)src=['\"]([^<>'\"]*)\.(bmp|gif|jpeg|jpg|png)([^<>'\"]*)['\"]([^<>]*)>/i";
        $replace = '<img$1src="' . $alt_image_src . '" file="$2.$3$4"$5><noscript>' . $matches[0] . '</noscript>';
    } else {
        $regexp = "/<img([^<>]*)src=['\"]([^<>'\"]*)['\"]([^<>]*)>/i";
        $replace = '<img$1src="' . $alt_image_src . '" file="$2"$3><noscript>' . $matches[0] . '</noscript>';
    }

    $wpl_lazyimg_string = preg_replace(
            $regexp, $replace, $wpl_lazyimg_string
    );

    return $wpl_lazyimg_string;
}


/*
 * The buffer content is passed here and all <img> tags are extracted from it and passed for further handling
 */
function wpl_lazyload_filter_content($content) {
    
    // Don't lazyload for feeds, previews, mobile
    if ( is_feed() || is_preview() || ( function_exists('is_mobile') && is_mobile() ) || is_ie8() )
        return $content;

    global $wpl_is_strict_lazyload;

    if ($wpl_is_strict_lazyload) {
        $regexp = "/<img([^<>]*)\.(bmp|gif|jpeg|jpg|png)([^<>]*)>/i";
    } else {
        $regexp = "/<img([^<>]*)>/i";
    }

    $content = preg_replace_callback(
            $regexp, "wpl_lazyimg_string_handler", $content
    );

    return $content;
}

/*
* To check browser IE8
*/
function is_ie8(){
	
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if(strpos($user_agent,'MSIE 8.0')){
		return true;
	}else{
		return false;
	}
}


?>