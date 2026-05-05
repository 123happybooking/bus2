<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountCashIn;
use Illuminate\Http\Request;

class AccountCashInController extends Controller
{
    public function index(Request $request)
    {
        $query = AccountCashIn::query();
        
        // 搜索功能：针对 title 字段进行搜索
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }
        
        $perPage = $request->input('per_page', 20);
        
        // 默认按 sort 排序
        $cashIns = $query->orderBy('mode','asc')->orderBy('sort','desc')->paginate($perPage);
        
        if ($request->has('search')) {
            $cashIns->appends(['search' => $request->search]);
        }
        $types = AccountCashIn::$type;
        return view('masters.account-cash-ins.index', compact('cashIns','types'));
    }

    public function create()
    {
        $types = AccountCashIn::$type;
        return view('masters.account-cash-ins.create',compact('types'));
    }

    public function store(Request $request)
    {
        $rules = [
            'mode'      => 'nullable|integer',
            'type_id'   => 'nullable|integer',
            'title'     => 'required|string|max:255',
            'sort'      => 'nullable|integer',
        ];

        $messages = [
            'title.required' => 'タイトルは必須です。',
            'title.max'      => 'タイトルは255文字以内で入力してください。',
            'mode.integer'   => 'モードは整数で入力してください。',
            'type_id.integer'=> 'タイプIDは整数で入力してください。',
            'sort.integer'   => 'ソート順は整数で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 处理空值或 Null
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 0;
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        if (!isset($validated['mode']) || $validated['mode'] === '' || $validated['mode'] === null) {
            $validated['mode'] = 0; // 假设默认为0，根据业务需求调整
        } else {
            $validated['mode'] = (int)$validated['mode'];
        }
        
        if (!isset($validated['type_id']) || $validated['type_id'] === '' || $validated['type_id'] === null) {
            $validated['type_id'] = null;
        } else {
            $validated['type_id'] = (int)$validated['type_id'];
        }

        AccountCashIn::create($validated);
        
        return redirect()->route('masters.account-cash-ins.index')
            ->with([
                'success' => '現金入力を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $cashIn = AccountCashIn::findOrFail($id);
        return view('masters.account-cash-ins.show', compact('cashIn'));
    }

    public function edit($id)
    {
        $cashIn = AccountCashIn::findOrFail($id);
        $types = AccountCashIn::$type;
        return view('masters.account-cash-ins.edit', compact('cashIn','types'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'mode'      => 'nullable|integer',
            'type_id'   => 'nullable|integer',
            'title'     => 'required|string|max:255',
            'sort'      => 'nullable|integer',
        ];

        $messages = [
            'title.required' => 'タイトルは必須です。',
            'title.max'      => 'タイトルは255文字以内で入力してください。',
            'mode.integer'   => 'モードは整数で入力してください。',
            'type_id.integer'=> 'タイプIDは整数で入力してください。',
            'sort.integer'   => 'ソート順は整数で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 处理空值或 Null
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 0;
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        if (!isset($validated['mode']) || $validated['mode'] === '' || $validated['mode'] === null) {
            $validated['mode'] = 0;
        } else {
            $validated['mode'] = (int)$validated['mode'];
        }

        if (!isset($validated['type_id']) || $validated['type_id'] === '' || $validated['type_id'] === null) {
            $validated['type_id'] = null;
        } else {
            $validated['type_id'] = (int)$validated['type_id'];
        }

        $cashIn = AccountCashIn::findOrFail($id);
        $cashIn->update($validated);
        
        return redirect()->route('masters.account-cash-ins.index')
            ->with([
                'success' => '現金入力を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $cashIn = AccountCashIn::findOrFail($id);
        $cashIn->delete();
        
        return redirect()->route('masters.account-cash-ins.index')
            ->with([
                'success' => '現金入力を削除しました。',
                'alert-type' => 'success'
            ]);
    }
}