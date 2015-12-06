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
				'name'               => _x( $plural, 'post type general name' ),
				'singular_name'      => _x( $name, 'post type singular name' ),
				'add_new'            => _x( 'Add New', $name ),
				'add_new_item'       => __( 'Add New ' . $name ),
				'edit_item'          => __( 'Edit ' . $name ),
				'new_item'           => __( 'New ' . $name ),
				'all_items'          => __( 'All ' . $plural ),
				'view_item'          => __( 'View ' . $name ),
				'search_items'       => __( 'Search ' . $plural ),
				'not_found'          => __( 'No ' . $plural . ' found' ),
				'not_found_in_trash' => __( 'No ' . $plural . ' found in Trash' ),
				'parent_item_colon'  => '',
				'menu_name'          => $plural
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
		$name 	= self::beautify( $this->post_type_name );
		
		$messages[$this->post_type_name] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( $name . ' updated. <a href="%s">View ' . strtolower( $name ) . '</a>' ),  esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.' ),
			3 => __( 'Custom field deleted.' ),
			4 => __( $name . ' updated.' ),
			5 => isset( $_GET['revision'] ) ? sprintf( __($name . ' restored to revision from %s' ),  wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( $name . ' published. <a href="%s">View ' . strtolower( $name ) . '</a>'), esc_url( get_permalink( $post_ID ) ) ),
			7 => __( 'Page saved.' ),
			8 => sprintf( __( $name . ' submitted. <a target="_blank" href="%s">Preview ' . strtolower( $name ) . '</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( $name . ' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . strtolower( $name ) . '</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( $name . ' draft updated. <a target="_blank" href="%s">Preview ' . strtolower( $name ) . '</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
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
				$name		= self::beautify( $name );
				$plural	= self::pluralize( $name );
				// Default labels, overwrite them with the given labels.
				$labels = array_merge(
					// Default
					array(
						'name'              => _x( $plural, 'taxonomy general name' ),
						'singular_name'     => _x( $name, 'taxonomy singular name' ),
						'search_items'      => __( 'Search ' . $plural ),
						'all_items'         => __( 'All ' . $plural ),
						'parent_item'       => __( 'Parent ' . $name ),
						'parent_item_colon' => __( 'Parent ' . $name . ':' ),
						'edit_item'         => __( 'Edit ' . $name ), 
						'update_item'       => __( 'Update ' . $name ),
						'add_new_item'      => __( 'Add New ' . $name ),
						'new_item_name'     => __( 'New ' . $name . ' Name' ),
						'menu_name'         => __( $name ),
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