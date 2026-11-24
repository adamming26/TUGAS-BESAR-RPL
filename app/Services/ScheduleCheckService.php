<?php

namespace App\Services;

use App\Models\BorrowRequest;
use App\Models\ScheduleLog;

class ScheduleCheckService
{
    /**
     * Memeriksa apakah laboratorium/ruangan tersedia pada rentang waktu yang diminta.
     * Kontrak: ScheduleCheckService::isAvailable(int $facilityId, string $date, string $start, string $end): bool
     */
    public static function isAvailable(int $facilityId, string $date, string $start, string $end, ?int $ignoreRequestId = null): bool
    {
        $parsedDate = \Carbon\Carbon::parse($date)->toDateString();
        $startTime = strlen($start) === 5 ? $start . ':00' : $start;
        $endTime = strlen($end) === 5 ? $end . ':00' : $end;

        $conflictQuery = BorrowRequest::where('facility_id', $facilityId)
            ->whereDate('borrow_date', $parsedDate)
            ->whereIn('status', ['menunggu', 'disetujui', 'dipinjam'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            });

        if ($ignoreRequestId) {
            $conflictQuery->where('id', '!=', $ignoreRequestId);
        }

        return !$conflictQuery->exists();
    }

    /**
     * Catat jadwal pemakaian ke tabel schedule_logs.
     */
    public static function logSchedule(int $facilityId, int $borrowRequestId, string $date, string $startTime, string $endTime, string $status = 'terjadwal'): ScheduleLog
    {
        return ScheduleLog::create([
            'facility_id' => $facilityId,
            'borrow_request_id' => $borrowRequestId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
        ]);
    }
}
