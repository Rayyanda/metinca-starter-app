<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\Operation;


class ProductionTrackingController extends Controller
{
    //
    public function trackQuality($division)
    {
        // Logic to track quality for the specified division
        // For example, fetch quality data from the database based on division

        // Dummy data for demonstration
        $qualityData = [
            'division' => $division,
            'totalInspected' => 150,
            'defectsFound' => 5,
            'defectRate' => '3.33%',
        ];
        $division = Division::where('code', $division)->first();
        $operations = Operation::where('division_id', $division->id)->paginate(10);

        // Return a view with the quality data
        return view('process.quality.index', compact('qualityData'));
    }
}
