<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_name_kana', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }
        
        $customers = $query->orderBy('customer_code')->paginate(20);
        
        if ($request->has('search')) {
            $customers->appends(['search' => $request->search]);
        }
        
        return view('masters.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('masters.customers.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'customer_code' => 'required|string|max:20|unique:customers,customer_code',
            'customer_name' => 'required|string|max:100',
            'customer_name_kana' => 'nullable|string|max:100',
            'customer_type' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'closing_day' => 'nullable|string|max:10',
            'payment_method' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'remarks' => 'nullable|string|max:500',
        ];

        $messages = [
            'customer_code.required' => '顧客コードは必須です。',
            'customer_code.unique' => 'この顧客コードは既に使用されています。',
            'customer_code.max' => '顧客コードは20文字以内で入力してください。',
            'customer_name.required' => '顧客名は必須です。',
            'customer_name.max' => '顧客名は100文字以内で入力してください。',
            'customer_name_kana.max' => '顧客名（カナ）は100文字以内で入力してください。',
            'customer_type.max' => '顧客タイプは20文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は200文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'manager_name.max' => '担当者名は50文字以内で入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'closing_day.max' => '締め日は10文字以内で入力してください。',
            'payment_method.max' => '支払方法は20文字以内で入力してください。',
            'is_active.boolean' => '有効/無効は正しい値を選択してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            Customer::create($validated);
            return redirect()->route('masters.customers.index')
                ->with([
                    'success' => '顧客を登録しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function show(Customer $customer)
    {
        return view('masters.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('masters.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $rules = [
            'customer_code' => 'required|string|max:20|unique:customers,customer_code,' . $customer->id,
            'customer_name' => 'required|string|max:100',
            'customer_name_kana' => 'nullable|string|max:100',
            'customer_type' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'closing_day' => 'nullable|string|max:10',
            'payment_method' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'remarks' => 'nullable|string|max:500',
        ];

        $messages = [
            'customer_code.required' => '顧客コードは必須です。',
            'customer_code.unique' => 'この顧客コードは既に使用されています。',
            'customer_code.max' => '顧客コードは20文字以内で入力してください。',
            'customer_name.required' => '顧客名は必須です。',
            'customer_name.max' => '顧客名は100文字以内で入力してください。',
            'customer_name_kana.max' => '顧客名（カナ）は100文字以内で入力してください。',
            'customer_type.max' => '顧客タイプは20文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は200文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'manager_name.max' => '担当者名は50文字以内で入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'closing_day.max' => '締め日は10文字以内で入力してください。',
            'payment_method.max' => '支払方法は20文字以内で入力してください。',
            'is_active.boolean' => '有効/無効は正しい値を選択してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            $customer->update($validated);
            
            return redirect()->route('masters.customers.index')
                ->with([
                    'success' => '顧客を更新しました。',
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

    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            
            return redirect()->route('masters.customers.index')
                ->with([
                    'success' => '顧客を削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.customers.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}