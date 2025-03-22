<?php

class WCWSA_RestApi {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        // Получение всех настроек из списка
        register_rest_route('wp', '/settings', [
            'methods' => 'GET',
            'callback' => [$this, 'get_settings'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
        
        // Получение конкретной настройки по ключу
        register_rest_route('wp', '/settings/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_setting'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'key' => [
                    'validate_callback' => function($param) {
                        return !empty($param);
                    }
                ],
            ],
        ]);
        
        // Обновление настройки
        register_rest_route('wp', '/settings/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_setting'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'key' => [
                    'validate_callback' => function($param) {
                        return !empty($param);
                    }
                ],
            ],
        ]);
        
        // Создание новой настройки
        register_rest_route('wp', '/settings', [
            'methods' => 'POST',
            'callback' => [$this, 'create_setting'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    public function get_settings() {
        $data_handler = new WCWSA_DataHandler();
        $result = $data_handler->getMultipleSettings();
        
        if (!$result['success']) {
            return new WP_REST_Response($result, 404);
        }
        
        return new WP_REST_Response($result);
    }
    
    public function get_setting(WP_REST_Request $request) {
        $key = $request->get_param('key');
        
        $data_handler = new WCWSA_DataHandler();
        $result = $data_handler->getSetting($key);
        
        if (!$result['success']) {
            return new WP_REST_Response($result, 404);
        }
        
        return new WP_REST_Response($result);
    }
    
    public function update_setting(WP_REST_Request $request) {
        $key = $request->get_param('key');
        $data = $request->get_json_params();
        
        $data_handler = new WCWSA_DataHandler();
        $result = $data_handler->updateSetting($key, $data);
        
        if (!$result['success']) {
            return new WP_REST_Response($result, 400);
        }
        
        return new WP_REST_Response($result);
    }
    
    public function create_setting(WP_REST_Request $request) {
        $data = $request->get_json_params();
        
        if (empty($data['key']) || !isset($data['value'])) {
            return new WP_REST_Response([
                'success' => false, 
                'error' => 'Необходимо указать key и value для настройки'
            ], 400);
        }
        
        $data_handler = new WCWSA_DataHandler();
        $result = $data_handler->createSetting($data['key'], $data['value'], isset($data['autoload']) ? $data['autoload'] : 'yes');
        
        if (!$result['success']) {
            return new WP_REST_Response($result, 400);
        }
        
        return new WP_REST_Response($result, 201);
    }

    public function check_permissions() {
        return current_user_can('manage_options'); // Adjust permission check as needed
    }
}