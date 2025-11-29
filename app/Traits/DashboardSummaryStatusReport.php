<?php

namespace App\Traits;

use App\Models\StoryStatusReportModel;

trait DashboardSummaryStatusReport
{
    public function dashboardSummary()
    {
        $user = auth()->user()->id;
        $reports = StoryStatusReportModel::where('created_by', $user)
            ->whereDate('report_date', now()->toDateString())
            ->orderBy('report_time')
            ->get();

        // Total status hari ini
        $totalReport = $reports->count();

        // Total jumlah status hari ini
        $totalCountStatus = $reports->sum('count_status');

        if ($reports->isEmpty()) {
            return [
                'totalReport' => 0,
                'totalCountStatus' => 0,
                'activePeriods' => 0,
                'passivePeriods' => 0,
                'lastUpdate' => null,
                'firstTime' => null,
                'lastTime' => null,
                'durationActive' => 0,
                'durationPassive' => 0,
            ];
        }

        // Hitung aktif/pasif
        $active = 0;
        $passive = 0;
        $activeMinutes = 0;
        $passiveMinutes = 0;

        for ($i = 0; $i < $reports->count() - 1; $i++) {
            $time1 = \Carbon\Carbon::parse($reports[$i]->report_time);
            $time2 = \Carbon\Carbon::parse($reports[$i + 1]->report_time);

            $diff = $time1->diffInMinutes($time2);

            if ($diff <= 15) {
                $active++;
                $activeMinutes += $diff;
            } else {
                $passive++;
                $passiveMinutes += $diff;
            }
        }

        return [
            'totalReport' => $totalReport,
            'totalCountStatus' => $totalCountStatus,
            'activePeriods' => $active,
            'passivePeriods' => $passive,
            'durationActive' => $activeMinutes,
            'durationPassive' => $passiveMinutes,
            'firstTime' => $reports->first()->report_time,
            'lastTime' => $reports->last()->report_time,
            'lastUpdate' => $reports->last()->updated_at->diffForHumans(),
            'periode' => now()->format('d/m/Y'),
        ];
    }
}
