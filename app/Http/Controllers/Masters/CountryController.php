<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::where('is_active', true)->orderBy('display_order')->get();
        
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($countries);
        }
        
        return view('masters.countries.index', compact('countries'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'countries' => 'required|string',
        ]);

        $lines = explode("\n", $request->countries);
        $countryNames = array_map('trim', $lines);
        $countryNames = array_filter($countryNames, function($name) {
            return !empty($name);
        });

        Country::where('is_active', true)->update(['is_active' => false]);

        $displayOrder = 1;
        foreach ($countryNames as $name) {
            Country::updateOrCreate(
                ['country_name' => $name],
                ['display_order' => $displayOrder++, 'is_active' => true]
            );
        }

        return response()->json(['success' => true]);
    }
}