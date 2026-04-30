<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\DriverOperationStatus;
use Illuminate\Http\Request;

class DriverOperationStatusController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverOperationStatus::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        
        $perPage = $request->input('per_page', 20);
        $operationStatuses = $query->orderBy('display_order')->orderBy('id')->paginate($perPage);
        
        if ($request->has('search')) {
            $operationStatuses->appends(['search' => $request->search]);
        }
        
        return view('masters.driver-operation-status.index', compact('operationStatuses'));
    }

    public function create()
    {
        return view('masters.driver-operation-status.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'name.required' => '操作ステータス名は必須です。',
            'name.max' => '操作ステータス名は100文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = DriverOperationStatus::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        $validated['created_by'] = session('user_id', auth()->id() ?? 0);
        $validated['updated_by'] = session('user_id', auth()->id() ?? 0);

        DriverOperationStatus::create($validated);

        return redirect()->route('masters.driver-operation-status.index')
            ->with('success', '操作ステータスを登録しました。');
    }

    public function edit($id)
    {
        $operationStatus = DriverOperationStatus::findOrFail($id);
        return view('masters.driver-operation-status.edit', compact('operationStatus'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
        ];

        $messages = [
            'name.required' => '操作ステータス名は必須です。',
            'name.max' => '操作ステータス名は100文字以内で入力してください。',
            'display_order.integer' => '表示順は数値で入力してください。',
            'display_order.min' => '表示順は0以上の数値で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['updated_by'] = session('user_id', auth()->id() ?? 0);

        $operationStatus = DriverOperationStatus::findOrFail($id);
        $operationStatus->update($validated);

        return redirect()->route('masters.driver-operation-status.index')
            ->with('success', '操作ステータスを更新しました。');
    }

    public function destroy($id)
    {
        $operationStatus = DriverOperationStatus::findOrFail($id);
        $operationStatus->delete();

        return redirect()->route('masters.driver-operation-status.index')
            ->with('success', '操作ステータスを削除しました。');
    }
}