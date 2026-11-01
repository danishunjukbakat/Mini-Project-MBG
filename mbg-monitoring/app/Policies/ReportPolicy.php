<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    // Pengguna login boleh membuka daftar
    // sesuai cakupan kewenangannya.
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'pelapor',
            'pemantau',
            'koordinator',
        ], true);
    }

    // Pelapor hanya melihat laporan sendiri.
    // Pemantau dan Koordinator dapat melihat
    // laporan untuk tugas komunitas.
    public function view(User $user, Report $report): bool
    {
        if ($user->role === 'pelapor') {
            return $user->id === $report->reporter_id;
        }

        return in_array($user->role, [
            'pemantau',
            'koordinator',
        ], true);
    }

    // Hanya Pelapor membuat laporan.
    public function create(User $user): bool
    {
        return $user->role === 'pelapor';
    }

    // Laporan hanya bisa diedit pemiliknya
    // ketika status masih submitted.
    public function update(User $user, Report $report): bool
    {
        return $user->role === 'pelapor'
            && $user->id === $report->reporter_id
            && $report->status === 'submitted';
    }

    // Penghapusan laporan tidak disediakan.
    public function delete(User $user, Report $report): bool
    {
        return false;
    }

    // Pemantau dapat melakukan peninjauan.
    public function review(User $user, Report $report): bool
    {
        return $user->role === 'pemantau'
            && in_array($report->status, [
                'submitted',
                'under_review',
            ], true);
    }

    // Koordinator mencatat penerusan.
    public function forward(User $user, Report $report): bool
    {
        return $user->role === 'koordinator'
            && in_array($report->status, [
                'needs_forwarding',
                'forwarded',
            ], true);
    }
}
