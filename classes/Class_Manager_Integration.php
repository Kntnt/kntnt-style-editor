<?php

declare( strict_types = 1 );

namespace Kntnt\Style_Editor;

/**
 * Integration with CSS Class Manager plugin.
 *
 * Parses CSS comments for @class-manager annotations and makes those classes
 * available to the CSS Class Manager plugin's autocomplete feature.
 *
 * @package Kntnt\Style_Editor
 * @since   2.0.0
 */
final class Class_Manager_Integration {

	/**
	 * Adds annotated CSS classes to the CSS Class Manager.
	 *
	 * Parses the custom CSS for @class-manager annotations and adds them
	 * to the list of available classes for the CSS Class Manager plugin.
	 *
	 * @param array $class_names Existing class names from CSS Class Manager.
	 *
	 * @return array Modified class names array with additional classes.
	 * @since 2.0.0
	 */
	public function add_classes_to_manager( array $class_names ): array {

		// Get the stored CSS content
		$custom_css = Plugin::get_option()['css'] ?? '';
		if ( empty( $custom_css ) ) {
			return $class_names;
		}

		// Parse CSS for @class-manager annotations
		$found_classes = $this->parse_css_for_classes( $custom_css );
		if ( empty( $found_classes ) ) {
			return $class_names;
		}

		// Merge new classes with existing ones and return
		return array_merge( $class_names, $found_classes );

	}

	/**
	 * Parses CSS content for @class-manager annotations.
	 *
	 * Searches through all CSS comment blocks for @class-manager tags
	 * and extracts class names and descriptions.
	 *
	 * @param string $css The CSS content to parse.
	 *
	 * @return array{name: string, description: string}[] Array of parsed class definitions.
	 * @since 2.0.0
	 */
	private function parse_css_for_classes( string $css ): array {

		$classes = [];

		// Regex that matches /* ... */ comment blocks
		// Handles nested asterisks and special characters properly
		$comment_pattern = '/\/\*(?:[^*]++|\*(?!\/))*+\*\//';

		// Find all comment blocks in the CSS
		if ( ! preg_match_all( $comment_pattern, $css, $comment_matches ) ) {
			return [];
		}

		// Process each comment block for @class-manager annotations
		foreach ( $comment_matches[0] as $comment_block ) {

			// Remove comment delimiters /* and */
			$comment_content = trim( substr( $comment_block, 2, - 2 ) );

			// Look for @class-manager lines within the comment
			// Handles various whitespace and asterisk patterns
			$class_manager_pattern = '/^[ \t]*\*?[ \t]*@class-manager\s+(.+)$/m';

			if ( preg_match_all( $class_manager_pattern, $comment_content, $matches ) ) {
				// Process each @class-manager line found
				foreach ( $matches[1] as $class_definition ) {
					$parsed_class = $this->parse_class_definition( $class_definition );
					if ( $parsed_class ) {
						$classes[] = $parsed_class;
					}
				}
			}
		}

		return $classes;

	}

	/**
	 * Parses a single @class-manager definition line.
	 *
	 * Extracts the class name and optional description from a line like:
	 * "class-name | Optional description"
	 *
	 * @param string $class_definition The class definition line to parse.
	 *
	 * @return array{name: string, description: string}|null Parsed class data or null if invalid.
	 * @since 2.0.0
	 */
	private function parse_class_definition( string $class_definition ): ?array {

		// Split on pipe character to separate class name from description
		$parts = explode( '|', $class_definition, 2 );
		$class_name = trim( $parts[0] );

		// Validate class name using basic CSS class name rules
		// Must start with letter, can contain letters, numbers, hyphens, underscores
		if ( empty( $class_name ) || ! preg_match( '/^[a-zA-Z][\w-]*$/', $class_name ) ) {
			return null;
		}

		// Extract description if provided
		$description = isset( $parts[1] ) ? trim( $parts[1] ) : '';

		return [
			'name' => $class_name,
			'description' => $description,
		];
	}

}