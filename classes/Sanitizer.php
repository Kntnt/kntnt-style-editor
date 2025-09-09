<?php

declare( strict_types = 1 );

namespace Kntnt\Style_Editor;

/**
 * CSS Content Sanitizer
 *
 * This class safely sanitizes CSS content while preserving valid CSS syntax that might
 * otherwise be mistaken for HTML tags.
 *
 * The primary use case is handling modern CSS features like Custom Properties (@property)
 * which use angle-bracket syntax (e.g., <angle>, <length>, <color>) that standard
 * HTML stripping functions would incorrectly remove.
 *
 * @example
 * $sanitizer = new \Kntnt\Style_Editor\Sanitizer();
 * $clean = $sanitizer->sanitize('@property --rot { syntax: "<angle>"; } <script>alert("XSS")</script>');
 * // Result: '@property --rot { syntax: "<angle>"; }' (script tag removed, <angle> preserved)
 */
final class Sanitizer {

	/**
	 * Placeholder markers used to temporarily replace CSS data types.
	 */
	private const PLACEHOLDER_PREFIX = '__CSS_PRESERVE_';

	private const PLACEHOLDER_SUFFIX = '__';

	/**
	 * Static cache of HTML tag names for performance.
	 */
	private static array $htmlTagsCache = [];

	/**
	 * Storage for preserved CSS values.
	 */
	private array $preservedValues = [];

	/**
	 * Counter for generating unique placeholders.
	 */
	private int $preservedCounter = 0;

	/**
	 * Main entry point for sanitizing CSS content.
	 *
	 * It performs initial cleaning by trimming whitespace and stripping null bytes
	 * before passing the content to the core sanitizer logic.
	 *
	 * @param string|null $css The CSS content to sanitize.
	 *
	 * @return string Sanitized CSS.
	 */
	public function sanitize( ?string $css ): string {

		// Early return for null or empty input.
		if ( $css === null || $css === '' ) {
			return '';
		}

		// Remove leading and trailing whitespace.
		$css = trim( $css );

		// Strip null bytes.
		$css = str_replace( '\0', '', $css );

		// Run the core sanitization logic and return the result.
		return $this->runSanitization( $css );

	}

	/**
	 * The core sanitization process.
	 *
	 * Orchestrates the sanitization process, ensuring dangerous HTML is removed
	 * while legitimate CSS syntax is preserved.
	 *
	 * @param string $cssString The CSS content to sanitize.
	 *
	 * @return string Sanitized CSS with HTML removed but CSS syntax preserved.
	 */
	private function runSanitization( string $cssString ): string {

		// Reset instance state for each run to ensure no data leaks between calls.
		$this->preservedValues = [];
		$this->preservedCounter = 0;

		// Remove complete HTML blocks that could contain malicious content.
		$cssString = $this->removeHtmlBlocks( $cssString );

		// Find and preserve legitimate CSS data type syntax.
		$cssString = $this->preserveCssDataTypes( $cssString );

		// Remove any remaining HTML tags.
		$cssString = strip_tags( $cssString );

		// Restore the preserved CSS data types.
		return $this->restorePreservedValues( $cssString );

	}

	/**
	 * Removes complete HTML blocks that could pose security risks (e.g., <script>).
	 *
	 * @param string $content The content to clean.
	 *
	 * @return string Content with dangerous HTML blocks removed.
	 */
	private function removeHtmlBlocks( string $content ): string {
		return preg_replace( '@<(script|style|noscript|iframe|object|embed)(?:\s[^>]*)?>.*?</\1>@si', '', $content ) ?? $content;
	}

	/**
	 * Identifies and temporarily replaces valid CSS data type syntax with placeholders.
	 *
	 * @param string $content The content to process.
	 *
	 * @return string Content with CSS data types replaced by placeholders.
	 */
	private function preserveCssDataTypes( string $content ): string {
		return preg_replace_callback( '/<([a-zA-Z_-][a-zA-Z0-9_-]*)>/', function ( $matches ) use ( $content ) {

			$fullMatch = $matches[0][0];
			$identifier = $matches[1][0];
			$position = $matches[0][1];

			if ( $this->shouldPreserveCssType( $content, $position, $identifier ) ) {
				$placeholder = $this->generatePlaceholder();
				$this->preservedValues[ $placeholder ] = $fullMatch;
				return $placeholder;
			}

			return $fullMatch;

		}, $content, - 1, $count, PREG_OFFSET_CAPTURE ) ?? $content;
	}

