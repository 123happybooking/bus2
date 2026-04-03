<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Remark;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RemarkController extends Controller
{
    public function index(Request $request)
    {
        $query = Remark::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remark_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        $remarks = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $remarks->appends(['search' => $request->search]);
        }
        
        return view('masters.remarks.index', compact('remarks'));
    }

    public function create()
    {
        return view('masters.remarks.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'remark_code' => 'required|string|max:20|unique:remarks,remark_code',
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:2000',
            'category' => 'required|string|max:50',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'remark_code.required' => '備考コードは必須です。',
            'remark_code.unique' => 'この備考コードは既に使用されています。',
            'remark_code.max' => '備考コードは20文字以内で入力してください。',
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは100文字以内で入力してください。',
            'content.required' => '内容は必須です。',
            'content.max' => '内容は2000文字以内で入力してください。',
            'category.required' => 'カテゴリは必須です。',
            'category.max' => 'カテゴリは50文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Remark::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        Remark::create($validated);

        return redirect()->route('masters.remarks.index')
            ->with([
                'success' => '備考を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $remark = Remark::findOrFail($id);
        return view('masters.remarks.show', compact('remark'));
    }

    public function edit($id)
    {
        $remark = Remark::findOrFail($id);
        return view('masters.remarks.edit', compact('remark'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'remark_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('remarks')->ignore($id),
            ],
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:2000',
            'category' => 'required|string|max:50',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'remark_code.required' => '備考コードは必須です。',
            'remark_code.unique' => 'この備考コードは既に使用されています。',
            'remark_code.max' => '備考コードは20文字以内で入力してください。',
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは100文字以内で入力してください。',
            'content.required' => '内容は必須です。',
            'content.max' => '内容は2000文字以内で入力してください。',
            'category.required' => 'カテゴリは必須です。',
            'category.max' => 'カテゴリは50文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $remark = Remark::findOrFail($id);
        $remark->update($validated);

        return redirect()->route('masters.remarks.index')
            ->with([
                'success' => '備考情報を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $remark = Remark::findOrFail($id);
        $remark->delete();

        return redirect()->route('masters.remarks.index')
            ->with([
                'success' => '備考を削除しました。',
                'alert-type' => 'success'
            ]);
    }
}