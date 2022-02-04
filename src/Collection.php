<?php

/**
 * @category Add-on
 * @package  ReineRougeContactForm7
 * @author   Julien Devergnies <j.devergnies@tousleshoraires.fr>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://tousleshoraires.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ReineRougeContactForm7;

use ReineRougeContactForm7\Front\EventListener\RequestListener;

final class Collection
{
    public const DOMAIN = 'contact-form-rr7';
    public const NAME = 'ReineRouge - Contact Form 7 add-on';

    /**
     * Instance
     *
     * @since 1.0.0
     *
     * @access private
     * @static
     *
     * @var \ReineRougeContactForm7\Collection The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.0.0
     *
     * @access public
     * @static
     *
     * @return \ReineRougeContactForm7\Collection An instance of the class.
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        if ($this->is_compatible()) {
            add_action( 'init', [ $this, 'init' ]);
        }
    }

    /**
     * Load Textdomain
     *
     * Load plugin localization files.
     *
     * Fired by `init` action hook.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function i18n()
     {
        load_plugin_textdomain( self::DOMAIN );
    }

    public function includes(): void
    {

        include_once( dirname(__DIR__) . '/src/Admin/Menu.php' );
        include_once( dirname(__DIR__) . '/src/Admin/Pages/Abstract_Page.php' );
        include_once( dirname(__DIR__) . '/src/Admin/Pages/Download_Page.php' );
        include_once( dirname(__DIR__) . '/src/Admin/Pages/Main_Page.php' );
        include_once( dirname(__DIR__) . '/src/Admin/Utils/Response.php' );
        include_once( dirname(__DIR__) . '/src/Exporter/Export.php' );
        include_once( dirname(__DIR__) . '/src/Front/EventListener/RequestListener.php' );
        include_once( dirname(__DIR__) . '/src/Processor/Webhook.php' );
        include_once( dirname(__DIR__) . '/src/Settings.php' );

    }

    /**
     * Initialize the plugin
     *
     * Load the plugin only after Contact Form 7 (and other plugins) are loaded.
     * Load the files required to run the plugin.
     *
     * Fired by `plugins_loaded` action hook.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function init(): void
    {

        $this->i18n();
        $this->includes();
    
        if ( \is_admin() ) {
            add_action( 'admin_menu', [ new \ReineRougeContactForm7\Admin\Menu(), 'register_pages' ], 900 );
            add_action( 'admin_init', [ new Settings(), 'register_settings_fields' ] );
        }

        if ( !\is_admin() ) {
            (new RequestListener())->compute();
        }

        new \ReineRougeContactForm7\Exporter\Export();
    }

    /**
     * Compatibility Checks
     *
     * Checks if the installed version of Contact Form 7 meets the plugin's minimum requirement.
     * Checks if the installed PHP version meets the plugin's minimum requirement.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function is_compatible(): bool
    {
        // Check if Contact Form 7 installed and activated
        if ( ! class_exists( \WPCF7_Submission::class ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
            return false;
        }
        return true;
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have Contact Form 7 installed or activated.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function admin_notice_missing_main_plugin(): void
    {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
        /* translators: 1: Plugin name 2: Contact Form 7 */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', self::DOMAIN),
            '<strong>' . esc_html__( self::NAME, self::DOMAIN) . '</strong>',
            '<strong>' . esc_html__( 'Contact Form 7', self::DOMAIN) . '</strong>'
        );
        $message.= ' <a href="'.get_admin_url(null, 'plugin-install.php?s=Contact Form 7&tab=search&type=term').'">'.esc_html__( 'Install Now' ).'</a>';

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }
}
