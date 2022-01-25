<?php

namespace ReineRougeContactForm7\Admin\Pages;

if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); }

use ReineRougeContactForm7\Admin\Utils\Response;

class Abstract_Page {
	/**
	 * @var Response $response
	 */
	protected $response;

	public function __construct() {
		$this->response = new Response();
	}
}
