<?php

/**
 * Class for adding a new settings page to the WordPress admin, under Settings.
 *
 * @package SixTenPressIsotope
 */
class SixTenPressIsotopeSettings {

	/**
	 * Option registered by plugin.
	 * @var array
	 */
	protected $setting;

	/**
	 * Slug for settings page.
	 * @var string
	 */
	protected $page = 'sixtenpressisotope';

	protected $post_types = array();

	/**
	 * Settings fields registered by plugin.
	 * @var array
	 */
	protected $fields;

	/**
	 * add a submenu page under settings
	 * @return submenu SixTen Press Isotope settings page
	 * @since  1.4.0
	 */
	public function do_submenu_page() {

		add_options_page(
			__( 'SixTen Press Isotope Settings', 'sixtenpress-isotope' ),
			__( 'SixTen Press Isotope', 'sixtenpress-isotope' ),
			'manage_options',
			$this->page,
			array( $this, 'do_settings_form' )
		);

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( "load-settings_page_{$this->page}", array( $this, 'help' ) );
	}

	/**
	 * Output the plugin settings form.
	 *
	 * @since 1.0.0
	 */
	public function do_settings_form() {

		$this->setting = $this->get_setting();
		echo '<div class="wrap">';
		echo '<h1>' . esc_attr( get_admin_page_title() ) . '</h1>';
		echo '<form action="options.php" method="post">';
		settings_fields( $this->page );
		do_settings_sections( $this->page );
		wp_nonce_field( "{$this->page}_save-settings", "{$this->page}_nonce", false );
		submit_button();
		echo '</form>';
		echo '</div>';

	}

	/**
	 * Add new fields to wp-admin/options-general.php?page=sixtenpressisotope
	 *
	 * @since 2.2.0
	 */
	public function register_settings() {

		register_setting( $this->page, $this->page, array( $this, 'do_validation_things' ) );

		$this->register_sections();

	}

	/**
	 * @return array Setting for plugin, or defaults.
	 */
	public function get_setting() {

		$defaults = array(
			'posts_per_page' => get_option( 'posts_per_page', 10 ),
			'style'          => 1,
		);

		$setting = get_option( $this->page, $defaults );

		return $setting;
	}

	/**
	 * Add isotope support to the relevant post types.
	 */
	public function add_post_type_support() {
		$args     = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output     = 'names';
		$post_types = get_post_types( $args, $output );
		foreach( $post_types as $post_type ) {
			if ( isset( $this->setting[ $post_type ]['support'] ) && $this->setting[ $post_type ]['support'] ) {
				add_post_type_support( $post_type, 'sixtenpress-isotope' );
			}
		}
	}

	/**
	 * Register sections for settings page.
	 *
	 * @since 3.0.0
	 */
	protected function register_sections() {

		$sections = array(
			'general' => array(
				'id'    => 'general',
				'title' => __( 'General Settings', 'sixtenpress-isotope' ),
			),
		);
		$args     = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output   = 'names';

		$this->post_types   = get_post_types( $args, $output );
		$this->post_types[] = 'post';

		if ( $this->post_types ) {

			$sections['cpt'] = array(
				'id'    => 'cpt',
				'title' => __( 'Isotope Settings for Content Types', 'sixtenpress-isotope' ),
			);
		}

		foreach ( $sections as $section ) {
			add_settings_section(
				$section['id'],
				$section['title'],
				array( $this, $section['id'] . '_section_description' ),
				$this->page
			);
		}

		$this->register_fields( $sections );

	}

	/**
	 * Register settings fields
	 *
	 * @param  settings sections $sections
	 *
	 * @return fields           settings fields
	 *
	 * @since 3.0.0
	 */
	protected function register_fields( $sections ) {

		$this->fields = array(
			array(
				'id'       => 'posts_per_page',
				'title'    => __( 'Number of Posts to Show on Isotope Archives', 'sixtenpress-isotope' ),
				'callback' => 'do_number',
				'section'  => 'general',
				'args'     => array(
					'setting' => 'posts_per_page',
					'min'     => 1,
					'max'     => 200,
					'label'   => __( 'Posts per Page', 'sixtenpress-isotope' ),
				),
			),
			array(
				'id'       => 'style',
				'title'    => __( 'Plugin Stylesheet', 'sixtenpress-isotope' ),
				'callback' => 'do_checkbox',
				'section'  => 'general',
				'args'     => array(
					'setting' => 'style',
					'label'   => __( 'Use the plugin styles?', 'sixtenpress-isotope' ),
				),
			),
		);
		if ( $this->post_types ) {

			foreach ( $this->post_types as $post_type ) {
				$object = get_post_type_object( $post_type );
				$label  = $object->labels->name;
				$this->fields[] = array(
					'id'       => '[post_types]' . esc_attr( $post_type ),
					'title'    => esc_attr( $label ),
					'callback' => 'set_post_type_options',
					'section'  => 'cpt',
					'args'     => array( 'post_type' => $post_type ),
				);
			}
		}

		foreach ( $this->fields as $field ) {
			add_settings_field(
				'[' . $field['id'] . ']',
				sprintf( '<label for="%s">%s</label>', $field['id'], $field['title'] ),
				array( $this, $field['callback'] ),
				$this->page,
				$sections[$field['section']]['id'],
				empty( $field['args'] ) ? array() : $field['args']
			);
		}
	}

