<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\DriverCompensation;
use App\Models\Masters\Driver;
use App\Models\Masters\DriverCompensationType;
use App\Models\Masters\BusAssignment;
use Illuminate\Http\Request;

class DriverCompensationController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverCompensation::with(['driver', 'compensationType', 'busAssignment']);
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('target_date', 'like', "%{$search}%")
                  ->orWhere('remark', 'like', "%{$search}%")
                  ->orWhereHas('driver', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('compensationType', function($sub) use ($search) {
                      $sub->where('comp_name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        
        $perPage = $request->input('per_page', 20);
        $compensations = $query->orderBy('target_date', 'desc')->orderBy('id', 'desc')->paginate($perPage);
        
        if ($request->has('search')) {
            $compensations->appends(['search' => $request->search]);
        }
        
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();
        $compensationTypes = DriverCompensationType::where('is_active', true)->orderBy('display_order')->get();
        
        return view('masters.driver-compensations.index', compact('compensations', 'drivers', 'compensationTypes'));
    }

    public function create()
    {
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();
        $compensationTypes = DriverCompensationType::where('is_active', true)->orderBy('display_order')->get();
        $busAssignments = BusAssignment::with(['vehicle', 'groupInfo'])->orderBy('id', 'desc')->get();
        
        return view('masters.driver-compensations.create', compact('drivers', 'compensationTypes', 'busAssignments'));
    }

    public function store(Request $request)
    {
        $rules = [
            'bus_assignment_id' => 'nullable|exists:bus_assignment,id',
            'itinerary_id' => 'nullable|exists:daily_itinerary,id',
            'driver_id' => 'required|exists:drivers,id',
            'comp_id' => 'required|exists:driver_compensation_types,id',
            'target_date' => 'required|string|max:20',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'remark' => 'nullable|string|max:500',
        ];

        $messages = [
            'bus_assignment_id.exists' => '選択された運行IDは無効です。',
            'itinerary_id.exists' => '選択された行程IDは無効です。',
            'driver_id.required' => 'ドライバーは必須です。',
            'comp_id.required' => '手当種別は必須です。',
            'target_date.required' => '対象日は必須です。',
            'price.required' => '単価は必須です。',
            'price.numeric' => '単価は数値で入力してください。',
            'price.min' => '単価は0以上で入力してください。',
            'qty.required' => '数量は必須です。',
            'qty.numeric' => '数量は数値で入力してください。',
            'qty.min' => '数量は0以上で入力してください。',
            'remark.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        
        $validated['amount'] = $validated['price'] * $validated['qty'];

        DriverCompensation::create($validated);

        return redirect()->route('masters.driver-compensations.index')
            ->with('success', '手当記録を登録しました。');
    }

    public function edit($id)
    {
        $compensation = DriverCompensation::findOrFail($id);
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();
        $compensationTypes = DriverCompensationType::where('is_active', true)->orderBy('display_order')->get();
        $busAssignments = BusAssignment::with(['vehicle', 'groupInfo'])->orderBy('id', 'desc')->get();
        
        return view('masters.driver-compensations.edit', compact('compensation', 'drivers', 'compensationTypes', 'busAssignments'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'bus_assignment_id' => 'nullable|exists:bus_assignment,id',
            'itinerary_id' => 'nullable|exists:daily_itinerary,id',
            'driver_id' => 'required|exists:drivers,id',
            'comp_id' => 'required|exists:driver_compensation_types,id',
            'target_date' => 'required|string|max:20',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'remark' => 'nullable|string|max:500',
        ];

        $messages = [
            'bus_assignment_id.exists' => '選択された運行IDは無効です。',
            'itinerary_id.exists' => '選択された行程IDは無効です。',
            'driver_id.required' => 'ドライバーは必須です。',
            'comp_id.required' => '手当種別は必須です。',
            'target_date.required' => '対象日は必須です。',
            'price.required' => '単価は必須です。',
            'price.numeric' => '単価は数値で入力してください。',
            'price.min' => '単価は0以上で入力してください。',
            'qty.required' => '数量は必須です。',
            'qty.numeric' => '数量は数値で入力してください。',
            'qty.min' => '数量は0以上で入力してください。',
            'remark.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        
        $validated['amount'] = $validated['price'] * $validated['qty'];

        $compensation = DriverCompensation::findOrFail($id);
        $compensation->update($validated);

        return redirect()->route('masters.driver-compensations.index')
            ->with('success', '手当記録を更新しました。');
    }

    public function destroy($id)
    {
        $compensation = DriverCompensation::findOrFail($id);
        $compensation->delete();

        return redirect()->route('masters.driver-compensations.index')
            ->with('success', '手当記録を削除しました。');
    }
}