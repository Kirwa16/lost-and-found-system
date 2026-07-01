<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Settings.php';

class SettingsController
{
    private PDO $conn;
    private Settings $settingsModel;

    public function __construct()
    {
        $database = new Database();

        $this->conn = $database->getConnection();

        $this->settingsModel = new Settings($this->conn);
    }

    /**
     * Load all settings
     */
    public function index(): array
    {
        return $this->settingsModel->getAllSettings();
    }

    /**
     * Save settings
     */
    public function update(array $data): array
    {
        $errors = [];

        // -----------------------------
        // Validation
        // -----------------------------

        if (empty(trim($data['system_name'] ?? ''))) {
            $errors[] = "System name is required.";
        }

        if (empty(trim($data['institution_name'] ?? ''))) {
            $errors[] = "Institution name is required.";
        }

        $sessionTimeout = (int)($data['session_timeout'] ?? 30);

        if ($sessionTimeout < 5) {
            $errors[] = "Session timeout must be at least 5 minutes.";
        }

        $passwordLength = (int)($data['minimum_password_length'] ?? 8);

        if ($passwordLength < 6) {
            $errors[] = "Minimum password length cannot be less than 6.";
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        // -----------------------------
        // Prepare settings
        // -----------------------------

        $settings = [

            'system_name' => trim($data['system_name']),

            'institution_name' => trim($data['institution_name']),

            'theme' => $data['theme'] ?? 'light',

            'email_notifications' =>
                isset($data['email_notifications']) ? '1' : '0',

            'claim_notifications' =>
                isset($data['claim_notifications']) ? '1' : '0',

            'session_timeout' => $sessionTimeout,

            'minimum_password_length' => $passwordLength

        ];

        $saved = $this->settingsModel->updateSettings($settings);

        if ($saved) {

            return [
                'success' => true,
                'message' => 'Settings updated successfully.'
            ];

        }

        return [
            'success' => false,
            'errors' => ['Failed to save settings.']
        ];
    }
}