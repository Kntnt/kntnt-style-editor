<?php


namespace Kntnt\Style_Editor;


abstract class Abstract_Plugin {

	static private $ns;

	static private $plugin_dir;

	static private $is_debugging = null;

	static private $instances = [];

	public function __construct() {

		// This plugin's machine name a.k.a. slug.
		self::$ns = strtr( strtolower( __NAMESPACE__ ), '_\\', '--' );

		// Path to this plugin's directory relative file system root.
		self::$plugin_dir = strtr( dirname( __DIR__ ), '\\', '/' );

		// Install script runs only on install (not activation).
		// Uninstall script runs "magically" on uninstall.
		if ( is_readable( self::$plugin_dir . '/install.php' ) ) {
			register_activation_hook( self::$plugin_dir . '/' . self::$ns . '.php', function () {
				if ( null === get_option( self::$ns, null ) ) {
					/** @noinspection PhpIncludeInspection */
					require self::$plugin_dir . '/install.php';
				}
			} );
		}

		// Setup localization if available.
		if ( is_dir( self::plugin_dir( '/languages' ) ) ) {
			add_action( 'plugins_loaded', function () {
				load_plugin_textdomain( self::$ns, false, self::$ns . '/languages' );
			} );
		}

		// Setup this plugin to run.
		foreach ( $this->classes_to_load() as $context => $hooks_and_classes ) {
			if ( $this->is_context( $context ) ) {
				foreach ( $hooks_and_classes as $hook => $classes ) {
					foreach ( $classes as $class ) {
						add_action( $hook, [ $this->instance( $class ), 'run' ] );
					}
				}
			}

		}

		// Calls the "constructor" of each trait. The "constructor" is a method
		// with the same name as the trait.
		foreach ( class_uses( $this ) as $trait ) {
			$method = substr( $trait, strrpos( $trait, '\\' ) + 1 );
			if ( method_exists( $this, $method ) ) {
				$this->$method();
			}
		}

	}

	// Returns the first created instance of the class with the provided name.
	// If no such instance exists and `$create_if_not_existing` is true, or if
	// `$create_always` is true, a new instance is created.
	public static final function instance( $class_name, $create_always = false, $create_if_not_existing = true ) {
		if ( $create_always || $create_if_not_existing && ! isset( self::$instances[ $class_name ] ) ) {
			$class = __NAMESPACE__ . '\\' . $class_name;
			$instance = new $class;
			if ( ! isset( self::$instances[ $class_name ] ) ) {
				self::$instances[ $class_name ] = $instance;
			}
			return $instance;
		}
		else {
			if ( isset( self::$instances[ $class_name ] ) ) {
				return self::$instances[ $class_name ];
			}
			else {
				throw new \LogicException( "No instance with name '$class_name'." );
			}
		}
	}

	public static final function is_using( $trait ) {
		return ( $traits = class_uses( static::class ) ) && isset( $traits[ __NAMESPACE__ . "\\$trait" ] );
	}

	// Name space of plugin.
	public static final function ns() {
		return self::$ns;
	}

	// Plugin name.
	public static final function name() {
		$key = self::$ns . '-plugin-name';
		$name = get_transient( $key );
		if ( ! $name ) {
			$name = get_plugin_data( self::plugin_dir( self::$ns . '.php' ), false, false )['Name'];
			set_transient( $key, $name, DAY_IN_SECONDS );
		}
		return $name;
	}

	// Plugin version.
	public static final function version() {
		$key = self::$ns . '-plugin-version';
		$version = get_transient( $key );
		if ( ! $version ) {
			$version = get_plugin_data( self::plugin_dir( self::$ns . '.php' ), false, false )['Version'];
			set_transient( $key, $version, DAY_IN_SECONDS );
		}
		return $version;
	}

	// This plugin's path relative file system root, with no trailing slash.
	// If $rel_path is given, with or without leading slash, it is appended
	// with leading slash.
	public static final function plugin_dir( $rel_path = '' ) {
		return self::str_join( self::$plugin_dir, $rel_path );
	}

	// This plugin's URL with no trailing slash. If $rel_path is given, with
	// or without leading slash, it is appended with leading slash.
	public static final function plugin_url( $rel_path = '' ) {
		static $plugin_url = null;
		if ( is_null( $plugin_url ) ) {
			$plugin_url = plugins_url( '', self::$plugin_dir . '/' . self::$ns . '.php' );
		}
		return self::str_join( $plugin_url, $rel_path );
	}

