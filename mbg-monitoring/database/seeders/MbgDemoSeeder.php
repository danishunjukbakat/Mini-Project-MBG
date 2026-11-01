<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Distribution;
use App\Models\Report;
use App\Models\ReportReview;
use App\Models\ReportForwarding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MbgDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun demo untuk seluruh role
        $accounts = [
            ['key' => 'pelapor1', 'name' => 'Pelapor Demo 1', 'email' => 'pelapor1@example.test', 'role' => 'pelapor'],
            ['key' => 'pelapor2', 'name' => 'Pelapor Demo 2', 'email' => 'pelapor2@example.test', 'role' => 'pelapor'],
            ['key' => 'pemantau', 'name' => 'Pemantau Demo', 'email' => 'pemantau@example.test', 'role' => 'pemantau'],
            ['key' => 'koordinator', 'name' => 'Koordinator Demo', 'email' => 'koordinator@example.test', 'role' => 'koordinator'],
        ];

        $users = [];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('DemoMBG2026!'),
                ]
            );

            $user->role = $account['role'];
            $user->save();

            $users[$account['key']] = $user;
        }

        // 2. Data sekolah
        $school1 = School::firstOrCreate(
            ['code' => 'SCH-001'],
            ['name' => 'SMP Harapan', 'address' => 'Alamat simulasi A']
        );

        $school2 = School::firstOrCreate(
            ['code' => 'SCH-002'],
            ['name' => 'SD Merdeka', 'address' => 'Alamat simulasi B']
        );

        $school3 = School::firstOrCreate(
            ['code' => 'SCH-003'],
            ['name' => 'SMA Nusantara', 'address' => 'Alamat simulasi C']
        );

        // 3. Distribusi makanan
        $distribution1 = Distribution::firstOrCreate(
            ['code' => 'DST-001'],
            [
                'school_id' => $school1->id,
                'distribution_date' => '2026-10-20',
                'meal_description' => 'Menu makanan simulasi A',
            ]
        );

        $distribution2 = Distribution::firstOrCreate(
            ['code' => 'DST-002'],
            [
                'school_id' => $school1->id,
                'distribution_date' => '2026-10-21',
                'meal_description' => 'Menu makanan simulasi B',
            ]
        );

        $distribution3 = Distribution::firstOrCreate(
            ['code' => 'DST-003'],
            [
                'school_id' => $school2->id,
                'distribution_date' => '2026-10-22',
                'meal_description' => 'Menu makanan simulasi C',
            ]
        );

        $distribution4 = Distribution::firstOrCreate(
            ['code' => 'DST-004'],
            [
                'school_id' => $school3->id,
                'distribution_date' => '2026-10-23',
                'meal_description' => 'Menu makanan simulasi D',
            ]
        );

        // 4. Laporan dengan status berbeda
        $samples = [
            ['DEMO-01 Laporan baru', $users['pelapor1'], $distribution1, '2026-10-20', 'submitted'],
            ['DEMO-02 Dalam peninjauan', $users['pelapor2'], $distribution1, '2026-10-20', 'under_review'],
            ['DEMO-03 Perlu diteruskan', $users['pelapor1'], $distribution2, '2026-10-21', 'needs_forwarding'],
            ['DEMO-04 Sudah diteruskan', $users['pelapor2'], $distribution3, '2026-10-22', 'forwarded'],
            ['DEMO-05 Proses ditutup', $users['pelapor1'], $distribution4, '2026-10-23', 'closed'],
        ];

        $reports = [];

        foreach ($samples as [$title, $reporter, $distribution, $date, $status]) {
            $report = Report::firstOrNew(['title' => $title]);

            if (!$report->exists) {
                $report->reporter_id = $reporter->id;
                $report->distribution_id = $distribution->id;
                $report->incident_date = $date;
                $report->description = 'Laporan simulasi untuk pengujian aplikasi MBG. Bukan kejadian nyata.';
                $report->status = $status;
                $report->save();
            }

            $reports[$status] = $report;
        }

        // 5. Contoh catatan peninjauan
        foreach ([
            ['needs_forwarding', true],
            ['forwarded', true],
            ['closed', false],
        ] as [$status, $needsForwarding]) {
            $report = $reports[$status];

            if (!ReportReview::where('report_id', $report->id)->exists()) {
                $review = new ReportReview();
                $review->report_id = $report->id;
                $review->reviewer_id = $users['pemantau']->id;
                $review->notes = 'Catatan peninjauan simulasi.';
                $review->needs_forwarding = $needsForwarding;
                $review->save();
            }
        }

        // 6. Contoh penerusan laporan
        $forwardedReport = $reports['forwarded'];

        if (!ReportForwarding::where('report_id', $forwardedReport->id)->exists()) {
            $forwarding = new ReportForwarding();
            $forwarding->report_id = $forwardedReport->id;
            $forwarding->coordinator_id = $users['koordinator']->id;
            $forwarding->recipient_category = 'school';
            $forwarding->recipient_name = 'SD Merdeka (simulasi)';
            $forwarding->notes = 'Penerusan informasi simulasi.';
            $forwarding->forwarded_at = '2026-10-23 10:00:00';
            $forwarding->save();
        }
    }
}
