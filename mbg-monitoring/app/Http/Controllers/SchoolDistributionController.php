<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\JsonResponse;

class SchoolDistributionController extends Controller
{
    public function index(School $school): JsonResponse
    {
        $distributions = $school->distributions()
            ->orderByDesc('distribution_date')
            ->get([
                'id',
                'code',
                'distribution_date',
            ]);

        return response()->json($distributions);
    }
}
