<?php


namespace Kntnt\Style_Editor;


class Load_CSS {

	public function run() {
		$url = Plugin::upload_url( 'kntnt-style-editor/kntnt-style-editor.css' );
		wp_enqueue_style( 'kntnt-style-editor.css', $url, [], Plugin::version() );
		Plugin::debug( 'Enqueued %s', $url );
	}

}
