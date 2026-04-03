<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AttendanceCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceCategory::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('attendance_code', 'like', "%{$search}%")
                  ->orWhere('attendance_name', 'like', "%{$search}%");
            });
        }
        
        $categories = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $categories->appends(['search' => $request->search]);
        }
        
        return view('masters.attendance-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('masters.attendance-categories.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'attendance_code' => 'required|string|max:20|unique:attendance_categories,attendance_code',
            'attendance_name' => 'required|string|max:100',
            'is_work_day' => 'nullable|boolean',
            'color_code' => 'required|string|max:7',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'attendance_code.required' => '勤怠コードは必須です。',
            'attendance_code.unique' => 'この勤怠コードは既に使用されています。',
            'attendance_code.max' => '勤怠コードは20文字以内で入力してください。',
            'attendance_name.required' => '勤怠名は必須です。',
            'attendance_name.max' => '勤怠名は100文字以内で入力してください。',
            'color_code.required' => 'カラーコードは必須です。',
            'color_code.max' => 'カラーコードは7文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_work_day'] = $request->has('is_work_day') ? 1 : 0;

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = AttendanceCategory::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        AttendanceCategory::create($validated);

        return redirect()->route('masters.attendance-categories.index')
            ->with([
                'success' => '勤怠区分を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $attendanceCategory = AttendanceCategory::findOrFail($id);
        return view('masters.attendance-categories.show', compact('attendanceCategory'));
    }

    public function edit($id)
    {
        $attendanceCategory = AttendanceCategory::findOrFail($id);
        return view('masters.attendance-categories.edit', compact('attendanceCategory'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'attendance_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('attendance_categories')->ignore($id),
            ],
            'attendance_name' => 'required|string|max:100',
            'is_work_day' => 'nullable|boolean',
            'color_code' => 'required|string|max:7',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'attendance_code.required' => '勤怠コードは必須です。',
            'attendance_code.unique' => 'この勤怠コードは既に使用されています。',
            'attendance_code.max' => '勤怠コードは20文字以内で入力してください。',
            'attendance_name.required' => '勤怠名は必須です。',
            'attendance_name.max' => '勤怠名は100文字以内で入力してください。',
            'color_code.required' => 'カラーコードは必須です。',
            'color_code.max' => 'カラーコードは7文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_work_day'] = $request->has('is_work_day') ? 1 : 0;

        $attendanceCategory = AttendanceCategory::findOrFail($id);
        $attendanceCategory->update($validated);

        return redirect()->route('masters.attendance-categories.index')
            ->with([
                'success' => '勤怠区分情報を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $attendanceCategory = AttendanceCategory::findOrFail($id);
        $attendanceCategory->delete();

        return redirect()->route('masters.attendance-categories.index')
            ->with([
                'success' => '勤怠区分を削除しました。',
                'alert-type' => 'success'
            ]);
    }
}