	/**
	 * Callback for general plugin settings section.
	 *
	 * @since 2.4.0
	 */
	public function general_section_description() {
		$description = __( 'You can set the default isotope settings here.', 'sixtenpress-isotope' );
		printf( '<p>%s</p>', wp_kses_post( $description ) );
	}

	public function cpt_section_description() {
		$description = __( 'Set the isotope settings for each post type.', 'sixtenpress-isotope' );
		printf( '<p>%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * Generic callback to create a checkbox setting.
	 *
	 * @since 3.0.0
	 */
	public function do_checkbox( $args ) {
		$setting = isset( $this->setting[ $args['setting'] ] ) ? $this->setting[ $args['setting'] ] : 0;
		if ( isset( $args['setting_name'] ) ) {
			if ( ! isset( $this->setting[ $args['post_type'] ] ) ) {
				$this->setting[ $args['post_type'] ] = array();
			}
			$setting = isset( $this->setting[ $args['post_type'] ][ $args['setting_name'] ] ) ? $this->setting[ $args['post_type'] ][ $args['setting_name'] ] : 0;
		}
		if ( ! isset( $this->setting[ $args['setting'] ] ) ) {
			$this->setting[ $args['setting'] ] = 0;
		}
		printf( '<input type="hidden" name="%s[%s]" value="0" />', esc_attr( $this->page ), esc_attr( $args['setting'] ) );
		printf( '<label for="%1$s[%2$s]"><input type="checkbox" name="%1$s[%2$s]" id="%1$s[%2$s]" value="1" %3$s class="code" />%4$s</label>',
			esc_attr( $this->page ),
			esc_attr( $args['setting'] ),
			checked( 1, esc_attr( $setting ), false ),
			esc_attr( $args['label'] )
		);
		$this->do_description( $args['setting'] );
	}

	/**
	 * Generic callback to create a number field setting.
	 *
	 * @since 3.0.0
	 */
	public function do_number( $args ) {
		$setting = isset( $this->setting[ $args['setting'] ] ) ? $this->setting[ $args['setting'] ] : 0;
		if ( isset( $args['setting_name'] ) ) {
			if ( ! isset( $this->setting[ $args['post_type'] ] ) ) {
				$this->setting[ $args['post_type'] ] = array();
			}
			$setting = isset( $this->setting[ $args['post_type'] ][ $args['setting_name'] ] ) ? $this->setting[ $args['post_type'] ][ $args['setting_name'] ] : 0;
		}
		if ( ! isset( $setting ) ) {
			$setting = 0;
		}
		printf( '<label for="%s[%s]">%s</label>', esc_attr( $this->page ), esc_attr( $args['setting'] ), esc_attr( $args['label'] ) );
		printf( '<input type="number" step="1" min="%1$s" max="%2$s" id="%5$s[%3$s]" name="%5$s[%3$s]" value="%4$s" class="small-text" />',
			(int) $args['min'],
			(int) $args['max'],
			esc_attr( $args['setting'] ),
			esc_attr( $setting ),
			esc_attr( $this->page )
		);
		$this->do_description( $args['setting'] );

	}

	/**
	 * Generic callback to create a select/dropdown setting.
	 *
	 * @since 3.0.0
	 */
	public function do_select( $args ) {
		$function = 'pick_' . $args['options'];
		$options  = $this->$function(); ?>
		<select id="sixtenpressisotope[<?php echo esc_attr( $args['setting'] ); ?>]"
		        name="sixtenpressisotope[<?php echo esc_attr( $args['setting'] ); ?>]">
			<?php
			foreach ( (array) $options as $name => $key ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $name ), selected( $name, $this->setting[$args['setting']], false ), esc_attr( $key ) );
			} ?>
		</select> <?php
		$this->do_description( $args['setting'] );
	}

