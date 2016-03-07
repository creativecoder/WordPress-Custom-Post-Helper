<?php
/**
 * Create and register custom post types and taxonomies
 *
 * Example: `$post = new Custom_Post_Type( $name, $args, $labels );`
 *
 * @package WordPress
 */

class Custom_Post_Type {
	/**
	 * Programatic name of post type
	 * @var string
	 */
	public $post_type_name;

	/**
	 * Post type settings
	 * @var array
	 */
	public $post_type_args;

	/**
	 * Post type labels
	 * @var array
	 */
	public $post_type_labels;

	/**
	 * Create a new custom post type
	 *
	 * @param string $name
	 * @param array  $args   Settings
	 * @param array  $labels
	 */
	public function __construct( $name, $args = array(), $labels = array() ) {
		// Set some important variables
		$this->post_type_name   = self::uglify( $name );
		$this->post_type_args   = $args;
		$this->post_type_labels = $labels;

		// Add action to register the post type, if the post type doesn't exist
		if ( !post_type_exists( $this->post_type_name ) ) {
			add_action( 'init', array( &$this, 'register_post_type' ) );

			// Add custom messages
			add_filter('post_updated_messages', array( &$this, 'set_messages' ) );
		}
	}

	/**
	 * Register the custom post type
	 * @return void
	 */
	public function register_post_type() {
		// Capitalize the words and make it plural
		$name   = self::beautify( $this->post_type_name );
		$plural = self::pluralize( $name );
		$slug   = self::slugify( $this->post_type_name );

		// We set the default labels based on the post type name and plural. We overwrite them with the given labels.
		$labels = array_merge(

			// Default
			array(
				// General name for the post type, usually plural. The same and overridden
				// by $post_type_object->label. Default is Posts/Pages
				'name'                  => sprintf( _x( '%s', 'post type general name', 'custom-post-helper' ), $plural ), 

				// name for one object of this post type. Default is Post/Page
				'singular_name'         => sprintf( _x( '%s', 'post type singular name', 'custom-post-helper' ), $name ),

				// Default is Add New for both hierarchical and non-hierarchical types.
				// When' internationalizing this string, please use a gettext context
				// @link https://codex.wordpress.org/I18n_for_WordPress_Developers#Disambiguation_by_context}
				// matching your post type. Example: `_x( 'Add New', 'product' );`.
				'add_new'               => _x( 'Add New', 'add new custom post type', 'custom-post-helper' ),

				// Default is Add New Post/Add New Page.
				'add_new_item'          => sprintf( _x( 'Add New %s', 'add new custom post type', 'custom-post-helper' ), $name ),

				// Default is Edit Post/Edit Page.
				'edit_item'             => sprintf( _x( 'Edit %s', 'edit custom post type', 'custom-post-helper' ), $name ),

				// Default is New Post/New Page.
				'new_item'              => sprintf( _x( 'New %s', 'create new custom post type', 'custom-post-helper' ), $name ),

				// Default is View Post/View Page.
				'view_item'             => sprintf( _x( 'View %', 'view custom post type', 'custom-post-helper' ), $name ),

				// Default is Search Posts/Search Pages.
				'search_items'          => sprintf( _x( 'Search %s', 'search custom post type', 'custom-post-helper' ), $plural ),

				// Default is No posts found/No pages found.
				'not_found'             => sprintf( _x( 'No %s found', 'no custom post types found', 'custom-post-helper' ), $plural ),

				// Default is No posts found in Trash/No pages found in Trash.
				'not_found_in_trash'    => sprintf( _x( 'No %s found in Trash', 'no custom post types found', 'custom-post-helper' ), $plural ),

				// This string isn't used on non-hierarchical types. In hierarchical ones the default is 'Parent Page:'.
				'parent_item_colon'     => sprintf( _x( 'Parent %s', 'custom post type name', 'custom-post-helper' ), $name ),

				// String for the submenu. Default is All Posts/All Pages.
				'all_items'             => sprintf( _x( 'All %s', 'custom post type plural name', 'custom-post-helper' ), $plural ),

				// String for use with archives in nav menus. Default is Post Archives/Page Archives.
				'archives'              => sprintf( _x( '%s Archives', 'custom post type archives', 'custom-post-helper' ), $name ),

				// String for the media frame button. Default is Insert into post/Insert into page.
				'insert_into_item'      => sprintf( _x( 'Insert into %s', 'insert into custom post type', 'custom-post-helper' ), $name ),

				// String for the media frame filter. Default is Uploaded to this post/Uploaded to this page.
				'uploaded_to_this_item' => sprintf( _x( 'Uploaded to this %s', 'uploaded to custom post type', 'custom-post-helper' ), $name ),

				// Default is Featured Image.
				'featured_image'        => __( 'Featured Image', 'custom-post-helper' ),

				// Default is Set featured image.
				'set_featured_image'    => __( 'Set featured image', 'custom-post-helper' ),

				// Default is Remove featured image.
				'remove_featured_image' => __( 'Remove featured image', 'custom-post-helper' ),

				// Default is Use as featured image.
				'use_featured_image'    => __( 'Use as featured image', 'custom-post-helper' ),

				// Default is the same as `name`.
				'menu_name'             => sprintf( _x( '%s', 'post type plural name', 'custom-post-helper' ), $plural ),

				// String for the table views hidden heading.
				'filter_items_list'     => sprintf( _x( 'Filter %s list', 'filter custom post type list', 'custom-post-helper' ), $plural ),

				// String for the table pagination hidden heading.
				'items_list_navigation' => sprintf( _x( '$s list navigation', 'custom post type list navigation', 'custom-post-helper' ), $plural ),

				// String for the table hidden heading.
				'items_list'            => sprintf( _x( '%s list', 'custom post type list', 'custom-post-helper' ), $plural ),

				// String for use in New in Admin menu bar. Default is the same as `singular_name`.
				'name_admin_bar'        => _x( $name, 'post type singular name', 'custom-post-helper' ),
			),

			// Given labels
			$this->post_type_labels

		);

		// Same principle as the labels. We set some default and overwite them with the given arguments.
		$args = array_merge(

			// Default
			array(
				'label'             => $plural,
				'labels'            => $labels,
				'public'            => true,
				'show_ui'           => true,
				'supports'          => array( 'title', 'editor' ),
				'show_in_nav_menus' => true,
				'has_archive'       => true,
				'rewrite'           => array( 'slug' => $slug ),
				'_builtin'          => false,
			),

			// Given args
			$this->post_type_args

		);

		// Register the post type
		register_post_type( $this->post_type_name, $args );
	}
	
