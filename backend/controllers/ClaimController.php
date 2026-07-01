<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Claim.php';

class ClaimController
{
    private Claim $claimModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();

        $this->claimModel = new Claim($db);
    }

    /**
     * Get all claims
     */
    public function index(): array
    {
        return $this->claimModel->getAllClaims();
    }

    /**
     * Get pending claims
     */
    public function pending(): array
    {
        return $this->claimModel->getPendingClaims();
    }

    /**
     * Get one claim
     */
    public function show(int $id): ?array
    {
        return $this->claimModel->getClaimById($id);
    }

    /**
     * Approve claim
     */
    public function approve(int $id): bool
    {
        return $this->claimModel->approveClaim($id);
    }

    /**
     * Reject claim
     */
    public function reject(int $id): bool
    {
        return $this->claimModel->rejectClaim($id);
    }
}