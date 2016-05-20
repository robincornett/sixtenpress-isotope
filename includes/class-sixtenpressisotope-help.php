<?php

/**
 * Class for adding help tab to the isotope settings.
 * @package   SixTenPressIsotope
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2016 Robin Cornett Creative, LLC
 */
class SixTenPressIsotopeHelp {

	/**
	 * Help tab for settings screen
	 *
	 * @since 1.0.0
	 */
	public function help() {
		$screen = get_current_screen();

		$general_help = '<h3>' . __( 'Number of Posts to Show on Isotope Archives', 'sixtenpress-isotope' ) . '</h3>';
		$general_help .= '<p>' . __( 'Change the number of items which show on content archives, to show more or less items than your regular archives.', 'sixtenpress-isotope' ) . '</p>';

		$general_help .= '<h3>' . __( 'Plugin Stylesheet', 'sixtenpress-isotope' ) . '</h3>';
		$general_help .= '<p>' . __( 'The plugin adds a wee bit of styling to handle the isotope layout, but if you want to do it yourself, disable the plugin style and enjoy!', 'sixtenpress-isotope' ) . '</p>';

		$cpt_help  = '<p>' . __( 'Each content type on your site will be handled uniquely. Enable Isotope, set the gutter width, and enable filters as you like.', 'sixtenpress-isotope' ) . '</p>';

		$help_tabs = array(
			array(
				'id'      => 'sixtenpressisotope_general-help',
				'title'   => __( 'General Settings', 'sixtenpress-isotope' ),
				'content' => $general_help,
			),
			array(
				'id'      => 'sixtenpressisotope_cpt-help',
				'title'   => __( 'Isotope Settings for Content Types', 'sixtenpress-isotope' ),
				'content' => $cpt_help,
			),
		);

		foreach ( $help_tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
	}
}
