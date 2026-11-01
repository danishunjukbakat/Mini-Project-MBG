<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use App\Models\Distribution;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFilterPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        $user = User::factory()->create();
        $user->role = 'pelapor';
        $user->save();

        return $user;
    }

    private function makeDistribution(
        string $schoolCode,
        string $distributionCode
    ): Distribution {
        $school = School::create([
            'code' => $schoolCode,
            'name' => 'Sekolah ' . $schoolCode,
        ]);

        return Distribution::create([
            'school_id' => $school->id,
            'code' => $distributionCode,
            'distribution_date' => now()->toDateString(),
            'meal_description' => 'Menu simulasi',
        ]);
    }

    private function makeReport(
        User $owner,
        Distribution $distribution,
        string $title,
        string $status = 'submitted'
    ): Report {
        $report = new Report();

        $report->reporter_id = $owner->id;
        $report->distribution_id = $distribution->id;
        $report->title = $title;
        $report->incident_date = now()->toDateString();
        $report->description = 'Deskripsi laporan simulasi.';
        $report->status = $status;

        $report->save();

        return $report;
    }

    public function test_search_respects_ownership(): void
    {
        $owner = $this->makeUser();
        $other = $this->makeUser();

        $distribution = $this->makeDistribution(
            'TEST-SCH-A',
            'TEST-DST-A'
        );

        $this->makeReport(
            $owner,
            $distribution,
            'Target Milik Saya'
        );

        $this->makeReport(
            $other,
            $distribution,
            'Target Milik Orang Lain'
        );

        $this->actingAs($owner)
            ->get(route('reports.index', [
                'search' => 'Target',
            ]))
            ->assertOk()
            ->assertSee('Target Milik Saya')
            ->assertDontSee('Target Milik Orang Lain');
    }

    public function test_filter_and_pagination(): void
    {
        $user = $this->makeUser();

        $distributionA = $this->makeDistribution(
            'TEST-SCH-B',
            'TEST-DST-B'
        );

        $distributionB = $this->makeDistribution(
            'TEST-SCH-C',
            'TEST-DST-C'
        );

        // Tujuh laporan yang sesuai filter.
        for ($i = 1; $i <= 7; $i++) {
            $this->makeReport(
                $user,
                $distributionA,
                "Khusus {$i}"
            );
        }

        // Laporan dari sekolah berbeda.
        $this->makeReport(
            $user,
            $distributionB,
            'Khusus Sekolah Lain'
        );

        // Laporan dengan status berbeda.
        $this->makeReport(
            $user,
            $distributionA,
            'Khusus Closed',
            'closed'
        );

        $filters = [
            'search' => 'Khusus',
            'status' => 'submitted',
            'school_id' => $distributionA->school_id,
        ];

        // Halaman pertama.
        $response = $this->actingAs($user)
            ->get(route('reports.index', $filters));

        $response->assertOk()
            ->assertSee('Total 7 laporan ditemukan')
            ->assertSee('Halaman 1')
            ->assertSee('Khusus 7')
            ->assertDontSee('Khusus Sekolah Lain')
            ->assertDontSee('Khusus Closed');

        // Link pagination harus mempertahankan filter.
        $this->assertStringContainsString(
            'search=Khusus',
            $response->getContent()
        );

        $this->assertStringContainsString(
            'status=submitted',
            $response->getContent()
        );

        $this->assertStringContainsString(
            'school_id=' . $distributionA->school_id,
            $response->getContent()
        );

        // Halaman kedua dengan filter yang sama.
        $this->get(route('reports.index', [
            ...$filters,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertSee('Halaman 2')
            ->assertSee('Khusus 1')
            ->assertDontSee('Khusus 7');
    }

    public function test_invalid_status_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get(route('reports.index', [
                'status' => 'invalid',
            ]))
            ->assertSessionHasErrors('status');
    }
}
