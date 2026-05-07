<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Masters\Currency; 
use Illuminate\Http\Request;
use Carbon\Carbon;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        $query = Currency::query();
        
        // 搜索功能：货币代码、名称、符号
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('currency_name', 'like', "%{$search}%");
            });
        }

        $perPage = 20; // 默认值
        $allowedPerPages = [20, 30, 50]; // 允许的选项
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }
        
        // 排序：先按 sort 降序（越大越前），再按代码升序
        $currencies = $query->orderBy('sort', 'desc')->paginate($perPage);
        
        $currencies->only(['search', 'per_page']);
        
        return view('masters.currencies.index', compact('currencies'));
    }

    public function create()
    {
        return view('masters.currencies.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'currency_code'   => 'required|string|max:10|unique:currencies,currency_code',
            'currency_name'   => 'required|string|max:50',
            'symbol'          => 'required|string|max:10',
            'decimal_digits'  => 'required|integer|min:0|max:4',
            'rate_to_jpy'     => 'required|numeric|gt:0',
            'rate_valid_from' => 'required|date',
            'rate_valid_to'   => 'required|date|after_or_equal:rate_valid_from',
            'sort'            => 'required|integer|min:0|max:999',
        ];

        $messages = [
            'currency_code.required'   => '通貨コードは必須です。',
            'currency_code.unique'     => 'この通貨コードは既に使用されています。',
            'currency_code.max'        => '通貨コードは10文字以内で入力してください。',
            'currency_name.required'   => '通貨名称は必須です。',
            'currency_name.max'        => '通貨名称は50文字以内で入力してください。',
            'symbol.required'          => '通貨記号は必須です。',
            'symbol.max'               => '通貨記号は10文字以内で入力してください。',
            'decimal_digits.required'  => '小数桁数は必須です。',
            'decimal_digits.integer'   => '小数桁数は整数で入力してください。',
            'decimal_digits.min'       => '小数桁数は0以上で入力してください。',
            'decimal_digits.max'       => '小数桁数は4以下で入力してください。',
            'rate_to_jpy.required'     => '対円レートは必須です。',
            'rate_to_jpy.numeric'      => '対円レートは数値で入力してください。',
            'rate_to_jpy.gt'           => '対円レートは0より大きい値を入力してください。',
            'rate_valid_from.required' => '適用開始日は必須です。',
            'rate_valid_from.date'     => '適用開始日は有効な日付形式で入力してください。',
            'rate_valid_to.date'       => '適用終了日は有効な日付形式で入力してください。',
            'rate_valid_to.after_or_equal' => '適用終了日は適用開始日以降の日付にしてください。',
            'sort.integer'             => 'ソート順は整数で入力してください。',
            'sort.min'                 => 'ソート順は0以上で入力してください。',
            'sort.max'                 => 'ソート順は999以下で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理：类型转换和默认值
        $validated['decimal_digits'] = (int)$validated['decimal_digits'];
        $validated['rate_to_jpy']    = (float)$validated['rate_to_jpy'];
        
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 1; // 默认值为 1，符合表结构 DEFAULT 1
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        // 如果结束日期为空字符串，转为 null (表单提交空日期有时会是空字符串)
        if (isset($validated['rate_valid_to']) && $validated['rate_valid_to'] === '') {
            $validated['rate_valid_to'] = null;
        }

        try {
            Currency::create($validated);
            return redirect()->route('masters.currencies.index')
                ->with([
                    'success' => '通貨情報を登録しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            // 可以在这里记录日志 Log::error($e);
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function show($id)
    {
        $currency = Currency::findOrFail($id);
        return view('masters.currencies.show', compact('currency'));
    }

    public function edit($id)
    {
        $currency = Currency::findOrFail($id);
        return view('masters.currencies.edit', compact('currency'));
    }

    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);
        $rules = [
            'currency_code'   => 'required|string|max:10|unique:currencies,currency_code,' . $currency->id,
            'currency_name'   => 'required|string|max:50',
            'symbol'          => 'required|string|max:10',
            'decimal_digits'  => 'required|integer|min:0|max:4',
            'rate_to_jpy'     => 'required|numeric|gt:0',
            'rate_valid_from' => 'required|date',
            'rate_valid_to'   => 'required|date|after_or_equal:rate_valid_from',
            'sort'            => 'required|integer|min:0|max:999',
        ];

        $messages = [
            'currency_code.required'   => '通貨コードは必須です。',
            'currency_code.unique'     => 'この通貨コードは既に使用されています。',
            'currency_code.max'        => '通貨コードは10文字以内で入力してください。',
            'currency_name.required'   => '通貨名称は必須です。',
            'currency_name.max'        => '通貨名称は50文字以内で入力してください。',
            'symbol.required'          => '通貨記号は必須です。',
            'symbol.max'               => '通貨記号は10文字以内で入力してください。',
            'decimal_digits.required'  => '小数桁数は必須です。',
            'decimal_digits.integer'   => '小数桁数は整数で入力してください。',
            'decimal_digits.min'       => '小数桁数は0以上で入力してください。',
            'decimal_digits.max'       => '小数桁数は4以下で入力してください。',
            'rate_to_jpy.required'     => '対円レートは必須です。',
            'rate_to_jpy.numeric'      => '対円レートは数値で入力してください。',
            'rate_to_jpy.gt'           => '対円レートは0より大きい値を入力してください。',
            'rate_valid_from.required' => '適用開始日は必須です。',
            'rate_valid_from.date'     => '適用開始日は有効な日付形式で入力してください。',
            'rate_valid_to.date'       => '適用終了日は有効な日付形式で入力してください。',
            'rate_valid_to.after_or_equal' => '適用終了日は適用開始日以降の日付にしてください。',
            'sort.integer'             => 'ソート順は整数で入力してください。',
            'sort.min'                 => 'ソート順は0以上で入力してください。',
            'sort.max'                 => 'ソート順は999以下で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 数据预处理
        $validated['decimal_digits'] = (int)$validated['decimal_digits'];
        $validated['rate_to_jpy']    = (float)$validated['rate_to_jpy'];
        
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 1;
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        if (isset($validated['rate_valid_to']) && $validated['rate_valid_to'] === '') {
            $validated['rate_valid_to'] = null;
        }

        try {
            $currency->update($validated);
            
            return redirect()->route('masters.currencies.index')
                ->with([
                    'success' => '通貨情報を更新しました。',
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

    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);
        try {
            // 可选：在此处添加检查，例如是否有关联的交易记录
            // if ($currency->transactions()->count() > 0) { ... }

            $currency->delete();
            
            return redirect()->route('masters.currencies.index')
                ->with([
                    'success' => '通貨情報を削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.currencies.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

}