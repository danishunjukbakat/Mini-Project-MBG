<?php

namespace Tests\Feature;

use App\Models\Distribution;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolDistributionFetchTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelapor_receives_only_selected_school_distributions(): void
    {
        $user = User::factory()->create();
        $user->role = 'pelapor';
        $user->save();

        $schoolA = School::create([
            'code' => 'SCH-A',
            'name' => 'Sekolah A',
        ]);

        $schoolB = School::create([
            'code' => 'SCH-B',
            'name' => 'Sekolah B',
        ]);

        Distribution::create([
            'school_id' => $schoolA->id,
            'code' => 'DST-A',
            'distribution_date' => '2026-10-20',
            'meal_description' => 'Menu simulasi A',
        ]);

        Distribution::create([
            'school_id' => $schoolB->id,
            'code' => 'DST-B',
            'distribution_date' => '2026-10-21',
            'meal_description' => 'Menu simulasi B',
        ]);

        $this->actingAs($user)
            ->getJson(route('schools.distributions.index', $schoolA))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['code' => 'DST-A'])
            ->assertJsonMissing(['code' => 'DST-B']);
    }

    public function test_pemantau_cannot_access_distribution_endpoint(): void
    {
        $user = User::factory()->create();
        $user->role = 'pemantau';
        $user->save();

        $school = School::create([
            'code' => 'SCH-C',
            'name' => 'Sekolah C',
        ]);

        $this->actingAs($user)
            ->getJson(route('schools.distributions.index', $school))
            ->assertForbidden();
    }

    public function test_unknown_school_returns_404(): void
    {
        $user = User::factory()->create();
        $user->role = 'pelapor';
        $user->save();

        $this->actingAs($user)
            ->getJson(route('schools.distributions.index', 999999))
            ->assertNotFound();
    }
}
