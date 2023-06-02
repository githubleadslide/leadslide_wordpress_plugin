<?php
/**
 * Plugin Name: Leadslide API Key
 * Description: Loads an external site in an iframe after validating the user's Leadslide API key.
 * Version: 1.0
 * Author: Leadslide.com
 * License: GPLv2 or later
 * Text Domain: Leadslide.com
 */

global $BASE_LS_API_URL;
global $BASE_LS_VIEW_URL;
global $LS_PAGE_TEMPLATE_PATH;

$BASE_LS_API_URL = 'http://localhost:8080/api/basic/wp/';
$BASE_VIEW_URL  = 'http://localhost:8080/campaign/cp/';
$LS_PAGE_TEMPLATE_PATH = plugin_dir_path(__FILE__) . '/templates/leadslide-page-template.php';

require_once plugin_dir_path(__FILE__) . 'admin/menu-and-pages.php';
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'admin/campaigns.php';
require_once plugin_dir_path(__FILE__) . 'admin/templates.php';
include plugin_dir_path( __FILE__ ) . 'admin/app-page.php';

