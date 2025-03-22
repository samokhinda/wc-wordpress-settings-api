<?php
/**
 * Plugin Name: WordPress Settings API for WooCommerce
 * Description: Плагин для получения и изменения настроек WordPress через WooCommerce API.
 * Version: 1.2
 * Author: Телеботы
 */

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Подключение необходимых файлов
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcwsa-rest-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcwsa-authentication.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcwsa-data-handler.php';

// Инициализация плагина
function wordpress_settings_api_init() {
    $rest_api = new WCWSA_RestApi();
    $rest_api->register_routes();
}

add_action( 'rest_api_init', 'wordpress_settings_api_init' );
?>
