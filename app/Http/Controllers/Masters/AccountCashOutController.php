<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountCashOut;
use Illuminate\Http\Request;

class AccountCashOutController extends Controller
{
    public function index(Request $request)
    {
        $query = AccountCashOut::query();

        // 搜索功能：针对 title 字段进行搜索
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 20);

        // 默认按 sort 排序
        $cashOuts = $query->orderBy('type_id','asc')->orderBy('sort','desc')->paginate($perPage);

        if ($request->has('search')) {
            $cashOuts->appends(['search' => $request->search]);
        }

        $types = AccountCashOut::$type;
        return view('masters.account-cash-outs.index', compact('cashOuts','types'));
    }

    public function create()
    {
        $types = AccountCashOut::$type;
        return view('masters.account-cash-outs.create',compact('types'));
    }

    public function store(Request $request)
    {
        $rules = [
            'type_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'sort' => 'nullable|integer|min:0',
        ];

        $messages = [
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'sort.integer' => 'ソート順は整数で入力してください。',
            'sort.min' => 'ソート順は0以上で入力してください。',
            'type_id.integer' => 'タイプIDは整数で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // sort 字段的默认值处理
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 0;
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        // type_id 处理：如果是空字符串则转为 null
        if (!isset($validated['type_id']) || $validated['type_id'] === '') {
            $validated['type_id'] = null;
        } else {
            $validated['type_id'] = (int)$validated['type_id'];
        }

        AccountCashOut::create($validated);

        return redirect()->route('masters.account-cash-outs.index')
            ->with([
                'success' => '現金出力を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $cashOut = AccountCashOut::findOrFail($id);
        return view('masters.account-cash-outs.show', compact('cashOut'));
    }

    public function edit($id)
    {
        $types = AccountCashOut::$type;
        $cashOut = AccountCashOut::findOrFail($id);
        return view('masters.account-cash-outs.edit', compact('cashOut','types'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'type_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'sort' => 'nullable|integer|min:0',
        ];

        $messages = [
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'sort.integer' => 'ソート順は整数で入力してください。',
            'sort.min' => 'ソート順は0以上で入力してください。',
            'type_id.integer' => 'タイプIDは整数で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // sort 字段的默认值处理
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 0;
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        // type_id 处理
        if (!isset($validated['type_id']) || $validated['type_id'] === '') {
            $validated['type_id'] = null;
        } else {
            $validated['type_id'] = (int)$validated['type_id'];
        }

        $cashOut = AccountCashOut::findOrFail($id);
        $cashOut->update($validated);

        return redirect()->route('masters.account-cash-outs.index')
            ->with([
                'success' => '現金出力を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $cashOut = AccountCashOut::findOrFail($id);
        $cashOut->delete();

        return redirect()->route('masters.account-cash-outs.index')
            ->with([
                'success' => '現金出力を削除しました。',
                'alert-type' => 'success'
            ]);
    }
}