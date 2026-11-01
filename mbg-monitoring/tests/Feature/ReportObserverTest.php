<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use App\Models\Distribution;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ReportObserverTest extends TestCase
{
    use RefreshDatabase;

    private function createDemoReport(): array
    {
        $pelapor = User::factory()->create();
        $pelapor->role = 'pelapor';
        $pelapor->save();

        $school = School::create([
            'code' => 'SCH-OBS',
            'name' => 'Sekolah Simulasi Observer',
        ]);

        $distribution = Distribution::create([
            'school_id' => $school->id,
            'code' => 'DST-OBS',
            'distribution_date' => now()->toDateString(),
            'meal_description' => 'Menu simulasi',
        ]);

        $report = new Report();
        $report->reporter_id = $pelapor->id;
        $report->distribution_id = $distribution->id;
        $report->title = 'Laporan Pengujian Observer';
        $report->incident_date = now()->toDateString();
        $report->description = 'Kejadian simulasi untuk pengujian.';
        $report->status = 'submitted';
        $report->save();

        return [$pelapor, $report];
    }

    public function test_status_change_is_logged(): void
    {
        [$pelapor, $report] = $this->createDemoReport();

        $pemantau = User::factory()->create();
        $pemantau->role = 'pemantau';
        $pemantau->save();

        $this->actingAs($pemantau);

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context)
                use ($report, $pemantau) {

                return $message === 'MBG report status changed'
                    && $context['report_id'] === $report->id
                    && $context['old_status'] === 'submitted'
                    && $context['new_status'] === 'under_review'
                    && $context['user_id'] === $pemantau->id
                    && !empty($context['changed_at']);
            });

        $this->post(
            route('reports.review.start', $report)
        )->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'under_review',
        ]);
    }

    public function test_title_change_does_not_log_status(): void
    {
        [$pelapor, $report] = $this->createDemoReport();

        $this->actingAs($pelapor);

        // Tidak boleh ada log perubahan status.
        Log::shouldReceive('info')->never();

        $this->patch(
            route('reports.update', $report),
            [
                'title' => 'Judul Laporan Diperbarui',
                'incident_date' => now()->toDateString(),
                'description' => 'Deskripsi laporan diperbarui untuk pengujian.',
            ]
        )->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'title' => 'Judul Laporan Diperbarui',
            'status' => 'submitted',
        ]);
    }
}
