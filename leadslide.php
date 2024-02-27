<?php
/**
 * Plugin Name: Leadslide AI Ebook Creator and Marketing Funnels
 * Description: Transform your content marketing strategy with LeadSlide's AI-powered Ebook Creator and Email Funnel plugin for WordPress. Create stunning ebooks, build comprehensive email funnels, and grow your audience effortlessly. No coding knowledge required, and it's all backed by the power of AI.
 * Version: 1.0.3
 * Author: ai.leadslide.com
 * License: GPLv2 or later
 * Text Domain: ai.leadslide.com
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
global $BASE_LS_API_URL, $BASE_LS_VIEW_URL,$LS_PAGE_TEMPLATE_PATH,$BASE_LEADSLIDE_WP_URL ;
/**
 * Define constants
 * $BASE_LS_API_URL is the base URL for the LeadSlide API and only the initial part.
 * $BASE_LS_VIEW_URL is the base URL for the LeadSlide campaign view and only the initial part.
 * $LS_PAGE_TEMPLATE_PATH is the path to the LeadSlide page template.
 * $BASE_LEADSLIDE_WP_URL is the base URL for the WordPress site.
 */

$BASE_LS_API_URL = 'https://ai.leadslide.com/api/basic/wp/';
$BASE_LEADSLIDE_VIEW_URL  = 'https://ai.leadslide.com/campaign/';

// $BASE_LS_API_URL = 'http://leadslide.api:8000/api/basic/wp/';
// $BASE_LEADSLIDE_VIEW_URL  = 'http://localhost:8080/campaign/';

$BASE_LEADSLIDE_WP_URL = get_site_url();

$LS_PAGE_TEMPLATE_PATH = plugin_dir_path(__FILE__) . '/templates/leadslide-page-template.php';
function leadslide_enqueue_admin_styles() {
    wp_enqueue_style('leadslide_admin_styles', plugin_dir_url(__FILE__) . 'admin/assets/css/style.css');
}
add_action('admin_enqueue_scripts', 'leadslide_enqueue_admin_styles');

require_once plugin_dir_path(__FILE__) . 'admin/menu-and-pages.php';
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'admin/campaigns.php';
require_once plugin_dir_path(__FILE__) . 'admin/popups.php';

