<?php

namespace App\Services\Routine;

use App\Models\User;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class RoutineSyncManager
 *
 * Coordinates transactional bulk database updates for class routines,
 * ensuring integrity and clean formatting without duplicates.
 */
class RoutineSyncManager
{
    /**
     * Sync extracted schedules with the database.
     *
     * @param User $user The user performing the sync (defines group ownership).
     * @param array $schedules Extracted schedule list.
     * @return int Number of records successfully synchronized.
     */
    public function sync(User $user, array $schedules): int
    {
        if (empty($schedules)) {
            Log::warning('[RoutineSyncManager] Attempted to sync empty schedule list.');
            return 0;
        }

        $dept = $user->department ?: 'SWE';
        $batch = $user->batch ?: '40';
        $section = $user->section ?: 'B';

        return DB::transaction(function () use ($user, $schedules, $dept, $batch, $section) {
            // Step 1: Delete existing routine entries for the user's specific group
            Schedule::where('department', $dept)
                ->where('batch', $batch)
                ->where('section', $section)
                ->delete();

            $syncedCount = 0;

            // Step 2: Insert the newly parsed routine items
            foreach ($schedules as $item) {
                $day = ucfirst(strtolower(trim($item['day'] ?? '')));
                $timeSlot = trim($item['time_slot'] ?? '');

                if (empty($day) || empty($timeSlot)) {
                    continue;
                }

                Schedule::create([
                    'course_code'     => trim($item['course_code'] ?? 'Unknown'),
                    'course_title'    => trim($item['course_title'] ?? 'Untitled Course'),
                    'teacher_initial' => trim($item['teacher_initial'] ?? 'TBD'),
                    'room_no'         => trim($item['room_no'] ?? 'TBD'),
                    'type'            => strtolower(trim($item['type'] ?? 'theory')) === 'lab' ? 'lab' : 'theory',
                    'lab_section'     => !empty($item['lab_section']) ? trim($item['lab_section']) : null,
                    'day'             => $day,
                    'time_slot'       => $timeSlot,
                    'department'      => $dept,
                    'batch'           => $batch,
                    'section'         => $section,
                    'major'           => $user->major,
                    'user_id'         => $user->id,
                ]);

                $syncedCount++;
            }

            Log::info("[RoutineSyncManager] Synced {$syncedCount} routine entries for dept={$dept}, batch={$batch}, section={$section}");
            return $syncedCount;
        });
    }
}
