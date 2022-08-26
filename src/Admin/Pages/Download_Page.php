<?php

namespace ReineRougeContactForm7\Admin\Pages;

if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); }

class Download_Page extends Abstract_Page
{
    public const MENU_SLUG = 'cf7rr_download';

    /**
     * Download today's logs
     */
    public function index(): void
    {
        $date = date('Ymd');
        if (!empty($_GET['day'])) {
            $date = sanitize_text_field( $_GET['day'] );
        }

        $fileName = dirname(__DIR__, 3).'/logs/'.$date.'.log';
        if (!file_exists($fileName)) {
            echo $this->noData();
            die;
        }

        $content = file_get_contents($fileName);

        $lines = explode(PHP_EOL, $content);
        krsort($lines);

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">Logs</h1>';
        echo '<form>';
        echo '<input type="text" name="day" value="' . esc_attr($date) . '">';
        echo '<button type="submit">change day</button>';
        echo '</form>';

        echo '<table class="wp-list-table widefat fixed striped">';
    
        foreach ($lines as $line) {
            if ($line === '---') {
                continue;
            }

            $row = explode("\t", $line);

            if (count($row) === 1) {
                continue;
            }

            $response = $row[3];
            $response = unserialize($response);

            echo '<tr>';
            echo '<td width="60">'.esc_attr($row[0]).'</td>';
            echo '<td>'.esc_attr($row[1]).'</td>';
            echo '<td>';
            if (\is_array($response) && \array_key_exists('body', $response)) {
                echo esc_attr($response['body']);
            } elseif ($response instanceof \WP_Error) {
                foreach ($response->errors as $error) {
                    echo implode('<br>'.PHP_EOL, esc_attr($error));
                }
            }
            echo '</td>';
            // echo '<td><pre>'.print_r($response, true).'</pre></td>';
            echo '<td>'.esc_attr($row[2]).'</td>';
            // echo '<td>'.$row[3].'</td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</div>';
    }

    public function noData(): void
    {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">Logs</h1>';
        echo '<p>Nothing to display today</p>';
        echo '</div>';
    }    
}
