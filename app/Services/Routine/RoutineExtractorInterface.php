<?php

namespace App\Services\Routine;

use App\Models\User;

/**
 * Interface RoutineExtractorInterface
 *
 * Defines the contract for all routine extraction engines.
 * This ensures the codebase remains SOLID and decoupled.
 */
interface RoutineExtractorInterface
{
    /**
     * Extract schedule details for a specific user based on their department, batch, and section.
     *
     * @param User $user The authenticated student (defines department, batch, section).
     * @param array $options Additional configuration options (e.g. file paths, scraper mode).
     * @return array List of structured schedule entries.
     */
    public function extract(User $user, array $options = []): array;
}
