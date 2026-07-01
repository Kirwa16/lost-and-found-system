<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ItemMatch.php';

class MatchController
{
    private ItemMatch $matchModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();

        $this->matchModel = new ItemMatch($db);
    }

    /**
     * Get all matches
     */
    public function index(): array
    {
        return $this->matchModel->getAllMatches();
    }

    /**
     * Get pending matches
     */
    public function pending(): array
    {
        return $this->matchModel->getPendingMatches();
    }

    /**
     * Get matches for a specific user
     */
    public function userMatches(int $userId): array
    {
        return $this->matchModel->getMatchesByUser($userId);
    }

    /**
     * Get a single match
     */
    public function show(int $id): ?array
    {
        return $this->matchModel->getMatchById($id);
    }

    /**
     * Approve a match
     */
    public function approve(int $id): bool
    {
        return $this->matchModel->approveMatch($id);
    }

    /**
     * Reject a match
     */
    public function reject(int $id): bool
    {
        return $this->matchModel->rejectMatch($id);
    }

    /**
     * Delete a match
     */
    public function delete(int $id): bool
    {
        return $this->matchModel->deleteMatch($id);
    }
}