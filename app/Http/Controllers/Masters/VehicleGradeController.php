<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\VehicleGrade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleGradeController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleGrade::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('grade_name', 'like', "%{$search}%");
            });
        }
        
        $grades = $query->orderBy('code')->paginate(20);
        
        if ($request->has('search')) {
            $grades->appends(['search' => $request->search]);
        }
        
        return view('masters.vehicle-grades.index', compact('grades'));
    }

    public function create()
    {
        return view('masters.vehicle-grades.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'code' => 'required|string|max:10|unique:vehicle_grades,code',
            'grade_name' => 'required|string|max:50',
            'description' => 'nullable|string|max:100',
        ];

        $messages = [
            'code.required' => 'コードは必須です。',
            'code.unique' => 'このコードは既に使用されています。',
            'code.max' => 'コードは10文字以内で入力してください。',
            'grade_name.required' => 'グレード名は必須です。',
            'grade_name.max' => 'グレード名は50文字以内で入力してください。',
            'description.max' => '説明は100文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        VehicleGrade::create($validated);

        return redirect()->route('masters.vehicle-grades.index')
            ->with('success', '車両グレードを登録しました。');
    }

    public function edit($id)
    {
        $grade = VehicleGrade::findOrFail($id);
        return view('masters.vehicle-grades.edit', compact('grade'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('vehicle_grades')->ignore($id),
            ],
            'grade_name' => 'required|string|max:50',
            'description' => 'nullable|string|max:100',
        ];

        $messages = [
            'code.required' => 'コードは必須です。',
            'code.unique' => 'このコードは既に使用されています。',
            'code.max' => 'コードは10文字以内で入力してください。',
            'grade_name.required' => 'グレード名は必須です。',
            'grade_name.max' => 'グレード名は50文字以内で入力してください。',
            'description.max' => '説明は100文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $grade = VehicleGrade::findOrFail($id);
        $grade->update($validated);

        return redirect()->route('masters.vehicle-grades.index')
            ->with('success', '車両グレードを更新しました。');
    }

    public function show($id)
    {
    }

    public function destroy($id)
    {
        $grade = VehicleGrade::findOrFail($id);
        $grade->delete();

        return redirect()->route('masters.vehicle-grades.index')
            ->with('success', '車両グレードを削除しました。');
    }
}