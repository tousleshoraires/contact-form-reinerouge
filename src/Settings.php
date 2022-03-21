<?php
/**
 * @package ReineRougeContactForm7
 * Auteur: Julien Devergnies <j.devergnies@tousleshoraires.fr>
 * Date: 29/10/21
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ReineRougeContactForm7;

use ReineRougeContactForm7\Admin\Pages\Main_Page;

final class Settings
{
    public const TAB_GENERAL = 'general';
    public const FIELD_PIXEL = 'reinerouge_lead_pixel';
    public const FIELD_URL = 'reinerouge_url';
    public const FIELD_FORM_NAME = 'reinerouge_form_name';
    public const FIELD_FORM_FIELDS_NAME = 'reinerouge_form_fields_name';
    public const FIELD_FORM_FIELDS_LASTNAME_DEFAULT = 'reinerouge_form_fields_lastname_default';
    public const FIELD_FORM_FIELDS_PHONE = 'reinerouge_form_fields_phone';
    public const FIELD_FORM_FIELDS_ADDRESS = 'reinerouge_form_fields_address';
    public const FIELD_FORM_FIELDS_ZIPCODE = 'reinerouge_form_fields_zipcode';
    public const FIELD_FORM_FIELDS_CITY = 'reinerouge_form_fields_city';
    public const FIELD_FORM_FIELD_OTHER = 'reinerouge_form_field_other';
    public const FIELD_DEBUG = 'reinerouge_debug';

    public function register_settings_fields(): void
    {
        $tabs = self::getTabs();

        foreach ( $tabs as $tab ) {
            if ( ! array_key_exists('sections', $tab) ) {
                continue;
            }

            foreach ( $tab['sections'] as $section_id => $section ) {
                $full_section_id = 'cf7rr_' . $section_id . '_section';

                $label = isset( $section['label'] ) ? $section['label'] : '';

                $section_callback = isset( $section['callback'] ) ? $section['callback'] : '__return_empty_string';

                add_settings_section( $full_section_id, $label, $section_callback, Main_Page::MENU_SLUG );

                foreach ( $section['fields'] as $field_id => $field ) {
                    $full_field_id = ! empty( $field['full_field_id'] ) ? $field['full_field_id'] : 'cf7rr_' . $field_id;

                    $field['field_args']['id'] = $full_field_id;

                    $field_classes = [ $full_field_id ];

                    if ( ! empty( $field['class'] ) ) {
                        $field_classes[] = $field['field_args']['class'];
                    }

                    $field['field_args']['class'] = implode( ' ', $field_classes );

                    add_settings_field(
                        $full_field_id,
                        isset( $field['label'] ) ? $field['label'] : '',
                        static function (array $field ) {
                            if ( empty( $field['attributes']['class'] ) ) {
                                $field['attributes']['class'] = 'regular-text';
                            }

                            if ($field['type'] === 'checkbox'): ?>
                                <input type="<?php echo esc_attr( $field['type'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $field['id'] ); ?>" value="1" <?php echo checked( 1, get_option( $field['id'], $field['std'] ), false ); ?> />
                            <?php else: ?>
                                <input type="<?php echo esc_attr( $field['type'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( get_option( $field['id'], $field['std'] ) ); ?>" />
                            <?php endif; ?>
                            <?php
                            if ( ! empty( $field['sub_desc'] ) ) :
                                echo wp_kses_post( $field['sub_desc'] );
                            endif;
                            ?>
                            <?php if ( ! empty( $field['desc'] ) ) : ?>
                                <p class="description"><?php echo wp_kses_post( $field['desc'] ); ?></p>
                            <?php
                            endif;
                        },
                        Main_Page::MENU_SLUG,
                        $full_section_id,
                        $field['field_args']
                    );

                    $setting_args = [];

                    if ( ! empty( $field['setting_args'] ) ) {
                        $setting_args = $field['setting_args'];
                    }

                    register_setting( Main_Page::MENU_SLUG, $full_field_id, $setting_args );
                }
            }
        }
    }

    /**
     * @return array[]
     */
    public static function getTabs(): array
    {
        return [
            self::TAB_GENERAL => [
                'label' => esc_html__('General', Collection::DOMAIN),
                'sections' => [
                    'general' => [
                        'fields' => [
                            self::FIELD_URL => [
                                'label' => esc_html__('Webkook URL', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'text',
                                    'std' => '',
                                    'desc' => esc_html__('Example: https://example.com/coreg_c(1)-s(1)/##EMAIL##', Collection::DOMAIN).
                                        '<br><span style="color: #f90;">'.esc_html__('This can be overriden locally.', Collection::DOMAIN).'</span>',
                                    'attributes' => [
                                        'placeholder' => 'https://'
                                    ]
                                ],
                            ],
                            self::FIELD_PIXEL => [
                                'label' => esc_html__('Webkook of Lead Pixel', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'url',
                                    'std' => '',
                                    'desc' => '<span style="color: #f00;">'.esc_html__('Deprecated: this option will be remove in a future version. Use campaign twins instead.', Collection::DOMAIN).'</span><br>'.
                                        esc_html__('It will called on coreg success. Example: https://example.com/lead_c(123)-s(1) or https://example.com/lead_p(123lorem)?h=##HASH##', Collection::DOMAIN),
                                    'attributes' => [
                                        'placeholder' => 'https://'
                                    ]
                                ],
                            ],
                            self::FIELD_FORM_NAME => [
                                'label' => esc_html__('ID of Form', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'text',
                                    'std' => '',
                                    'desc' => '<span style="color: #f00;">'.esc_html__('Deprecated: this option will be remove in a future version. Enable the contact form locally instead.', Collection::DOMAIN).'</span><br>'.
                                    esc_html__('Example: 1', Collection::DOMAIN),
                                ],
                            ],
                            self::FIELD_DEBUG => [
                                'label' => esc_html__('Log the answer', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'checkbox',
                                    'std' => '',
                                    'desc' => esc_html__('If enabled, it will log the answers in a stored file.', Collection::DOMAIN),
                                ],
                            ],
                            self::FIELD_FORM_FIELDS_NAME => [
                                'label' => esc_html__('Name of field', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'text',
                                    'std' => '',
                                    'desc' => esc_html__('Name of the field combining surname and firstname (left blank if not combined).', Collection::DOMAIN),
                                ],
                            ],
                            self::FIELD_FORM_FIELDS_LASTNAME_DEFAULT => [
                                'label' => esc_html__('Default last name', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'text',
                                    'std' => '',
                                    'desc' => esc_html__('If last name is empty, what will be the value?', Collection::DOMAIN),
                                ],
                            ],
                            self::FIELD_FORM_FIELDS_PHONE => [
                                'label' => esc_html__('Name of field "Phone"', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'text',
                                    'std' => '',
                                    'desc' => esc_html__('Name of the field for the phone.', Collection::DOMAIN),
                                ],
                            ],
                            self::FIELD_FORM_FIELDS_ADDRESS => [
                                'label' => esc_html__('Name of field "Address"', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'text',
                                    'std' => '',
                                    'desc' => esc_html__('Name of the field for the address.', Collection::DOMAIN),
                                ],
                            ],
                            self::FIELD_FORM_FIELDS_ZIPCODE => [
                                'label' => esc_html__('Name of field "Zipcode"', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'text',
                                    'std' => '',
                                    'desc' => esc_html__('Name of the field for the zipcode.', Collection::DOMAIN),
                                ],
                            ],
                            self::FIELD_FORM_FIELDS_CITY => [
                                'label' => esc_html__('Name of field "City"', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'text',
                                    'std' => '',
                                    'desc' => esc_html__('Name of the field for the city.', Collection::DOMAIN),
                                ],
                            ],
                            self::FIELD_FORM_FIELD_OTHER => [
                                'label' => esc_html__('Other values', Collection::DOMAIN),
                                'field_args' => [
                                    'type' => 'text',
                                    'std' => '',
                                    'desc' => esc_html__('Other values to send.', Collection::DOMAIN).
                                        '<br><span style="color: #f90;">'.esc_html__('This can be overriden locally.', Collection::DOMAIN).'</span>',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }
}
