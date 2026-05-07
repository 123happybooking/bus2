<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountPeriodController extends Controller
{
    /**
     * 周期列表
     */
    public function index(Request $request)
    {
        $query = AccountPeriod::query();

        // 搜索功能：周期名称
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        // 排序：默认按开始时间降序（最新的周期在前）
        $query->orderBy('start', 'desc');

        $perPage = 20;
        $allowedPerPages = [20, 30, 50];

        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }

        $periods = $query->paginate($perPage);

        // 保留查询参数
        $periods->appends(['search' => $request->search, 'per_page' => $perPage]);

        return view('masters.account-periods.index', compact('periods'));
    }

    /**
     * 创建周期页面
     */
    public function create()
    {
        return view('masters.account-periods.create');
    }

    /**
     * 保存新周期
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:50',
            'start' => 'required|date_format:Y-m',
        ];

        $messages = [
            'title.required'       => '周期名称は必須です。',
            'title.max'            => '周期名称は50文字以内で入力してください。',
            'start.required'       => '開始日は必須です。',
            'start.date'           => '開始日は有効な日付形式で入力してください。'
        ];

        $validated = $request->validate($rules, $messages);

        try {
            // 可以在这里添加检查周期重叠的逻辑
            // if ($this->isOverlapping($validated['start'], $validated['end'])) { ... }

            $validated['start'] = date('Y-m-01', strtotime($validated['start']));
            $validated['end'] = date('Y-m-t', strtotime("+11 months", strtotime($validated['start'])));
            AccountPeriod::create($validated);

            return redirect()->route('masters.account-periods.index')
                ->with([
                    'success' => '会計周期を登録しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            \Log::error($e);
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    /**
     * 查看周期详情
     */
    public function show($id)
    {
        $period = AccountPeriod::findOrFail($id);
        return view('masters.account-periods.show', compact('period'));
    }

    /**
     * 编辑周期页面
     */
    public function edit($id)
    {
        $period = AccountPeriod::findOrFail($id);
        $period->start = date('Y-m', strtotime($period->start));
        return view('masters.account-periods.edit', compact('period'));
    }

    /**
     * 更新周期
     */
    public function update(Request $request, $id)
    {
        $period = AccountPeriod::findOrFail($id);

        $rules = [
            'title' => 'required|string|max:50',
            'start' => 'required|date_format:Y-m'
        ];

        $messages = [
            'title.required'       => '周期名称は必須です。',
            'title.max'            => '周期名称は50文字以内で入力してください。',
            'start.required'       => '開始日は必須です。',
            'start.date'           => '開始日は有効な日付形式で入力してください。'
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $validated['start'] = date('Y-m-01', strtotime($validated['start']));
            $validated['end'] = date('Y-m-t', strtotime("+11 months", strtotime($validated['start'])));
            $period->update($validated);

            return redirect()->route('masters.account-periods.index')
                ->with([
                    'success' => '会計周期を更新しました。',
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

    /**
     * 删除周期
     */
    public function destroy($id)
    {
        $period = AccountPeriod::findOrFail($id);

        try {
            // 可选：检查该周期下是否有会计记录
            // if ($period->accounts()->count() > 0) { ... }

            $period->delete();

            return redirect()->route('masters.account-periods.index')
                ->with([
                    'success' => '会計周期を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            return redirect()->route('masters.account-periods.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}