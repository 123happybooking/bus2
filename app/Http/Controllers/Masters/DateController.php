<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Date;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class DateController extends Controller
{
    public function index(Request $request)
    {
        $query = Date::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%");
                
                if (preg_match('/^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/', $search)) {
                    $dateStr = str_replace('/', '-', $search);
                    try {
                        $date = Carbon::createFromFormat('Y-m-d', $dateStr)->format('Y-m-d');
                        $q->orWhereDate('date', $date);
                    } catch (\Exception $e) {}
                }
                
                if (preg_match('/^\d{4}年\d{1,2}月\d{1,2}日$/', $search)) {
                    $search = str_replace(['年', '月', '日'], ['-', '-', ''], $search);
                    try {
                        $date = Carbon::createFromFormat('Y-m-d', $search)->format('Y-m-d');
                        $q->orWhereDate('date', $date);
                    } catch (\Exception $e) {}
                }
                
                if (preg_match('/^\d{4}-\d{2}$/', $search)) {
                    $q->orWhereYear('date', substr($search, 0, 4))
                      ->orWhereMonth('date', substr($search, 5, 2));
                }
            });
        }
        
        $dates = $query->orderBy('date')->paginate(20);
        
        if ($request->has('search')) {
            $dates->appends(['search' => $request->search]);
        }
        
        return view('masters.dates.index', compact('dates'));
    }

    public function create()
    {
        return view('masters.dates.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'date' => 'required|date|unique:dates,date',
            'description' => 'required|string|max:255',
        ];

        $messages = [
            'date.required' => '日付は必須です。',
            'date.date' => '有効な日付を入力してください。',
            'date.unique' => 'この日付は既に登録されています。',
            'description.required' => '説明は必須です。',
            'description.max' => '説明は255文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        Date::create($validated);

        return redirect()->route('masters.dates.index')
            ->with([
                'success' => '特定期日を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $date = Date::findOrFail($id);
        return view('masters.dates.show', compact('date'));
    }

    public function edit($id)
    {
        $date = Date::findOrFail($id);
        return view('masters.dates.edit', compact('date'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'date' => [
                'required',
                'date',
                Rule::unique('dates')->ignore($id),
            ],
            'description' => 'required|string|max:255',
        ];

        $messages = [
            'date.required' => '日付は必須です。',
            'date.date' => '有効な日付を入力してください。',
            'date.unique' => 'この日付は既に登録されています。',
            'description.required' => '説明は必須です。',
            'description.max' => '説明は255文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $date = Date::findOrFail($id);
        $date->update($validated);

        return redirect()->route('masters.dates.index')
            ->with([
                'success' => '特定期日情報を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $date = Date::findOrFail($id);
        $date->delete();

        return redirect()->route('masters.dates.index')
            ->with([
                'success' => '特定期日を削除しました。',
                'alert-type' => 'success'
            ]);
    }
}