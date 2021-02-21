<?php


namespace Kntnt\Style_Editor;


final class Plugin extends Abstract_Plugin {

	use Directories;
	use File_Save;
	use Logger;
	use Options;

	public function classes_to_load() {
		return [
			'public' => [
				'init' => [
					'Load_CSS',
				],
			],
			'admin' => [
				'admin_menu' => [
					'Editor_Page',
				],
				'admin_enqueue_scripts' => [
					'Load_Editor',
				],
			],
		];
	}

}
