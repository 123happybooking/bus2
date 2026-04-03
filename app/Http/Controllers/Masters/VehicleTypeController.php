<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\VehicleType;
use App\Models\Masters\VehicleModel;
use App\Models\Masters\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleType::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('type_name', 'like', "%{$search}%");
        }
        
        $vehicleTypes = $query->orderBy('id')->paginate(20);
        
        if ($request->has('search')) {
            $vehicleTypes->appends(['search' => $request->search]);
        }
        
        return view('masters.vehicle-types.index', compact('vehicleTypes'));
    }

    public function create()
    {
        return view('masters.vehicle-types.create');
    }

    public function store(Request $request)
    {
        $models = $request->input('models', []);
        $filteredModels = [];
        
        foreach ($models as $model) {
            if (!empty($model['model_name']) || !empty($model['maker']) || !empty($model['remarks'])) {
                $filteredModels[] = $model;
            }
        }
        
        $request->merge(['models' => $filteredModels]);
        
        $rules = [
            'type_name' => 'required|string|max:255|unique:vehicle_types,type_name',
            'models' => 'nullable|array',
            'models.*.model_name' => 'required|string|max:100',
            'models.*.maker' => 'nullable|string|max:50',
            'models.*.remarks' => 'nullable|string|max:255',
        ];

        $messages = [
            'type_name.required' => '車両種類名は必須です。',
            'type_name.unique' => 'この車両種類名は既に使用されています。',
            'type_name.max' => '車両種類名は255文字以内で入力してください。',
            'models.*.model_name.required' => 'モデル名は必須です。',
            'models.*.model_name.max' => 'モデル名は100文字以内で入力してください。',
            'models.*.maker.max' => 'メーカーは50文字以内で入力してください。',
            'models.*.remarks.max' => '備考は255文字以内で入力してください。',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        
        $vehicleType = VehicleType::create([
            'type_name' => $validated['type_name']
        ]);
        
        if (isset($validated['models'])) {
            foreach ($validated['models'] as $index => $modelData) {
                $modelData['vehicle_type_id'] = $vehicleType->id;
                $modelData['display_order'] = $index + 1;
                VehicleModel::create($modelData);
            }
        }
        
        DB::commit();
        
        return redirect()->route('masters.vehicle-types.index')
            ->with('success', '車両種類を登録しました。');
    }

    public function show($id)
    {
        $vehicleType = VehicleType::with(['models' => function($query) {
            $query->orderBy('display_order');
        }])->findOrFail($id);
        
        return view('masters.vehicle-types.show', compact('vehicleType'));
    }

    public function edit($id)
    {
        $vehicleType = VehicleType::with(['models' => function($query) {
            $query->orderBy('display_order');
        }])->findOrFail($id);
        
        return view('masters.vehicle-types.edit', compact('vehicleType'));
    }

    public function update(Request $request, $id)
    {
        $models = $request->input('models', []);
        $filteredModels = [];
        
        foreach ($models as $model) {
            if (!empty($model['model_name']) || !empty($model['maker']) || !empty($model['remarks'])) {
                $filteredModels[] = $model;
            }
        }
        
        $request->merge(['models' => $filteredModels]);
        
        $rules = [
            'type_name' => 'required|string|max:255|unique:vehicle_types,type_name,' . $id,
            'models' => 'nullable|array',
            'models.*.id' => 'nullable|exists:vehicle_models,id',
            'models.*.model_name' => 'required|string|max:100',
            'models.*.maker' => 'nullable|string|max:50',
            'models.*.remarks' => 'nullable|string|max:255',
        ];

        $messages = [
            'type_name.required' => '車両種類名は必須です。',
            'type_name.unique' => 'この車両種類名は既に使用されています。',
            'type_name.max' => '車両種類名は255文字以内で入力してください。',
            'models.*.model_name.required' => 'モデル名は必須です。',
            'models.*.model_name.max' => 'モデル名は100文字以内で入力してください。',
            'models.*.maker.max' => 'メーカーは50文字以内で入力してください。',
            'models.*.remarks.max' => '備考は255文字以内で入力してください。',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        
        $vehicleType = VehicleType::findOrFail($id);
        $vehicleType->update([
            'type_name' => $validated['type_name']
        ]);
        
        $existingModelIds = [];
        
        if (isset($validated['models'])) {
            foreach ($validated['models'] as $index => $modelData) {
                $displayOrder = $index + 1;
                
                $createData = [
                    'model_name' => $modelData['model_name'],
                    'maker' => $modelData['maker'] ?? null,
                    'remarks' => $modelData['remarks'] ?? null,
                    'display_order' => $displayOrder,
                ];
                
                if (!empty($modelData['id'])) {
                    $model = VehicleModel::where('id', $modelData['id'])
                        ->where('vehicle_type_id', $vehicleType->id)
                        ->first();
                        
                    if ($model) {
                        $model->update($createData);
                        $existingModelIds[] = $model->id;
                    }
                } else {
                    $createData['vehicle_type_id'] = $vehicleType->id;
                    $model = VehicleModel::create($createData);
                    $existingModelIds[] = $model->id;
                }
            }
        }
        
        $modelsToDelete = VehicleModel::where('vehicle_type_id', $vehicleType->id)
            ->whereNotIn('id', $existingModelIds)
            ->get();
        
        $modelsInUse = [];
        foreach ($modelsToDelete as $model) {
            $isUsed = Vehicle::where('vehicle_model_id', $model->id)->exists();
            if ($isUsed) {
                $modelsInUse[] = $model->model_name;
            }
        }
        
        if (!empty($modelsInUse)) {
            DB::rollBack();
            $modelNames = implode('、', $modelsInUse);
            return redirect()->back()
                ->withInput()
                ->with('error', "以下のモデルは車両マスタで使用されているため削除できません：{$modelNames}");
        }
        
        VehicleModel::where('vehicle_type_id', $vehicleType->id)
            ->whereNotIn('id', $existingModelIds)
            ->delete();
        
        DB::commit();
        
        return redirect()->route('masters.vehicle-types.index')
            ->with('success', '車両種類を更新しました。');
    }
    
    public function destroy($id)
    {
        DB::beginTransaction();
        
        $vehicleType = VehicleType::findOrFail($id);
        
        $vehiclesCount = Vehicle::where('vehicle_type_id', $vehicleType->id)->count();
        
        if ($vehiclesCount > 0) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'この車両種類は車両マスタで使用されているため削除できません。');
        }
        
        $modelIds = $vehicleType->models()->pluck('id')->toArray();
        
        if (!empty($modelIds)) {
            $modelsInUse = Vehicle::whereIn('vehicle_model_id', $modelIds)
                ->select('vehicle_model_id', DB::raw('count(*) as count'))
                ->groupBy('vehicle_model_id')
                ->get();
            
            if ($modelsInUse->isNotEmpty()) {
                $usedModels = VehicleModel::whereIn('id', $modelsInUse->pluck('vehicle_model_id'))
                    ->pluck('model_name')
                    ->toArray();
                
                $modelNames = implode('、', $usedModels);
                DB::rollBack();
                return redirect()->back()
                    ->with('error', "以下のモデルが車両マスタで使用されているため削除できません：{$modelNames}");
            }
        }
        
        $vehicleType->models()->delete();
        $vehicleType->delete();
        
        DB::commit();
        
        return redirect()->route('masters.vehicle-types.index')
            ->with('success', '車両種類を削除しました。');
    }
}