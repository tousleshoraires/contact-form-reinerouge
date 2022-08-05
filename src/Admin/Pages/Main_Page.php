<?php

namespace ReineRougeContactForm7\Admin\Pages;

use ReineRougeContactForm7\Collection;
use ReineRougeContactForm7\Settings;

if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); }

class Main_Page extends Abstract_Page
{
    const MENU_SLUG = 'cf7rr_settings';

    /**
     * Main page of the Application
     * 
     */
    public function index(): void
    {
        $tabs = Settings::getTabs();

        echo '<div class="nav-tab-wrapper">';
        foreach ( $tabs as $tab_id => $tab ) {
            if ( ! $this->is_tab($tab) ) {
                continue;
            }

            $active_class = '';

            if ( 'general' === $tab_id ) {
                $active_class = ' nav-tab-active';
            }

            $sanitized_tab_id = esc_html( $tab_id );
            $sanitized_tab_label = esc_html( $tab['label'] );

            // PHPCS - Escaped the relevant strings above.
            ?>
            <a id="reinerouge-settings-tab-<?=$sanitized_tab_id ?>" class="nav-tab<?=active_class?>" href="#tab-<?=$sanitized_tab_id ?>">
                <?=$sanitized_tab_label ?>
            </a>
            <?php
        }
        echo '</div>';
        echo '<form id="reinerouge-settings-form" method="post" action="options.php">';
            settings_fields( static::MENU_SLUG );

            foreach ( $tabs as $tab_id => $tab ) {
                if ( ! $this->is_tab($tab) ) {
                    continue;
                }

                $active_class = '';

                if ( 'general' === $tab_id ) {
                    $active_class = ' reinerouge-active';
                }

                $sanitized_tab_id = sanitize_html_class( $tab_id );

                // PHPCS - $active_class is a non-dynamic string and $sanitized_tab_id is escaped above.
                echo "<div id='tab-{$sanitized_tab_id}' class='reinerouge-settings-form-page{$active_class}'>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                // $this->compute_forms($tab);
                $this->compute_session($tab);

                echo '</div>';
            }

            submit_button();
        echo '</form>';

        /*
         * Content
         */
        $content = '<h3>'.esc_html__( 'Information about this plugin', Collection::DOMAIN ).'</h3>';
        $content.= '<p>'.esc_html__( 'This plugin will help you to send contact result to Reine Rouge.', Collection::DOMAIN ).'</p>';
        $content.= '<p>'.sprintf(__( 'Configure a local form is the recommanded way. An example of configuration is available <a href="%s" target="_blank">in the wiki</a>.', Collection::DOMAIN ), 'https://github.com/tousleshoraires/contact-form-reinerouge/wiki/Configure-a-form-locally').'</p>';
        $content.= '<p>'.sprintf(__( 'The current plugin is provided as is as a courtesy by <a href="%s" target="_blank">%s</a> to ease the use of ReineRouge.', Collection::DOMAIN ), 'https://tousleshoraires.com', 'TLH SARL').'</p>';
        $content.= '<p>'.sprintf(__( '<a href="%s" target="_blank">Download today\'s log</a>', Collection::DOMAIN ), '').'</p>';

        echo $this->response->render(__( 'Reine Rouge - Contact Form 7 add-on', Collection::DOMAIN ), $content);

    }

    private function is_tab(array $tab): bool
    {
        if ( ! array_key_exists('sections', $tab) && ! array_key_exists('forms', $tab) ) {
            return false;
        }

        return !( empty( $tab['sections'] ) && ! array_key_exists('forms', $tab) );
    }

    private function compute_session(array $tab): void
    {
        if ( ! array_key_exists('sections', $tab) ) {
            return;
        }

        foreach ( $tab['sections'] as $section_id => $section ) {
            $full_section_id = 'cf7rr_' . $section_id . '_section';

            if ( ! empty( $section['label'] ) ) {
                echo '<h2>' . esc_html( $section['label'] ) . '</h2>';
            }

            if ( ! empty( $section['callback'] ) ) {
                $section['callback']();
            }

            echo '<table class="form-table">';

            do_settings_fields( static::MENU_SLUG, $full_section_id );

            echo '</table>';
        }
    }
}
