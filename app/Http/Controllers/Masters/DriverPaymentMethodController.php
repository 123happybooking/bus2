<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\DriverPaymentMethod;
use Illuminate\Http\Request;

class DriverPaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverPaymentMethod::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('method_name', 'like', "%{$search}%")
                  ->orWhere('remark', 'like', "%{$search}%");
            });
        }
        
        $perPage = $request->input('per_page', 20);
        $paymentMethods = $query->orderBy('id')->paginate($perPage);
        
        if ($request->has('search')) {
            $paymentMethods->appends(['search' => $request->search]);
        }
        
        return view('masters.driver-payment-methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        return view('masters.driver-payment-methods.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'method_name' => 'required|string|max:100',
            'is_reimbursable' => 'nullable|boolean',
            'remark' => 'nullable|string|max:500',
        ];

        $messages = [
            'method_name.required' => '支払方法名は必須です。',
            'method_name.max' => '支払方法名は100文字以内で入力してください。',
            'remark.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        $validated['is_reimbursable'] = $request->has('is_reimbursable') ? 1 : 0;

        DriverPaymentMethod::create($validated);

        return redirect()->route('masters.driver-payment-methods.index')
            ->with('success', '支払方法を登録しました。');
    }

    public function edit($id)
    {
        $paymentMethod = DriverPaymentMethod::findOrFail($id);
        return view('masters.driver-payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'method_name' => 'required|string|max:100',
            'is_reimbursable' => 'nullable|boolean',
            'remark' => 'nullable|string|max:500',
        ];

        $messages = [
            'method_name.required' => '支払方法名は必須です。',
            'method_name.max' => '支払方法名は100文字以内で入力してください。',
            'remark.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        $validated['is_reimbursable'] = $request->has('is_reimbursable') ? 1 : 0;

        $paymentMethod = DriverPaymentMethod::findOrFail($id);
        $paymentMethod->update($validated);

        return redirect()->route('masters.driver-payment-methods.index')
            ->with('success', '支払方法を更新しました。');
    }

    public function destroy($id)
    {
        $paymentMethod = DriverPaymentMethod::findOrFail($id);
        $paymentMethod->delete();

        return redirect()->route('masters.driver-payment-methods.index')
            ->with('success', '支払方法を削除しました。');
    }
}