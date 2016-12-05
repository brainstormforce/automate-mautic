<?php

/**
 * White labeling for the builder.
 *
 * @since 1.8
 */
final class BSFMBranding {
	/**
	 * @return void
	 */
	static public function init() {
		add_filter( 'all_plugins', __CLASS__ . '::bsfm_plugins_page' );
	}

	/**
	 * Branding on the plugins page.
	 *
	 * @since 1.0.0
	 * @param array $plugins An array data for each plugin.
	 * @return array
	 */
	static public function bsfm_plugins_page($plugins) {

		$branding = BB_Ultimate_Addon_Helper::get_builder_uabb_branding();
		$basename = plugin_basename( BSF_MAUTIC_PLUGIN_DIR . 'mauticpress.php' );
		
		if ( isset( $plugins[ $basename ] ) && is_array( $branding ) ) {
			$plugin_name = ( array_key_exists( 'bsfm-plugin-name', $branding ) ) ? $branding['bsfm-plugin-name'] : '';
			$plugin_desc = ( array_key_exists( 'bsfm-plugin-desc', $branding ) ) ? $branding['bsfm-plugin-desc'] : '';
			$author_name = ( array_key_exists( 'bsfm-author-name', $branding ) ) ? $branding['bsfm-author-name'] : '';
			$author_url  = ( array_key_exists( 'bsfm-author-url', $branding ) )  ? $branding['bsfm-author-url'] : '';
			
			if ( $plugin_name != '' ) {
				$plugins[ $basename ]['Name']  = $plugin_name;
				$plugins[ $basename ]['Title'] = $plugin_name;
			}

			if ( $plugin_desc != '' ) {
            	$plugins[ $basename ]['Description'] = $plugin_desc;
			}

			if ( $author_name != '' ) {
				$plugins[ $basename ]['Author']     = $author_name;
				$plugins[ $basename ]['AuthorName'] = $author_name;
			}

			if ( $author_url != '' ) {
				$plugins[ $basename ]['AuthorURI'] = $author_url;
				$plugins[ $basename ]['PluginURI'] = $author_url;
			}
		}
		return $plugins;
	}
}
BSFMBranding::init();