	/**
	 * Generic callback to create a text field.
	 *
	 * @since 3.0.0
	 */
	public function do_text_field( $args ) {
		printf( '<input type="text" id="%3$s[%1$s]" name="%3$s[%1$s]" value="%2$s" class="regular-text" />', esc_attr( $args['setting'] ), esc_attr( $this->setting[$args['setting']] ), esc_attr( $this->page ) );
		$this->do_description( $args['setting'] );
	}

	/**
	 * Generic callback to display a field description.
	 *
	 * @param  string $args setting name used to identify description callback
	 *
	 * @return string       Description to explain a field.
	 */
	protected function do_description( $args ) {
		$function = $args . '_description';
		if ( ! method_exists( $this, $function ) ) {
			return;
		}
		$description = $this->$function();
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	public function set_post_type_options( $args ) {
		$post_type = $args['post_type'];
		if ( ! isset( $this->setting['post_type'][$post_type] ) ) {
			$this->setting['post_type'][$post_type] = array();
		}
		$setting_name = 'support';
		$checkbox_args = array(
			'setting'      => "{$post_type}][{$setting_name}",
			'label'        => __( 'Enable Isotope for this post type?', 'sixtenpress-isotope' ),
			'post_type'    => $post_type,
			'setting_name' => $setting_name,
		);
		$this->do_checkbox( $checkbox_args );
		echo '<br />';
		$setting_name = 'gutter';
		$gutter_args = array(
			'setting'      => "{$post_type}][{$setting_name}",
			'min'          => 0,
			'max'          => 24,
			'label'        => __( 'Gutter Width', 'sixtenpress-isotope' ),
			'post_type'    => $post_type,
			'setting_name' => $setting_name,
		);
		$this->do_number( $gutter_args );
		echo '<br />';
		$taxonomies = get_object_taxonomies( $post_type, 'names' );
		$taxonomies = 'post' === $post_type ? array( 'category' ) : $taxonomies;
		foreach ( $taxonomies as $taxonomy ) {
			$tax_object = get_taxonomy( $taxonomy );
			$tax_args   = array(
				'setting'      => "{$post_type}][{$taxonomy}",
				'label'        => sprintf( __( 'Add a filter for %s', 'sixtenpress-isotope' ), $tax_object->labels->name ),
				'post_type'    => $post_type,
				'setting_name' => $taxonomy,
			);
			$this->do_checkbox( $tax_args );
			echo '<br />';
		}
	}

	/**
	 * Validate all settings.
	 *
	 * @param  array $new_value new values from settings page
	 *
	 * @return array            validated values
	 *
	 * @since 3.0.0
	 */
	public function do_validation_things( $new_value ) {

		if ( empty( $_POST[$this->page . '_nonce'] ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'sixtenpress-isotope' ) );
		}

		check_admin_referer( "{$this->page}_save-settings", "{$this->page}_nonce" );

		foreach ( $this->fields as $field ) {
			switch ( $field['callback'] ) {
				case 'do_checkbox':
					$new_value[ $field['id'] ] = $this->one_zero( $new_value[ $field['id'] ] );
					break;

				case 'do_select':
					$new_value[ $field['id'] ] = esc_attr( $new_value[ $field['id'] ] );
					break;

				case 'do_number':
					$new_value[ $field['id'] ] = (int) $new_value[ $field['id'] ];
					break;
			}
		}

		return $new_value;

	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 2.4.0
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
	 *
	 * @return integer 1 or 0.
	 */
	protected function one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}

