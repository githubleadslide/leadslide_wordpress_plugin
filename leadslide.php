<?php
/**
 * Plugin Name: Leadslide AI Ebook Creator and Marketing Funnels
 * Description: Transform your content marketing strategy with LeadSlide's AI-powered Ebook Creator and Email Funnel plugin for WordPress. Create stunning ebooks, build comprehensive email funnels, and grow your audience effortlessly. No coding knowledge required, and it's all backed by the power of AI.
 * Version: 1.0.3
 * Author: ai.leadslide.com
 * License: GPLv2 or later
 * Text Domain: ai.leadslide.com
 */

global $BASE_LS_API_URL, $BASE_LS_VIEW_URL,$LS_PAGE_TEMPLATE_PATH,$BASE_LEADSLIDE_WP_URL ;



//$BASE_LS_API_URL = 'http://localhost:8080/api/basic/wp/';
//$BASE_LEADSLIDE_VIEW_URL  = 'http://localhost:8080/campaign/cp/';

$BASE_LS_API_URL = 'https://ai.leadslide.com/api/basic/wp/';
$BASE_LEADSLIDE_VIEW_URL  = 'https://ai.leadslide.com/campaign/cp/';
$BASE_LEADSLIDE_WP_URL = get_site_url();

$LS_PAGE_TEMPLATE_PATH = plugin_dir_path(__FILE__) . '/templates/leadslide-page-template.php';

require_once plugin_dir_path(__FILE__) . 'admin/menu-and-pages.php';
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'admin/campaigns.php';
require_once plugin_dir_path(__FILE__) . 'admin/templates.php';
include plugin_dir_path( __FILE__ ) . 'admin/app-page.php';