	/**
	 * Custom status messages in wp-admin using post type name
	 *
	 * @param array $messages Set of status messages for actions on a post (updating, scheduling, etc)
	 * @return array          Filtered array of messages
	 */
	public function set_messages( $messages ) {
		// Get post object and post ID
		global $post, $post_ID;
		
		// Capitalize the words and make it plural
		$name = self::beautify( $this->post_type_name );
		
		$messages[$this->post_type_name] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf(
				__( '%1$s updated. <a href="%2$s">View %1$s</a>', 'custom-post-helper' ),
				$name,
				esc_url( get_permalink( $post_ID ) )
			),
			2 => __( 'Custom field updated.', 'custom-post-helper' ),
			3 => __( 'Custom field deleted.', 'custom-post-helper' ),
			4 => sprintf( _x( '%s updated.', 'custom post type updated', 'custom-post-helper' ), $name ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( '%s restored to revision from %s', 'custom-post-helper' ), $name, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( '%s published. <a href="%s">View %s</a>', 'custom-post-helper' ), $name, esc_url( get_permalink( $post_ID ) ), strtolower( $name ) ),
			7 => sprintf( __( '%s saved.', 'custom-post-helper' ), $name ),
			8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'custom-post-helper' ), $name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), strtolower( $name ) ),
			9 => sprintf( __( '%s scheduled for: <strong>%s</strong>. <a target="_blank" href="%s">Preview %s</a>', 'custom-post-helper' ), $name, date_i18n( __( 'M j, Y @ G:i', 'custom-post-helper' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), strtolower( $name ) ),
			10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'custom-post-helper' ), $name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), strtolower( $name ) ),
		);

		return $messages;
	}

	/**
	 * Associate a taxonomy to the custom post type
	 *
	 * @param string $name   Name of taxonomy to add
	 * @param array  $args   Settings for the taxonomy, used if it isn't a built in taxonomy and hasn't already been registered
	 * @param array  $labels Labels for the taxonomy, used if it isn't a built in taxonomy and hasn't already been registered
	 */
	public function add_taxonomy( $name, $args = array(), $labels = array() ) {
		if ( $name ) {
			// We need to know the post type name, so the new taxonomy can be attached to it.
			$post_type_name = $this->post_type_name;

			// Taxonomy properties
			$taxonomy_name   = self::uglify( $name );
			$taxonomy_labels = $labels;
			$taxonomy_args   = $args;

			if ( ! taxonomy_exists( $taxonomy_name ) ) {
				//Capitilize the words and make it plural
				$name	= self::beautify( $name );
				$plural	= self::pluralize( $name );

				// Default labels, overwrite them with the given labels.
				$labels = array_merge(
					// Default
					array(
						// General name for the taxonomy, usually plural. The same as and overridden by $tax->label.
						// Default is _x( 'Post Tags', 'taxonomy general name' ) or _x( 'Categories', 'taxonomy general name' ).
						// When internationalizing this string, please use a gettext context matching your post type. Example: _x('Writers', 'taxonomy general name');
						'name' => sprintf( _x( '%s', 'taxonomy general name', 'custom-post-helper' ), $plural ),

						// Name for one object of this taxonomy.
						// Default is _x( 'Post Tag', 'taxonomy singular name' ) or _x( 'Category', 'taxonomy singular name' ).
						// When internationalizing this string, please use a gettext context matching your post type. Example: _x('Writer', 'taxonomy singular name');
						'singular_name' => sprintf( _x( '%s', 'taxonomy singular name', 'custom-post-helper' ), $name ),

						// The menu name text. This string is the name to give menu items. If not set, defaults to value of name label.
						'menu_name' => sprintf( _x( '%s', 'taxonomy general name', 'custom-post-helper' ), $plural ),

						// The all items text.
						// Default is __( 'All Tags' ) or __( 'All Categories' )
						'all_items' => sprintf( _x( 'All %s', 'all taxonomy terms', 'custom-post-helper' ), $plural ),

						// the edit item text.
						// Default is __( 'Edit Tag' ) or __( 'Edit Category' )
						'edit_item' => sprintf( _x( 'Edit %s', 'edit taxonomy term', 'custom-post-helper' ), $name ),

						// The view item text.
						// Default is __( 'View Tag' ) or __( 'View Category' )
						'view_item' => sprintf( _x( 'View %s', 'view taxonomy term', 'custom-post-helper' ), $name ),

						// The update item text.
						// Default is __( 'Update Tag' ) or __( 'Update Category' )
						'update_item' => sprintf( _x( 'Update %s', 'taxonomy term', 'custom-post-helper'), $name ),

						// The add new item text.
						// Default is __( 'Add New Tag' ) or __( 'Add New Category' )
						'add_new_item' => sprintf( _x( 'Add New %s', 'add new taxonomy term', 'custom-post-helper'), $name ),

						// The new item name text.
						// Default is __( 'New Tag Name' ) or __( 'New Category Name' )
						'new_item_name' => sprintf( _x( 'New %s Name', 'taxonomy term', 'custom-post-helper'), $name ),
						
						// The parent item text. This string is not used on non-hierarchical taxonomies such as post tags.
						// Default is null or __( 'Parent Category' )
						'parent_item' => sprintf( _x( 'Parent %s', 'parent taxonomy term', 'custom-post-helper' ), $name ),
						
						// The same as parent_item, but with colon : in the end null, __( 'Parent Category:' )
						'parent_item_colon' => sprintf( _x( 'Parent %s:', 'parent taxonomy term', 'custom-post-helper' ), $name ),

						// The search items text.
						// Default is __( 'Search Tags' ) or __( 'Search Categories' )
						'search_items' => sprintf( _x( 'Search %s', 'search taxonomy', 'custom-post-helper' ), $plural ),

						// The popular items text. This string is not used on hierarchical taxonomies.
						// Default is __( 'Popular Tags' ) or null
						'popular_items' => sprintf( _x( 'Popular %s', 'popular taxonomy terms', 'custom-post-helper' ), $plural ),

						// The separate item with commas text used in the taxonomy meta box. This string is not used on hierarchical taxonomies.
						// Default is __( 'Separate tags with commas' ), or null
						'separate_items_with_commas' => sprintf( _x( 'Separate %s with commas', 'separate taxonomy terms with commas', 'custom-post-helper' ), strtolower( $plural ) ),
						
						// The add or remove items text and used in the meta box when JavaScript is disabled. This string is not used on hierarchical taxonomies.
						// Default is __( 'Add or remove tags' ) or null
						'add_or_remove_items' => sprintf( _x( 'Add or remove %s', 'add or remove taxonomy terms', 'custom-post-helper' ), strtolower( $plural ) ),

						// the choose from most used text used in the taxonomy meta box. This string is not used on hierarchical taxonomies.
						// Default is __( 'Choose from the most used tags' ) or null
						'choose_from_most_used' => sprintf( _x( 'Choose from the most used %s', 'taxonomy terms', 'custom-post-helper' ), strtolower( $plural ) ),

						// the text displayed via clicking 'Choose from the most used tags' in the taxonomy meta box when no tags are available and (4.2+) -
						// the text used in the terms list table when there are no items for a taxonomy.
						// Default is __( 'No tags found.' ) or __( 'No categories found.' )
						'not_found' => sprintf( _x( 'No %s found.', 'taxonomy terms', 'custom-post-helper' ), strtolower( $plural ) ),
					),

					// Given labels
					$taxonomy_labels

				);

				// Default arguments, overwitten with the given arguments
				$args = array_merge(

					// Default
					array(
						'label'             => $plural,
						'labels'            => $labels,
						'public'            => true,
						'show_ui'           => true,
						'show_in_nav_menus' => true,
						'_builtin'          => false,
					),
	
					// Given
					$taxonomy_args

				);

				// Add the taxonomy to the post type
				add_action( 'init', function() use( $taxonomy_name, $post_type_name, $args ) {
						register_taxonomy( $taxonomy_name, $post_type_name, $args );
					}
				);
			} else {
				add_action( 'init', function() use( $taxonomy_name, $post_type_name ) {
						register_taxonomy_for_object_type( $taxonomy_name, $post_type_name );
					}
				);
			}
		}
	}

	/**
	 * Convert text string to be human readable (convert `_` to spaces)
	 * 
	 * @param  string $string Text to beautify
	 * @return string         Beautified text
	 */
	public static function beautify( $string ) {
		return ucwords( str_replace( '_', ' ', $string ) );
	}
	
	/**
	 * Convert text string to be program readable
	 *
	 * @param  string $string Text to uglify
	 * @return string         Uglified text
	 */
	public static function uglify( $string ) {
		return strtolower( str_replace( ' ', '_', $string ) );
	}

	/**
	 * Convert word into plural form
	 *
	 * @param  string $string Singular text
	 * @return string         Plural text
	 */
	public static function pluralize( $string ) {
		$last = substr( $string, -1 );
		$nextlast = substr( $string, -2, 1 );
		$vowel = array( 'a', 'e', 'i', 'o', 'u' );

		if ( $last == 'y' && ! in_array( $nextlast, $vowel ) ) {
			$cut = substr( $string, 0, -1 );
			// remove "y" and add "ies"
			$plural = $cut . 'ies';
		} else {
			// just attach an s
			$plural = $string . 's';
		}

		return $plural;
	}

	/**
	 * Convert text to be used in uri (convert `_` to `-` and pluralize it)
	 *
	 * @param  string $string Programatic text
	 * @return string         Text for uri
	 */
	public static function slugify( $string ) {
		$string = strtolower( str_replace( '_', '-', $string ) );
		
		return self::pluralize( $string );
	}
}
