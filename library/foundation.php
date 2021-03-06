<?php
// Pagination
function FoundationPress_pagination() {
	global $wp_query;
 
	$big = 999999999; // This needs to be an unlikely integer
 
	// For more options and info view the docs for paginate_links()
	// http://codex.wordpress.org/Function_Reference/paginate_links
	$paginate_links = paginate_links( array(
		'base' => str_replace( $big, '%#%', get_pagenum_link($big) ),
		'current' => max( 1, get_query_var('paged') ),
		'total' => $wp_query->max_num_pages,
		'mid_size' => 5,
		'prev_next' => True,
	    'prev_text' => __('&laquo;', 'FoundationPress'),
	    'next_text' => __('&raquo;', 'FoundationPress'),
		'type' => 'list'
	) );
 

	$paginate_links = str_replace( "<ul class='page-numbers'>", "<ul class='pagination'>", $paginate_links );
	$paginate_links = str_replace( '<li><span class="page-numbers dots">', "<li><a href='#'>", $paginate_links );
	$paginate_links = str_replace( "<li><span class='page-numbers current'>", "<li class='current'><a href='#'>", $paginate_links );
	$paginate_links = str_replace( "</span>", "</a>", $paginate_links );
	$paginate_links = str_replace( "<li><a href='#'>&hellip;</a></li>", "<li><span class='dots'>&hellip;</span></li>", $paginate_links );
	$paginate_links = preg_replace( "/\s*page-numbers/", "", $paginate_links );

	// Display the pagination if more than one page is found
	if ( $paginate_links ) {
		echo '<div class="pagination-centered">';
		echo $paginate_links;
		echo '</div><!--// end .pagination -->';
	}
}

/**
 * A fallback when no navigation is selected by default.
 */
function FoundationPress_menu_fallback() {
	echo '<div class="alert-box secondary">';
	// Translators 1: Link to Menus, 2: Link to Customize
  	printf( __( 'Please assign a menu to the primary menu location under %1$s or %2$s the design.', 'FoundationPress' ),
  		sprintf(  __( '<a href="%s">Menus</a>', 'FoundationPress' ),
  			get_admin_url( get_current_blog_id(), 'nav-menus.php' )
  		),
  		sprintf(  __( '<a href="%s">Customize</a>', 'FoundationPress' ),
  			get_admin_url( get_current_blog_id(), 'customize.php' )
  		)
  	);
  	echo '</div>';
}

// Add Foundation 'active' class for the current menu item
function FoundationPress_active_nav_class( $classes, $item ) {
    if ( $item->current == 1 || $item->current_item_ancestor == true ) {
        $classes[] = 'active';
    }
    return $classes;
}
add_filter( 'nav_menu_css_class', 'FoundationPress_active_nav_class', 10, 2 );

/**
 * Use the active class of ZURB Foundation on wp_list_pages output.
 * From required+ Foundation http://themes.required.ch
 */
function FoundationPress_active_list_pages_class( $input ) {

	$pattern = '/current_page_item/';
    $replace = 'current_page_item active';

    $output = preg_replace( $pattern, $replace, $input );

    return $output;
}
add_filter( 'wp_list_pages', 'FoundationPress_active_list_pages_class', 10, 2 );

/**
 * Enqueue the CSS links to link to the Google Webfonts needed in the theme.
 */

add_action( 'wp_head', 'FoundationPress_add_google_fonts', 5);

function FoundationPress_add_google_fonts() {
        echo "<link href='http://fonts.googleapis.com/css?family=Roboto:700,700italic,400,400italic|Montserrat:400,700' rel='stylesheet' type='text/css'>";
}

/**
 * Getter function for Featured Content.
 *
 * @return array An array of WP_Post objects.
 */
function FoundationPress_get_featured_posts() {
	/**
	 * Filter the featured posts to return.
	 *
	 *
	 * @param array|bool $posts Array of featured posts, otherwise false.
	 */
	return apply_filters( 'FoundationPress_get_featured_posts', array() );
}

/**
 * A helper conditional function that returns a boolean value.
 *
 * @return bool Whether there are featured posts.
 */
function FoundationPress_has_featured_posts() {
	return ! is_paged() && (bool) FoundationPress_get_featured_posts();
}



if ( ! function_exists( 'FoundationPress_sub_nav' ) ) :
 /**
  * Displays a simple subnav with child pages of the current
  * or page above. See usage in page-templates/left-nav-page.php
  *
  * @param  integer $depth  		Levels of child pages to show, default is 1
  * @param  string  $before 		List to start the nav, you could use something like <ul class="nav-bar vertical">
  * @param  string  $after 		Closing </ul>
  * @param  bool    $show_home	Show a home link? Default: false
  * @param  string  $item_type	Usually an li, if not we use dd for you buddy!
  * @return string  Echo out the whole navigation
  *
  */

/*
 * Now it shows the parent page link on top like a breadcrumb instead of linking to home *
 */
function FoundationPress_sub_nav( $nav_args = '' ) {
 
 	global $post;
 
 	$defaults = array(
 		'show_home' => false,
 		'depth'		=> 1,
 		'before'	=> '<ul class="side-nav">',
 		'after'		=> '</ul>',
 		'item_type' => 'li',
 	);
 
 	$nav_args = apply_filters( 'fp_sub_nav_args', wp_parse_args( $nav_args, $defaults ) );
 
 	$args = array(
 		'title_li' 		=> '',
 		'depth'			=> $nav_args['depth'],
 		'sort_column'	=> 'menu_order',
 		'echo'			=> 0,
 	);
 
 	// Make sure the dl only shows 1 level
 	if ( $nav_args['item_type'] != 'li' ) {
 		$args['depth'] = 0;
 	}
 
 	if ( $post->post_parent ) {
 		// So we have a post parent
     	$args['child_of'] = $post->post_parent;
     } else {
     	// So we don't have a post parent, so you are!
     	$args['child_of'] = $post->ID;
     }
 
     // Filter the $args if you want to do something different!
     $children = wp_list_pages( $args );
 
     // Point us back to previous parent or not?
     if ( $nav_args['show_home'] == true ) {
     	$nav_args['before'] .= '<li><a href="' . get_permalink($args['child_of']) . '">' . get_the_title($args['child_of']) . '</a></li><li class="divider"></li>';
     }
 
     // Do we have children?
     if ( $children ) {
 
 		$output = $nav_args['before'] . $children . $nav_args['after'];
 
 		// Replace the output if we are on a definition list
 		if ( $nav_args['item_type'] != 'li' ) {
 
     		$pattern_start = '/<li/';
     		$pattern_end = '/<\/li>/';
 
     		$replace_start = '<dd';
     		$replace_end = '</dd>';
 
     		$output = preg_replace($pattern_start, $replace_start, $output);
     		$output = preg_replace($pattern_end, $replace_end, $output);
     	}
 
     	echo $output;
     }
 }
 endif;

//Page Slug Body Class

function add_slug_body_class( $classes ) {

  global $post;

  if ( isset( $post ) ) {

    $classes[] = $post->post_type . '-' . $post->post_name;

  }

  return $classes;

  }

  add_filter( 'body_class', 'add_slug_body_class' );
?>

