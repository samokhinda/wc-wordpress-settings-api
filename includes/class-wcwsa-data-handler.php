<?php
class WCWSA_DataHandler {
    /**
     * Получение отдельной настройки по ключу
     */
    public function getSetting($key) {
        // Получаем значение опции
        $value = get_option($key);
        
        if ($value === false) {
            return [
                'success' => false,
                'error' => 'Настройка с указанным ключом не найдена.'
            ];
        }

        return [
            'success' => true,
            'data' => [
                'key' => $key,
                'value' => $value
            ]
        ];
    }
    
    /**
     * Получение нескольких настроек по списку ключей или всех настроек
     */
    public function getMultipleSettings($keys = null) {
        global $wpdb;
        
        if ($keys && is_array($keys)) {
            // Если передан список ключей, получаем только указанные настройки
            $placeholders = implode(',', array_fill(0, count($keys), '%s'));
            $query = $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name IN ($placeholders)",
                $keys
            );
        } else {
            // Получаем все настройки, исключая временные и транзиентные
            $query = "SELECT option_name, option_value FROM {$wpdb->options} 
                      WHERE option_name NOT LIKE '_transient%' 
                      AND option_name NOT LIKE '_site_transient%'
                      AND option_name NOT LIKE '_wp_session_%'";
        }
        
        $results = $wpdb->get_results($query);
        
        if (!$results) {
            return [
                'success' => false,
                'error' => 'Настройки не найдены.'
            ];
        }
        
        $settings = [];
        foreach ($results as $result) {
            $value = maybe_unserialize($result->option_value);
            $settings[] = [
                'key' => $result->option_name,
                'value' => $value
            ];
        }

        return [
            'success' => true,
            'data' => $settings
        ];
    }
    
    /**
     * Создание новой настройки
     */
    public function createSetting($key, $value, $autoload = 'yes') {
        // Проверяем, существует ли уже такая настройка
        if (get_option($key) !== false) {
            return [
                'success' => false,
                'error' => 'Настройка с таким ключом уже существует.'
            ];
        }
        
        $result = add_option($key, $value, '', $autoload);
        
        if (!$result) {
            return [
                'success' => false,
                'error' => 'Не удалось создать настройку.'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Настройка успешно создана.',
            'data' => [
                'key' => $key,
                'value' => $value
            ]
        ];
    }
    
    /**
     * Обновление существующей настройки
     */
    public function updateSetting($key, $data) {
        // Проверяем, существует ли настройка
        if (get_option($key) === false) {
            return [
                'success' => false,
                'error' => 'Настройка с указанным ключом не найдена.'
            ];
        }
        
        $value = isset($data['value']) ? $data['value'] : null;
        
        if ($value === null) {
            return [
                'success' => false,
                'error' => 'Не указано значение настройки.'
            ];
        }
        
        $result = update_option($key, $value);
        
        if (!$result) {
            return [
                'success' => false,
                'error' => 'Не удалось обновить настройку.'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Настройка успешно обновлена.',
            'data' => [
                'key' => $key,
                'value' => $value
            ]
        ];
    }    
}