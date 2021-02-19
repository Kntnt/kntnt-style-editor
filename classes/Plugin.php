<?php


namespace Kntnt\Style_Editor;


final class Plugin extends Abstract_Plugin {

	use Logger;
	use Options;
	use File_Save;

	public function classes_to_load() {
		return [
			'public' => [
				'wp_enqueue_scripts' => [
					'Load_CSS',
				],
			],
			'admin' => [
				'init' => [
					'Settings',
				],
				'admin_enqueue_scripts' => [
					'Load_Editor',
				],
			],
		];
	}

}