	// This plugin's path relative WordPress root, with leading slash but no
	// trailing slash. If $rel_path is given, with or without leading slash,
	// it is appended with leading slash.
	public static final function rel_plugin_dir( $rel_path = '' ) {
		return self::str_join( substr( self::$plugin_dir, strlen( ABSPATH ) - 1 ), ltrim( $rel_path, '/' ), '/' );
	}

	// The WordPress' upload directory relative file system root, with leading
	// slash but no trailing slash. If $rel_path is given, with or without
	// leading slash, it is appended with leading slash.
	// Based on _wp_upload_dir().
	public static function upload_dir( $rel_path = '' ) {
		static $upload_dir = null;
		if ( is_null( $upload_dir ) ) {
			$upload_path = trim( get_option( 'upload_path' ) );
			if ( empty( $upload_path ) || 'wp-content/uploads' === $upload_path ) {
				$upload_dir = WP_CONTENT_DIR . '/uploads';
			}
			else if ( 0 !== strpos( $upload_path, ABSPATH ) ) {
				$upload_dir = path_join( ABSPATH, $upload_path );
			}
			else {
				$upload_dir = $upload_path;
			}
		}
		return self::str_join( $upload_dir, ltrim( $rel_path, '/' ), '/' );
	}

	public static function rel_upload_dir( $rel_path = '' ) {
		static $upload_dir = null;
		if ( is_null( $upload_dir ) ) {
			$upload_dir = substr( self::upload_dir(), strlen( ABSPATH ) );
		}
		return self::str_join( $upload_dir, ltrim( $rel_path, '/' ), '/' );
	}


	// The URL of the upload directory. If $rel_path is given, with or without
	// leading slash, it is appended with leading slash.
	public static function upload_url( $rel_path = '' ) {
		static $upload_url = null;
		if ( is_null( $upload_url ) ) {
			$upload_url = get_site_url( null, self::rel_upload_dir() );
		}
		return self::str_join( $upload_url, ltrim( $rel_path, '/' ), '/' );
	}

	// The WordPress' root relative file system root, with no trailing slash.
	// If $rel_path is given, with or without leading slash, it is appended
	// with leading slash.
	public static final function wp_dir( $rel_path = '' ) {
		return self::str_join( ABSPATH, ltrim( $rel_path, '/' ), '/' );
	}

	// Returns the truth value of the statement that we are running in the
	// context asserted by $context.
	public static final function is_context( $context ) {
		return 'any' == $context ||
		       'public' == $context && ( ! defined( 'WP_ADMIN' ) || ! WP_ADMIN ) ||
		       'rest' == $context && self::_is_rest_api_request() ||
		       'ajax' == $context && defined( 'DOING_AJAX' ) && DOING_AJAX ||
		       'admin' == $context && defined( 'WP_ADMIN' ) && WP_ADMIN && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ||
		       'cron' == $context && defined( 'DOING_CRON' ) && DOING_CRON ||
		       'cli' == $context && defined( 'WP_CLI' ) && WP_CLI ||
		       isset( $_SERVER ) && isset( $_SERVER['SCRIPT_FILENAME'] ) && pathinfo( $_SERVER['SCRIPT_FILENAME'], PATHINFO_FILENAME ) == $context;
	}

	// Returns true if and only if the debug flag is set.
	// The debug flag is a constant with the plugin's namespace with `/`
	// replaced with `_` and all letters in uppercase.
	public static final function is_debugging() {
		if ( null == self::$is_debugging ) {
			$kntnt_debug = strtr( strtoupper( self::$ns ), '-', '_' ) . '_DEBUG';
			self::$is_debugging = defined( 'WP_DEBUG' ) && constant( 'WP_DEBUG' ) && defined( $kntnt_debug ) && constant( $kntnt_debug );
		}
		return self::$is_debugging;
	}

	// Return the string "{$lhs}{$sep}{$rhs}" after any trailing $sep in $lhs
	// and any leading $sep in $rhs. By default $sep is forward slash.
	public static final function str_join( $lhs, $rhs, $sep = '/' ) {
		return rtrim( $lhs, $sep ) . $sep . ltrim( $rhs, $sep );
	}

	// Returns context => hook => class relationships for classes to load.
	protected abstract function classes_to_load();

	// Awaiting a core function to test if a call is a REST API call
	// (see https://core.trac.wordpress.org/ticket/42061) we use this
	// solution inspired by WooCommerce (see https://github.com/woocommerce/woocommerce/pull/21090/files#diff-7a990aa0f401ec3e7e8a62c6b23d8b3e)
	private static function _is_rest_api_request() {
		return $_SERVER['REQUEST_URI'] && ( false !== strpos( $_SERVER['REQUEST_URI'], trailingslashit( rest_get_url_prefix() ) ) );
	}

}
