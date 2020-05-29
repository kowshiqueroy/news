<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Mystery Themes
 * @subpackage News Portal
 * @since 1.0.0
 */

/*-----------------------------------------------------------------------------------------------------------------------*/
if ( ! function_exists( 'news_portal_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function news_portal_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	echo '<span class="posted-on"><a href="'. esc_url( get_permalink() ) .'" rel="bookmark">' . wp_kses_post( $time_string ) . '</a></span><span class="byline"><span class="author vcard"><a class="url fn n" href="'. esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) .'">' . esc_html( get_the_author() ) .'</a></span></span>';

}
endif;

/*-----------------------------------------------------------------------------------------------------------------------*/
if ( ! function_exists( 'news_portal_inner_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function news_portal_inner_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	echo '<span class="posted-on"><a href="'. esc_url( get_permalink() ) .'" rel="bookmark">' . wp_kses_post( $time_string ) . '</a></span><span class="byline"><span class="author vcard"><a class="url fn n" href="'. esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) .'">' . esc_html( get_the_author() ) .'</a></span></span>';

	if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		comments_popup_link(
			sprintf(
				wp_kses(
					/* translators: %s: post title */
					__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'news-portal' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			)
		);
		echo '</span>';
	}

}
endif;

/*-----------------------------------------------------------------------------------------------------------------------*/
if ( ! function_exists( 'news_portal_entry_footer' ) ) :
/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function news_portal_entry_footer() {

	if ( is_single() ) {
		/* translators: used between list items, there is a space after the comma */
		$tags_list = get_the_tag_list();
		if ( $tags_list ) {
			/* translators: 1: list of tags. */
			printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'news-portal' ) . '</span>', $tags_list ); // WPCS: XSS OK.
		}
	}
	
	edit_post_link(
		sprintf(
			wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				__( 'Edit <span class="screen-reader-text">%s</span>', 'news-portal' ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			),
			get_the_title()
		),
		'<span class="edit-link">',
		'</span>'
	);
}
endif;

/*-----------------------------------------------------------------------------------------------------------------------*/
/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function news_portal_categorized_blog() {
	$all_the_cool_cats = get_transient( 'news_portal_categories' );
	if ( false === $all_the_cool_cats ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'news_portal_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 || is_preview() ) {
		// This blog has more than 1 category so news_portal_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so news_portal_categorized_blog should return false.
		return false;
	}
}

/*-----------------------------------------------------------------------------------------------------------------------*/
/**
 * Flush out the transients used in news_portal_categorized_blog.
 */
function news_portal_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'news_portal_categories' );
}
add_action( 'edit_category', 'news_portal_category_transient_flusher' );
add_action( 'save_post',     'news_portal_category_transient_flusher' );

/*-----------------------------------------------------------------------------------------------------------------------*/
/**
 * Categories list in multiple color background
 *
 * @since 1.0.0
 */
if( ! function_exists( 'news_portal_post_categories_list' ) ):
	function news_portal_post_categories_list() {
		global $post;
		$post_id = $post->ID;
		$categories_list = get_the_category( $post_id );
		if( !empty( $categories_list ) ) {
			$cat_count = apply_filters( 'news_portal_cat_list_count', 2 );
			$count = 0;
?>
		<div class="post-cats-list">
			<?php 
				foreach ( $categories_list as $cat_data ) {					
					$cat_name = $cat_data->name;
					$cat_id = $cat_data->term_id;
					$cat_link = get_category_link( $cat_id );
					if( $count < $cat_count ) {
			?>
						<span class="category-button np-cat-<?php echo esc_attr( $cat_id ); ?>"><a href="<?php echo esc_url( $cat_link ); ?>"><?php echo esc_html( $cat_name ); ?></a></span>
			<?php
					}
					$count++;
				}
			?>
		</div>
<?php
		}
	}
endif;

/*-----------------------------------------------------------------------------------------------------------------------*/
/**
 * Categories list in multiple color background for single post page
 *
 * @since 1.0.0
 */
if( ! function_exists( 'news_portal_single_post_categories_list' ) ):
	function news_portal_single_post_categories_list() {
		global $post;
		$post_id = $post->ID;
		$categories_list = get_the_category( $post_id );
		if( !empty( $categories_list ) ) {
?>
		<div class="post-cats-list">
			<?php 
				foreach ( $categories_list as $cat_data ) {					
					$cat_name = $cat_data->name;
					$cat_id = $cat_data->term_id;
					$cat_link = get_category_link( $cat_id );
			?>
				<span class="category-button np-cat-<?php echo esc_attr( $cat_id ); ?>"><a href="<?php echo esc_url( $cat_link ); ?>"><?php echo esc_html( $cat_name ); ?></a></span>
			<?php
				}
			?>
		</div>
<?php
		}
	}
endif;