	/**
	 * Determines whether a matched pattern should be preserved as CSS syntax.
	 *
	 * @param string $content    The full content being processed.
	 * @param int    $position   The position of the match in the content.
	 * @param string $identifier The identifier between angle brackets (e.g., "angle" from "<angle>").
	 *
	 * @return bool True if this should be preserved as CSS syntax.
	 */
	private function shouldPreserveCssType( string $content, int $position, string $identifier ): bool {
		if ( $this->looksLikeHtmlTag( $identifier ) && ! $this->isInCssPropertyContext( $content, $position ) ) {
			return false;
		}
		return $this->isInCssPropertyContext( $content, $position );
	}

	/**
	 * Checks if the matched pattern is within a CSS @param string $content The full content.
	 *
	 * @param int $position Position of the potential CSS data type.
	 *
	 * @return bool True if position is within a CSS @property syntax context.
	 */
	private function isInCssPropertyContext( string $content, int $position ): bool {
		$substringBefore = substr( $content, 0, $position );
		$lastProperty = strrpos( $substringBefore, '@property' );
		if ( $lastProperty === false ) {
			return false;
		}

		$lastSyntax = strrpos( $substringBefore, 'syntax:' );
		if ( $lastSyntax === false || $lastSyntax < $lastProperty ) {
			return false;
		}

		$substringAfterSyntax = substr( $substringBefore, $lastSyntax );
		if ( str_contains( $substringAfterSyntax, ';' ) || str_contains( $substringAfterSyntax, '}' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if an identifier matches a known HTML tag name.
	 *
	 * @param string $identifier The identifier to check (without angle brackets).
	 *
	 * @return bool True if this matches a known HTML tag name.
	 */
	private function looksLikeHtmlTag( string $identifier ): bool {
		if ( empty( self::$htmlTagsCache ) ) {
			self::$htmlTagsCache = array_flip( [
				'a',
				'abbr',
				'address',
				'area',
				'article',
				'aside',
				'audio',
				'b',
				'base',
				'bdi',
				'bdo',
				'blockquote',
				'body',
				'br',
				'button',
				'canvas',
				'caption',
				'cite',
				'code',
				'col',
				'colgroup',
				'data',
				'datalist',
				'dd',
				'del',
				'details',
				'dfn',
				'dialog',
				'div',
				'dl',
				'dt',
				'em',
				'embed',
				'fieldset',
				'figcaption',
				'figure',
				'footer',
				'form',
				'h1',
				'h2',
				'h3',
				'h4',
				'h5',
				'h6',
				'head',
				'header',
				'hr',
				'html',
				'i',
				'iframe',
				'img',
				'input',
				'ins',
				'kbd',
				'label',
				'legend',
				'li',
				'link',
				'main',
				'map',
				'mark',
				'meta',
				'meter',
				'nav',
				'noscript',
				'object',
				'ol',
				'optgroup',
				'option',
				'output',
				'p',
				'param',
				'picture',
				'pre',
				'progress',
				'q',
				'rp',
				'rt',
				'ruby',
				's',
				'samp',
				'script',
				'section',
				'select',
				'small',
				'source',
				'span',
				'strong',
				'style',
				'sub',
				'summary',
				'sup',
				'svg',
				'table',
				'tbody',
				'td',
				'template',
				'textarea',
				'tfoot',
				'th',
				'thead',
				'time',
				'title',
				'tr',
				'track',
				'u',
				'ul',
				'var',
				'video',
				'wbr',
			] );
		}
		return isset( self::$htmlTagsCache[ strtolower( $identifier ) ] );
	}

	/**
	 * Generates a unique placeholder string.
	 *
	 * @return string A unique placeholder string.
	 */
	private function generatePlaceholder(): string {
		return self::PLACEHOLDER_PREFIX . base_convert( (string) $this->preservedCounter ++, 10, 36 ) . '_' . substr( bin2hex( random_bytes( 4 ) ), 0, 8 ) . self::PLACEHOLDER_SUFFIX;
	}

	/**
	 * Restores preserved CSS values from their placeholders.
	 *
	 * @param string $content Content with placeholders.
	 *
	 * @return string Content with placeholders replaced by original CSS syntax.
	 */
	private function restorePreservedValues( string $content ): string {
		if ( empty( $this->preservedValues ) ) {
			return $content;
		}
		return strtr( $content, $this->preservedValues );
	}

}