<?php

namespace ReineRougeContactForm7\Admin\Utils;

if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); }

class Response {
	/**
	 * Response constructor.
	 */
	public function __construct() {}

	public function render(string $title, $content): string
    {
		/*
		 * Start of the Wrapper
		 */
		$wrapper = '<div class="wrap">';
		$wrapper.= sprintf('<h1 class="wp-heading-inline">%s</h1>', $title);

		$wrapper.= $content;

		/*
		 * End of Wrapper
		 */
		$wrapper.= '</div>';

		return $wrapper;
	}
}
