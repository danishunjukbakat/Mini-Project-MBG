<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use App\Models\Distribution;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $pelapor;
    private User $pemantau;
    private User $koordinator;
    private School $school;
    private Distribution $distribution;

    protected function setUp(): void
    {
        parent::setUp();

        // Akun pengujian
        $this->pelapor = $this->makeUser('pelapor');
        $this->pemantau = $this->makeUser('pemantau');
        $this->koordinator = $this->makeUser('koordinator');

        // Sekolah dan distribusi pengujian
        $this->school = School::create([
            'code' => 'TEST-SCH-001',
            'name' => 'Sekolah Simulasi',
        ]);

        $this->distribution = Distribution::create([
            'school_id' => $this->school->id,
            'code' => 'TEST-DST-001',
            'distribution_date' => now()->toDateString(),
            'meal_description' => 'Menu simulasi pengujian',
        ]);
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function makeReport(
        string $status = 'submitted'
    ): Report {
        $report = new Report();

        $report->reporter_id = $this->pelapor->id;
        $report->distribution_id = $this->distribution->id;
        $report->title = 'Laporan Simulasi';
        $report->incident_date = now()->toDateString();
        $report->description = 'Deskripsi kejadian untuk pengujian.';
        $report->status = $status;

        $report->save();

        return $report;
    }

    private function reportData(): array
    {
        return [
            'title' => 'Laporan Diperbarui',
            'incident_date' => now()->toDateString(),
            'description' => 'Deskripsi terbaru untuk pengujian.',
        ];
    }

    // BR-01: Hanya pemilik boleh mengedit
    // laporan berstatus submitted.
    public function test_br01_report_editing(): void
    {
        $report = $this->makeReport();

        $this->actingAs($this->pelapor)
            ->patch(
                route('reports.update', $report),
                $this->reportData()
            )->assertRedirect();

        $this->assertSame(
            'Laporan Diperbarui',
            $report->fresh()->title
        );

        // Pengguna lain ditolak.
        $other = $this->makeUser('pelapor');

        $this->actingAs($other)
            ->patch(
                route('reports.update', $report),
                $this->reportData()
            )->assertForbidden();

        // Setelah ditinjau, pemilik juga ditolak.
        $report->status = 'under_review';
        $report->save();

        $this->actingAs($this->pelapor)
            ->patch(
                route('reports.update', $report),
                $this->reportData()
            )->assertForbidden();
    }

    // BR-02: Distribusi harus sesuai sekolah.
    public function test_br02_distribution_school_match(): void
    {
        $otherSchool = School::create([
            'code' => 'TEST-SCH-002',
            'name' => 'Sekolah Lain',
        ]);

        // Distribusi milik sekolah pertama,
        // tetapi dikirim dengan ID sekolah kedua.
        $this->actingAs($this->pelapor)
            ->post(route('reports.store'), [
                'school_id' => $otherSchool->id,
                'distribution_id' => $this->distribution->id,
                ...$this->reportData(),
            ])
            ->assertSessionHasErrors('distribution_id');

        $this->assertDatabaseCount('reports', 0);
    }

    // BR-03: Status harus mengikuti alur.
    public function test_br03_review_workflow(): void
    {
        $report = $this->makeReport();

        // Tidak boleh langsung meninjau
        // tanpa memulai proses.
        $this->actingAs($this->pemantau)
            ->post(
                route('reports.reviews.store', $report),
                [
                    'notes' => 'Catatan pengujian.',
                    'needs_forwarding' => '0',
                ]
            )->assertForbidden();

        $this->assertDatabaseCount('report_reviews', 0);

        // Mulai peninjauan.
        $this->post(
            route('reports.review.start', $report)
        )->assertRedirect();

        $this->assertSame(
            'under_review',
            $report->fresh()->status
        );

        // Selesaikan tanpa penerusan.
        $this->post(
            route('reports.reviews.store', $report),
            [
                'notes' => 'Tidak perlu diteruskan.',
                'needs_forwarding' => '0',
            ]
        )->assertRedirect();

        $this->assertSame(
            'closed',
            $report->fresh()->status
        );

        $this->assertDatabaseCount('report_reviews', 1);

        // Laporan closed tidak boleh ditinjau ulang.
        $this->post(
            route('reports.review.start', $report)
        )->assertForbidden();
    }

    // BR-03: Jalur peninjauan yang perlu diteruskan.
    public function test_br03_needs_forwarding_transition(): void
    {
        $report = $this->makeReport();

        $this->actingAs($this->pemantau)
            ->post(
                route('reports.review.start', $report)
            )->assertRedirect();

        $this->post(
            route('reports.reviews.store', $report),
            [
                'notes' => 'Perlu tindak lanjut komunitas.',
                'needs_forwarding' => '1',
            ]
        )->assertRedirect();

        $this->assertSame(
            'needs_forwarding',
            $report->fresh()->status
        );

        $this->assertDatabaseHas('report_reviews', [
            'report_id' => $report->id,
            'reviewer_id' => $this->pemantau->id,
            'needs_forwarding' => 1,
        ]);
    }

    // BR-04: Penerusan dibatasi role dan status.
    public function test_br04_forwarding_rules(): void
    {
        $report = $this->makeReport();

        $data = [
            'recipient_category' => 'school',
            'recipient_name' => 'Sekolah Simulasi',
            'notes' => 'Informasi simulasi diteruskan.',
        ];

        // Koordinator tidak boleh langsung
        // meneruskan laporan submitted.
        $this->actingAs($this->koordinator)
            ->post(
                route('reports.forwardings.store', $report),
                $data
            )->assertForbidden();

        $this->assertDatabaseCount('report_forwardings', 0);

        // Siapkan status yang diperbolehkan.
        $report->status = 'needs_forwarding';
        $report->save();

        // Pelapor tetap tidak berwenang.
        $this->actingAs($this->pelapor)
            ->post(
                route('reports.forwardings.store', $report),
                $data
            )->assertForbidden();

        // Koordinator boleh meneruskan.
        $this->actingAs($this->koordinator)
            ->post(
                route('reports.forwardings.store', $report),
                $data
            )->assertRedirect();

        $this->assertSame(
            'forwarded',
            $report->fresh()->status
        );

        $this->assertDatabaseHas('report_forwardings', [
            'report_id' => $report->id,
            'coordinator_id' => $this->koordinator->id,
        ]);

        // Setelah penerusan, proses boleh ditutup.
        $this->post(
            route('reports.close', $report)
        )->assertRedirect();

        $this->assertSame(
            'closed',
            $report->fresh()->status
        );

        // Laporan yang sudah closed tidak
        // boleh diteruskan kembali.
        $this->post(
            route('reports.forwardings.store', $report),
            $data
        )->assertForbidden();
    }

    // Ownership: laporan pengguna lain tidak boleh dibuka.
    public function test_report_privacy(): void
    {
        $report = $this->makeReport();
        $other = $this->makeUser('pelapor');

        $this->actingAs($other)
            ->get(route('reports.show', $report))
            ->assertForbidden();
    }
}
