<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\VehicleModel;
use App\Models\Masters\VehicleType;
use Illuminate\Http\Request;

class VehicleModelController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleModel::query()->with('vehicleType');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model_name', 'like', "%{$search}%")
                  ->orWhere('maker', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%");
            });
        }
        
        $VehicleModels = $query->orderBy('id')->paginate(20);
        
        if ($request->has('search')) {
            $VehicleModels->appends(['search' => $request->search]);
        }
        
        return view('masters.vehicle-models.index', compact('VehicleModels'));
    }

    public function create()
    {
        $VehicleTypes = VehicleType::orderBy('id')->get();
        return view('masters.vehicle-models.create', compact('VehicleTypes'));
    }

    public function store(Request $request)
    {
        $rules = [
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'model_name' => 'required|string|max:255',
            'maker' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ];

        $messages = [
            'vehicle_type_id.required' => '車両分類名は必須です。',
            'vehicle_type_id.exists' => '選択された車両分類名は有効ではありません。',
            'model_name.required' => '車両名は必須です。',
            'model_name.max' => '車両名は255文字以内で入力してください。',
            'maker.max' => 'メーカーは255文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            VehicleModel::create($validated);
            return redirect()->route('masters.vehicle-models.index')
                ->with([
                    'success' => '車両モデルを登録しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function show(VehicleModel $VehicleModel)
    {
        $VehicleModel->load('vehicleType');
        return view('masters.vehicle-models.show', compact('VehicleModel'));
    }

    public function edit(VehicleModel $VehicleModel)
    {
        $VehicleTypes = VehicleType::orderBy('id')->get();
        return view('masters.vehicle-models.edit', compact('VehicleModel', 'VehicleTypes'));
    }

    public function update(Request $request, VehicleModel $VehicleModel)
    {
        $rules = [
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'model_name' => 'required|string|max:255',
            'maker' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ];

        $messages = [
            'vehicle_type_id.required' => '車両分類名は必須です。',
            'vehicle_type_id.exists' => '選択された車両分類名は有効ではありません。',
            'model_name.required' => '車両名は必須です。',
            'model_name.max' => '車両名は255文字以内で入力してください。',
            'maker.max' => 'メーカーは255文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $VehicleModel->update($validated);
            
            return redirect()->route('masters.vehicle-models.index')
                ->with([
                    'success' => '車両モデルを更新しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '更新に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function destroy(VehicleModel $VehicleModel)
    {
        try {
            $VehicleModel->delete();
            
            return redirect()->route('masters.vehicle-models.index')
                ->with([
                    'success' => '車両モデルを削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.vehicle-models.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}