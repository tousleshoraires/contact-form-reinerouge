<?php

/**
 * Process and prepare the Uri and Datas for Webhook call.
 * 
 * @package Processor
 * @author  Julien Devergnies <j.devergnies@tousleshoraires.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ReineRougeContactForm7\Processor;

use ReineRougeContactForm7\Front\EventListener\RequestListener;

final class Webhook
{
    /**
     * Process the Url for the S2S hook system.
     * 
     * @param string $uri   Uri of the webhook
     * @param string $email Email address of the user
     * 
     * @return string
     */
    public function process(string $uri, string $email): string
    {
        /*
         * For S2S hash
         */
        if (PHP_SESSION_ACTIVE !== session_status()) {
            session_start();
        }
        $hash = $_SESSION[RequestListener::HASHNAME];

        $uri = str_replace('##HASH##', $hash, $uri);

        $uri.= (strpos($uri, '?') === false) ? '?' : '&';
        $uri.= 'lead_id='.sha1($email);
        $uri.= '&custom1='.sha1($email);

        return $uri;
    }
}
