<?php

namespace Kntnt\Style_Editor;

class Settings extends Abstract_Settings {

	protected function menu_title() {
		return __( 'Kntnt Style Editor', 'kntnt-style-editor' );
	}

	protected function fields() {

		$fields['css'] = [
			'type' => 'text area',
			'label' => __( 'Style', 'kntnt-style-editor' ),
			'rows' => 25,
			'cols' => 80,
		];

		$fields['submit'] = [
			'type' => 'submit',
		];

		return $fields;

	}

	protected final function actions_after_saving( $opt, $fields ) {
		if ( $opt['css'] ) {
			$info = Plugin::save_to_file( $opt['css'], 'css' );
			Plugin::set_option( 'css_file_info', $info );
		}
		else if ( $css_file_info = Plugin::option( 'css_file_info' ) ) {
			@unlink( $css_file_info['file'] );
			Plugin::delete_option( 'css_file_info' );
			Plugin::debug( 'Deleted "%s".', $css_file_info['file'] );
		}
	}

}
