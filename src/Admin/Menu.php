<?php
/**
 * @package Admin
 * Auteur: Julien Devergnies <j.devergnies@tousleshoraires.fr>
 * Date: 29/10/21
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ReineRougeContactForm7\Admin;

use ReineRougeContactForm7\Admin\Pages\Download_Page;
use ReineRougeContactForm7\Admin\Pages\Main_Page;
use ReineRougeContactForm7\Collection;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

final class Menu
{
    const CF7_ID = 'wpcf7';

    public function __construct() {}

    public function register_pages(): void
    {
        add_submenu_page(
            self::CF7_ID,
            '',
            __( 'Reine Rouge add-on', Collection::DOMAIN ),
            'manage_options',
            Main_Page::MENU_SLUG,
            [new Main_Page(), 'index'],
        );
        add_submenu_page(
            self::CF7_ID,
            '',
            __( 'Reine Rouge log', Collection::DOMAIN ),
            'manage_options',
            Download_Page::MENU_SLUG,
            [new Download_Page(), 'index'],
        );
    }
}
