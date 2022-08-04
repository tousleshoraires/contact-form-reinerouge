<?php

/**
 * This class will look for the necessary variables on site load.
 * 
 * @package Front
 * @author  Julien Devergnies <j.devergnies@tousleshoraires.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ReineRougeContactForm7\Front\EventListener;

final class RequestListener
{
    public const HASHNAME = 's2shash';

    /**
     * Compute the Uri variables to store the S2S variable if existant.
     *
     * @return void
     */
    public static function compute(): void
    {
        if (!empty($_GET['h'])) {
            $hash = esc_attr(trim($_GET['h']));

            if (PHP_SESSION_ACTIVE !== session_status()) {
                session_start();
            }

            $_SESSION[self::HASHNAME] = $hash;
        }
    }
}
