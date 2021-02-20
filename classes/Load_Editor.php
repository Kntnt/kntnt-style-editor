<?php


namespace Kntnt\Style_Editor;


class Load_Editor {

	public function run() {

		// Editor
		$settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );
		wp_localize_script( 'jquery', 'cm_settings', $settings );
		wp_enqueue_script( 'kntnt-style-editor.js', Plugin::plugin_url( 'js/kntnt-style-editor.js' ), [ 'jquery' ], Plugin::version(), true );
		Plugin::debug( 'Enqueued CodeMirror with following settings: %s', $settings );

		// Editor style
		$name = 'kntnt-style-editor.css';
		$url = Plugin::plugin_url( "css/$name" );
		wp_enqueue_style( $name, $url, [], Plugin::version() );
		Plugin::debug( 'Enqueued CodeMirror style: %s', $url );

	}

}