	/**
	 * Help tab for settings screen
	 * @return help tab with verbose information for plugin
	 *
	 * @since 2.4.0
	 */
	public function help() {
		$screen = get_current_screen();

		$general_help = '<h3>' . __( 'RSS/Email Image Width', 'sixtenpress-isotope' ) . '</h3>';
		$general_help .= '<p>' . __( 'If you have customized your emails to be a nonstandard width, or you are using a template with a sidebar, you will want to change your RSS/Email Image width. The default is 560 pixels, which is the content width of a standard single column email (600 pixels wide with 20 pixels padding on the content). Mad Mimi users should set this to 530.', 'sixtenpress-isotope' ) . '</p>';
		$general_help .= '<p class="description">' . __( 'Note: Changing the width here will not affect previously uploaded images, but it will affect the max-width applied to images\' style.', 'sixtenpress-isotope' ) . '</p>';

		$full_text_help = '<h3>' . __( 'Simplify Feed', 'sixtenpress-isotope' ) . '</h3>';
		$full_text_help .= '<p>' . __( 'If you are not concerned about sending your feed out over email and want only your galleries changed from thumbnails to large images, select Simplify Feed.', 'sixtenpress-isotope' ) . '</p>';

		$full_text_help .= '<h3>' . __( 'Alternate Feed', 'sixtenpress-isotope' ) . '</h3>';
		$full_text_help .= '<p>' . __( 'By default, the SixTen Press Isotope plugin modifies every feed from your site. If you want to leave your main feed untouched and set up a totally separate feed for emails only, select this option.', 'sixtenpress-isotope' ) . '</p>';
		$full_text_help .= '<p>' . __( 'If you use custom post types with their own feeds, the alternate feed method will work even with them.', 'sixtenpress-isotope' ) . '</p>';

		$full_text_help .= '<h3>' . __( 'Featured Image', 'sixtenpress-isotope' ) . '</h3>';
		$full_text_help .= '<p>' . __( 'Some themes and/or plugins add the featured image to the front end of your site, but not to the feed. If you are using a full text feed and want the featured image to be added to it, use this setting. I definitely recommend double checking your feed after enabling this, in case your theme or another plugin already adds the featured image to the feed, because you may end up with duplicate images.', 'sixtenpress-isotope' ) . '</p>';
		$full_text_help .= '<p>' . __( 'If you are using the Alternate Feed setting, the featured image will be added to both feeds, but the full size version will be used on your unprocessed feed.', 'sixtenpress-isotope' ) . '</p>';
		if ( class_exists( 'Display_Featured_Image_Genesis' ) ) {
			$full_text_help .= '<p class="description">' . sprintf( __( 'As a <a href="%s">Display Featured Image for Genesis</a> user, you already have the option to add featured images to your feed using that plugin. If you have both plugins set to add the featured image to your full text feed, this plugin will step aside and not output the featured image until you have deactivated that setting in the other. This plugin gives you more control over the featured image output in the feed.', 'sixtenpress-isotope' ), esc_url( admin_url( 'themes.php?page=displayfeaturedimagegenesis' ) ) ) . '</p>';
		}
		$full_text_help .= '<p>' . __( 'Note: the plugin will attempt to see if the image is already in your post content. If it is, the featured image will not be added to the feed as it would be considered a duplication.', 'sixtenpress-isotope' ) . '</p>';

		$general_help .= '<h3>' . __( 'Featured Image Size', 'sixtenpress-isotope' ) . '</h3>';
		$general_help .= '<p>' . __( 'Select which size image you would like to use in your excerpt/summary.', 'sixtenpress-isotope' ) . '</p>';

		$general_help .= '<h3>' . __( 'Featured Image Alignment', 'sixtenpress-isotope' ) . '</h3>';
		$general_help .= '<p>' . __( 'Set the alignment for your post\'s featured image.', 'sixtenpress-isotope' ) . '</p>';

		$general_help .= '<h3>' . __( 'Process Both Feeds', 'sixtenpress-isotope' ) . '</h3>';
		$general_help .= '<p>' . __( 'Some users like to allow subscribers who use Feedly or another RSS reader to read the full post, with images, but use the summary for email subscribers. To get images processed on both, set your feed settings to Full Text, and check this option.', 'sixtenpress-isotope' ) . '</p>';

		$summary_help = '<h3>' . __( 'Excerpt Length', 'sixtenpress-isotope' ) . '</h3>';
		$summary_help .= '<p>' . __( 'Set the target number of words you want your excerpt to generally have. The plugin will count that many words, and then add on as many as are required to ensure your summary ends in a complete sentence.', 'sixtenpress-isotope' ) . '</p>';

		$summary_help .= '<h3>' . __( 'Read More Text', 'sixtenpress-isotope' ) . '</h3>';
		$summary_help .= '<p>' . __( 'Enter the text you want your "read more" link in your feed to contain. You can use placeholders for the post title and blog name.', 'sixtenpress-isotope' ) . '</p>';
		$summary_help .= '<p class="description">' . __( 'Hint: "Read More" is probably inadequate for your link\'s anchor text.', 'sixtenpress-isotope' ) . '</p>';

		$help_tabs = array(
			array(
				'id'      => 'sixtenpressisotope_general-help',
				'title'   => __( 'General Image Settings', 'sixtenpress-isotope' ),
				'content' => $general_help,
			),
			array(
				'id'      => 'sixtenpressisotope_full_text-help',
				'title'   => __( 'Full Text Settings', 'sixtenpress-isotope' ),
				'content' => $full_text_help,
			),
			array(
				'id'      => 'sixtenpressisotope_summary-help',
				'title'   => __( 'Summary Settings', 'sixtenpress-isotope' ),
				'content' => $summary_help,
			),
		);

		foreach ( $help_tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}

	}
}
