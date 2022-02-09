<?php

/**
 * @package Admin
 * Auteur: Julien Devergnies <j.devergnies@tousleshoraires.fr>
 * Date: 29/10/21
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ReineRougeContactForm7\Exporter;

use ReineRougeContactForm7\Settings;
use ReineRougeContactForm7\Processor\Webhook;

class Export
{
    public function __construct()
    {
        add_action( 'wpcf7_before_send_mail', [ $this, 'cf7rr_before_send_mail' ] );
    }
    
    /**
     * Initialize the plugin
     *
     * Execute on before_send_email hook Contact Forms 7
     *
     * Fired by `wpcf7_before_send_mail` action hook.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function cf7rr_before_send_mail( $form_tag )
    {
        global $wpdb;
        $time_now      = time();

        $submission   = \WPCF7_Submission::get_instance();
        $contact_form = $submission->get_contact_form();
        $tags_names   = array();

        if ( $submission ) {

            $allowed_tags = array();

            $not_allowed_tags = apply_filters( 'cf7rr_not_allowed_tags', array( 'g-recaptcha-response' ) );
            $allowed_tags     = apply_filters( 'cf7rr_allowed_tags', $allowed_tags );
            $data             = $submission->get_posted_data();
            $files            = $submission->uploaded_files();
            $uploaded_files   = array();

            $form_data   = array();

            $form_data['cf7rr_status'] = 'unread';
            foreach ($data as $key => $d) {

                if ( !\in_array($key, $not_allowed_tags ) && !\in_array($key, $uploaded_files )  ) {

                    $tmpD = $d;

                    if ( !\is_array($d) ){
                        $bl   = array('\"',"\'",'/','\\','"',"'");
                        $wl   = array('&quot;','&#039;','&#047;', '&#092;','&quot;','&#039;');
                        $tmpD = str_replace($bl, $wl, $tmpD );
                    }

                    $form_data[$key] = $tmpD;
                }
                if ( \in_array($key, $uploaded_files ) ) {
                    $file = is_array( $files[ $key ] ) ? reset( $files[ $key ] ) : $files[ $key ];
                    $file_name = empty( $file ) ? '' : $time_now.'-'.$key.'-'.basename( $file ); 
                    $form_data[$key.'cf7rr_file'] = $file_name;
                }
            }

            /* cf7rr before save data. */
            $form_data = apply_filters('cf7rr_before_save_data', $form_data);

            do_action( 'cf7rr_before_save', $form_data );

            $form_post_id = $form_tag->id();
            // $form_date    = current_time('Y-m-d H:i:s');

            $rr_form_id = get_option('cf7rr_'. Settings::FIELD_FORM_NAME, '' );
            $rr_coreg_url = get_option('cf7rr_'. Settings::FIELD_URL, '' );
            $rr_pixel_webhook = get_option('cf7rr_'. Settings::FIELD_PIXEL, '' );
            $rr_log = get_option('cf7rr_'. Settings::FIELD_DEBUG, '' );
            $rr_form_field_other = get_option('cf7rr_'. Settings::FIELD_FORM_FIELD_OTHER, '' );

            if ((int)$form_post_id !== (int)$rr_form_id) {
                return;
            }
            if ($rr_coreg_url === '') {
                return;
            }

            $email = '';
            if (\array_key_exists('email', $form_data)) {
                $email = $form_data['email'];
            } elseif (\array_key_exists('your-email', $form_data)) {
                $email = $form_data['your-email'];
            }

            $form_data = $this->computeFields($form_data);
            $form_data = $this->mandatoryFields($form_data);

            if (!preg_match('`##EMAIL##`', $rr_coreg_url) &&
                preg_match('`coreg\_`', $rr_coreg_url) &&
                preg_match('`key\(([a-z0-9]+)\)/$`', $rr_coreg_url)
            ) {
                $rr_coreg_url.= '##EMAIL##';
            }

            $rr_coreg_url = \str_replace('##EMAIL##', $email, $rr_coreg_url);
            $rr_coreg_url.= '?ip='.$form_data['ip'].'&urlcollection='.$form_data['urlcollection'];

            $others = explode('&', $rr_form_field_other);
            foreach ($others as $other) {
                $fnv = explode('=', $other);
                if (count($fnv) === 2) {
                    // $form_data[$fnv[0]] = $fnv[1];
                    $rr_coreg_url.= '&'.$fnv[0].'='.$fnv[1];
                }
            }

            /*
             * to do: enable dry-run
             */
            // $rr_coreg_url.= '&dry-run=true';

            $args = [
                'method'      => 'POST',
                'body'        => $form_data
            ];
            $curl = new \WP_Http_Curl();
            $response = $curl->request($rr_coreg_url, $args);
            $responseBody = json_decode($response['body'], true);

            if ($rr_pixel_webhook !== '' && \array_key_exists('success', $responseBody) && $responseBody['success']) {
                $rr_pixel_webhook = (new Webhook())->process($rr_pixel_webhook, $email);

                $hookArgs = ['method' => 'GET'];
                $curl->request($rr_pixel_webhook, $hookArgs);
            }

            if ((bool)$rr_log) {
                $content = date('H:i:s')."\t".$rr_coreg_url."\t".\serialize($form_data)."\t".\serialize($response).PHP_EOL;
                $content.= date('H:i:s')."\t".$rr_pixel_webhook."\t\t".PHP_EOL;
                $content.= '---'.PHP_EOL;

                file_put_contents(
                    dirname(__DIR__, 2).'/logs/'.date('Ymd').'.log',
                    $content,
                    FILE_APPEND
                );
            }
        }
    }

    private function computeFields(array $form_data): array
    {
        $rr_form_default_lastname = get_option('cf7rr_'. Settings::FIELD_FORM_FIELDS_LASTNAME_DEFAULT, '');
        $rr_form_combined_name = get_option('cf7rr_'. Settings::FIELD_FORM_FIELDS_NAME, '');
        $rr_form_phone = get_option('cf7rr_'. Settings::FIELD_FORM_FIELDS_PHONE, '');
        $rr_form_address = get_option('cf7rr_'. Settings::FIELD_FORM_FIELDS_ADDRESS, '');
        $rr_form_zipcode = get_option('cf7rr_'. Settings::FIELD_FORM_FIELDS_ZIPCODE, '');
        $rr_form_city = get_option('cf7rr_'. Settings::FIELD_FORM_FIELDS_CITY, '');
        if ($rr_form_combined_name !== '') {
            $yourNames = explode(' ', $form_data[$rr_form_combined_name]);

            $firstName = array_shift($yourNames);
            $form_data['firstname'] = $firstName;
            $form_data['lastname'] = implode(' ', $yourNames);
        }
        if (empty($form_data['lastname']) && !empty($rr_form_default_lastname)) {
            $form_data['lastname'] = $rr_form_default_lastname;
        }
        if ($rr_form_phone !== '') {
            $form_data['phone'] = $form_data[$rr_form_phone];
            unset($form_data[$rr_form_phone]);
        }
        if ($rr_form_address !== '') {
            $form_data['address'] = $form_data[$rr_form_address];
            unset($form_data[$rr_form_address]);
        }
        if ($rr_form_zipcode !== '') {
            $form_data['zipcode'] = $form_data[$rr_form_zipcode];
            unset($form_data[$rr_form_zipcode]);
        }
        if ($rr_form_city !== '') {
            $form_data['city'] = $form_data[$rr_form_city];
            unset($form_data[$rr_form_city]);
        }

        return $form_data;
    }

    private function mandatoryFields(array $form_data): array
    {
        $form_data['ip'] = $_SERVER['REMOTE_ADDR'];
        $form_data['timestamp'] = date('Y-m-d H:i:s');
        // $form_data['urlcollection'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . '://';
        // $form_data['urlcollection'].= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $form_data['urlcollection'] = $_SERVER['HTTP_REFERER'];
        if (empty($form_data['urlcollection'])) {
            $form_data['urlcollection'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . '://';
            $form_data['urlcollection'].= $_SERVER['HTTP_HOST'];
        }
        $form_data['urlcollection'] = urlencode($form_data['urlcollection']);

        return $form_data;
    }
}
