<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AgencyType;
use Illuminate\Http\Request;

class AgencyTypeController extends Controller
{
    public function index()
    {
        $types = AgencyType::where('is_active', true)->orderBy('display_order')->get();
        
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($types);
        }
        
        return view('masters.agency-types.index', compact('types'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'types' => 'required|string',
        ]);

        $lines = explode("\n", $request->types);
        $typeNames = array_map('trim', $lines);
        $typeNames = array_filter($typeNames, function($name) {
            return !empty($name);
        });

        AgencyType::where('is_active', true)->update(['is_active' => false]);

        $displayOrder = 1;
        foreach ($typeNames as $name) {
            AgencyType::updateOrCreate(
                ['type_name' => $name],
                ['display_order' => $displayOrder++, 'is_active' => true]
            );
        }

        return response()->json(['success' => true]);
    }
}