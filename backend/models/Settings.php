<?php

class Settings
{
    private PDO $conn;
    private string $table = "settings";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Get a single setting by key
     */
    public function getSetting(string $key): ?string
    {
        $query = "SELECT setting_value
                  FROM {$this->table}
                  WHERE setting_key = :key
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":key", $key);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['setting_value'] : null;
    }

    /**
     * Get all settings
     */
    public function getAllSettings(): array
    {
        $query = "SELECT setting_key, setting_value
                  FROM {$this->table}";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        $settings = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Update one setting
     */
    public function updateSetting(string $key, string $value): bool
    {
        $query = "UPDATE {$this->table}
                  SET setting_value = :value
                  WHERE setting_key = :key";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":key", $key);
        $stmt->bindParam(":value", $value);

        return $stmt->execute();
    }

    /**
     * Update multiple settings
     */
    public function updateSettings(array $settings): bool
    {
        try
        {
            $this->conn->beginTransaction();

            foreach ($settings as $key => $value)
            {
                $this->updateSetting($key, $value);
            }

            $this->conn->commit();

            return true;
        }
        catch (Exception $e)
        {
            $this->conn->rollBack();

            return false;
        }
    }
}