<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Services\DatabaseConnectionService;
use App\Models\Masters\Vehicle;
use App\Models\Masters\VehicleType;
use App\Models\Masters\Branch;
use App\Models\Masters\VehicleModel;
use App\Models\Masters\VehicleGrade;
use App\Models\Masters\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Vehicle::query()->with('branch', 'vehicleType');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vehicle_code', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhereHas('vehicleType', function ($typeQuery) use ($search) {
                      $typeQuery->where('type_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('branch', function ($branchQuery) use ($search) {
                      $branchQuery->where('branch_name', 'like', "%{$search}%")
                                 ->orWhere('branch_code', 'like', "%{$search}%");
                  });
            });
        }
        
        $perPage = $request->input('per_page', 20);
        $vehicles = $query->orderBy('display_order')->paginate($perPage);
        
        if ($request->has('search')) {
            $vehicles->appends(['search' => $request->search]);
        }
        
        return view('masters.vehicles.index', compact('vehicles'));
    }
    
    public function create(): View
    {
        $branches = Branch::orderBy('branch_name')->get();
        $vehicleTypes = VehicleType::with('models')->get();
        $vehicleGrades = VehicleGrade::orderBy('id')->get();
        
        $friendCompanyIds = DB::table('friends')
            ->where('status', 'accepted')
            ->pluck('friend_company_id')
            ->toArray();
            
        $friendCompanies = [];
        if (!empty($friendCompanyIds)) {
            $friendCompanies = User::on('mysql')
                ->whereIn('id', $friendCompanyIds)
                ->select('id', 'user_company_name', 'name')
                ->get()
                ->map(function($company) {
                    return (object)[
                        'id' => $company->id,
                        'user_company_name' => $company->user_company_name ?: $company->name,
                    ];
                });
        }
        
        return view('masters.vehicles.create', compact('branches', 'vehicleTypes', 'vehicleGrades', 'friendCompanies'));
    }
    
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'vehicle_code' => 'required|unique:vehicles|max:50',
            'vehicle_color' => 'nullable|string|max:50',
            'registration_number' => 'required|unique:vehicles|max:20',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'vehicle_model_id' => 'required|exists:vehicle_models,id',
            'vehicle_grade_id' => 'required|exists:vehicle_grades,id',
            'seating_capacity' => 'required|integer|min:1|max:100',
            'ownership_type' => 'required|in:own,reservable,rental',
            'inspection_expiration_date' => 'required|date',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
            'vehicle_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_share' => 'nullable|boolean',
            'share_to' => 'nullable|array',
        ], [
            'branch_id.required' => '所属営業所を選択してください',
            'vehicle_code.required' => '車両コードを入力してください',
            'vehicle_code.unique' => 'この車両コードは既に登録されています',
            'registration_number.required' => '登録番号を入力してください',
            'registration_number.unique' => 'この登録番号は既に登録されています',
            'vehicle_type_id.required' => '車両種類を選択してください',
            'vehicle_type_id.exists' => '選択された車両種類は存在しません',
            'vehicle_model_id.required' => 'モデルを選択してください',
            'vehicle_model_id.exists' => '選択されたモデルは存在しません',
            'seating_capacity.required' => '乗車定員を入力してください',
            'ownership_type.required' => '所有形態を選択してください',
            'inspection_expiration_date.required' => '車検満了日を入力してください',
            'is_active.required' => 'ステータスを選択してください',
            'remarks.max' => '備考は500文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
            'vehicle_image.image' => '画像ファイルをアップロードしてください',
            'vehicle_image.mimes' => '画像形式はJPEG、PNG、JPG、GIFのみ対応しています',
            'vehicle_image.max' => '画像サイズは5MB以下にしてください',
        ]);

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Vehicle::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        if ($request->hasFile('vehicle_image')) {
            $path = $request->file('vehicle_image')->store('vehicles', 'public');
            $validated['image_path'] = $path;
        }

        unset($validated['vehicle_image']);
        
        $validated['is_share'] = $request->has('is_share') ? 1 : 0;
        
        if ($validated['is_share']) {
            $shareMode = $request->input('share_mode', 'selected');
            
            if ($shareMode == 'all') {
                $validated['share_to'] = 'all';
            } else {
                if ($request->has('share_to') && is_array($request->share_to)) {
                    $validated['share_to'] = json_encode($request->share_to);
                } else {
                    $validated['share_to'] = null;
                }
            }
        } else {
            $validated['share_to'] = null;
        }

        Vehicle::create($validated);

        return redirect()->route('masters.vehicles.index')
            ->with('success', '車両が登録されました。');
    }
    
    public function show($id)
    {
        $vehicle = Vehicle::with('branch', 'vehicleType', 'vehicleModel')->findOrFail($id);
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'id' => $vehicle->id,
                'registration_number' => $vehicle->registration_number,
                'vehicle_code' => $vehicle->vehicle_code,
                'vehicle_color' => $vehicle->vehicle_color ?? '',
                'vehicle_type' => $vehicle->vehicleType->type_name ?? '',
                'vehicle_model' => $vehicle->vehicleModel->model_name ?? '',
                'vehicle_branch' => $vehicle->branch->branch_name ?? '',
                'seating_capacity' => $vehicle->seating_capacity ?? '',
                'image_path' => $vehicle->image_path ? asset('storage/' . $vehicle->image_path) : null,
            ]);
        }
        
        return view('masters.vehicles.show', compact('vehicle'));
    }
    
    public function edit($id): View
    {
        $vehicle = Vehicle::findOrFail($id);
        $branches = Branch::orderBy('branch_name')->get();
        $vehicleTypes = VehicleType::with('models')->get();
        $vehicleGrades = VehicleGrade::orderBy('id')->get();
        
        $friendCompanyIds = DB::table('friends')
            ->where('status', 'accepted')
            ->pluck('friend_company_id')
            ->toArray();
            
        $friendCompanies = [];
        if (!empty($friendCompanyIds)) {
            $friendCompanies = User::on('mysql')
                ->whereIn('id', $friendCompanyIds)
                ->select('id', 'user_company_name', 'name')
                ->get()
                ->map(function($company) {
                    return (object)[
                        'id' => $company->id,
                        'user_company_name' => $company->user_company_name ?: $company->name,
                    ];
                });
        }
            
        return view('masters.vehicles.edit', compact('vehicle', 'branches', 'vehicleTypes','vehicleGrades', 'friendCompanies'));
    }
    
    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'vehicle_code' => 'required|max:50|unique:vehicles,vehicle_code,' . $id,
            'vehicle_color' => 'nullable|string|max:50',
            'registration_number' => 'required|max:20|unique:vehicles,registration_number,' . $id,
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'vehicle_model_id' => 'required|exists:vehicle_models,id',
            'vehicle_grade_id' => 'required|exists:vehicle_grades,id',
            'seating_capacity' => 'required|integer|min:1|max:100',
            'ownership_type' => 'required|in:own,reservable,rental',
            'inspection_expiration_date' => 'required|date',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
            'vehicle_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'remove_image' => 'nullable|boolean',
            'is_share' => 'nullable|boolean',
            'share_to' => 'nullable|array',
        ], [
            'branch_id.required' => '所属営業所を選択してください',
            'vehicle_code.required' => '車両コードを入力してください',
            'vehicle_code.unique' => 'この車両コードは既に登録されています',
            'registration_number.required' => '登録番号を入力してください',
            'registration_number.unique' => 'この登録番号は既に登録されています',
            'vehicle_type_id.required' => '車両種類を選択してください',
            'vehicle_type_id.exists' => '選択された車両種類は存在しません',
            'vehicle_model_id.required' => 'モデルを選択してください',
            'vehicle_model_id.exists' => '選択されたモデルは存在しません',
            'vehicle_grade_id.required' => '車両等級を選択してください',
            'vehicle_grade_id.exists' => '選択された車両等級は存在しません',
            'seating_capacity.required' => '乗車定員を入力してください',
            'ownership_type.required' => '所有形態を選択してください',
            'inspection_expiration_date.required' => '車検満了日を入力してください',
            'is_active.required' => 'ステータスを選択してください',
            'remarks.max' => '備考は500文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
            'vehicle_image.image' => '画像ファイルをアップロードしてください',
            'vehicle_image.mimes' => '画像形式はJPEG、PNG、JPG、GIFのみ対応しています',
            'vehicle_image.max' => '画像サイズは5MB以下にしてください',
        ]);
    
        $vehicle = Vehicle::findOrFail($id);
    
        if ($request->hasFile('vehicle_image')) {
            if ($vehicle->image_path && Storage::disk('public')->exists($vehicle->image_path)) {
                Storage::disk('public')->delete($vehicle->image_path);
            }
            $path = $request->file('vehicle_image')->store('vehicles', 'public');
            $validated['image_path'] = $path;
        }
    
        if ($request->input('remove_image') == 1) {
            if ($vehicle->image_path && Storage::disk('public')->exists($vehicle->image_path)) {
                Storage::disk('public')->delete($vehicle->image_path);
            }
            $validated['image_path'] = null;
        }
    
        unset($validated['vehicle_image'], $validated['remove_image']);
        
        $validated['is_share'] = $request->has('is_share') ? 1 : 0;
    
        if ($validated['is_share']) {
            $shareMode = $request->input('share_mode', 'selected');
            
            if ($shareMode == 'all') {
                $validated['share_to'] = 'all';
            } else {
                if ($request->has('share_to') && is_array($request->share_to)) {
                    $validCompanyIds = User::on('mysql')
                        ->whereIn('id', $request->share_to)
                        ->pluck('id')
                        ->toArray();
                    
                    $invalidIds = array_diff($request->share_to, $validCompanyIds);
                    if (!empty($invalidIds)) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['share_to' => '無効な会社IDが含まれています。']);
                    }
                    
                    $validated['share_to'] = json_encode($request->share_to);
                } else {
                    $validated['share_to'] = null;
                }
            }
        } else {
            $validated['share_to'] = null;
        }
    
        $vehicle->update($validated);
    
        return redirect()->route('masters.vehicles.index')
            ->with('success', '車両情報が更新されました。');
    }
    
    public function destroy($id): RedirectResponse
    {
        $vehicle = Vehicle::findOrFail($id);
        
        if ($vehicle->image_path && Storage::disk('public')->exists($vehicle->image_path)) {
            Storage::disk('public')->delete($vehicle->image_path);
        }
        
        $vehicle->delete();

        return redirect()->route('masters.vehicles.index')
            ->with('success', '車両が削除されました。');
    }
}