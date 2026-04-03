<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Masters\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * 显示设计品列表
     */
    public function index(Request $request)
    {
        $query = Product::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->has('language') && $request->language != '') {
            $query->where('language', $request->language);
        }

        $perPage = 20; // 默认值
        $allowedPerPages = [20, 30, 50]; // 允许的选项
        
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }
        
        // 分页并按创建时间倒序
        $lists = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        $lists->only(['search', 'per_page','language']);
        
        return view('masters.products.index', compact('lists'));
    }

    /**
     * 显示创建表单
     */
    public function create()
    {
        return view('masters.products.create');
    }

    /**
     * 存储新设计品
     */
    public function store(Request $request)
    {
        // 验证规则
        $rules = [
            'name' => 'required|string|max:255',
            'language'     => 'required|string|max:20',
        ];

        // 错误消息 (日语)
        $messages = [
            'name.required' => '品名は必須です。',
            'name.max'      => '品名は255文字以内で入力してください。',
            'language.required'     => '言語は必須です。',
            'language.max'          => '言語コードは20文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            Product::create($validated);

            return redirect()->route('masters.products.index')
                ->with([
                    'success' => '設計品情報を登録しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    /**
     * 显示单个设计品详情
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('masters.products.show', compact('product'));
    }

    /**
     * 显示编辑表单
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('masters.products.edit', compact('product'));
    }

    /**
     * 更新设计品信息
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'language'     => 'required|string|max:20',
        ];

        $messages = [
            'name.required' => '品名は必須です。',
            'name.max'      => '品名は255文字以内で入力してください。',
            'language.required'     => '言語は必須です。',
            'language.max'          => '言語コードは20文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $product->update($validated);
            
            return redirect()->route('masters.products.index')
                ->with([
                    'success' => '設計品情報を更新しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '更新に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    /**
     * 删除设计品
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        try {
            $product->delete();
            
            return redirect()->route('masters.products.index')
                ->with([
                    'success' => '設計品情報を削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.products.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}