<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = Partner::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('partner_code', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%");
            });
        }
        
        $partners = $query->orderBy('partner_code')->paginate(20);
        
        if ($request->has('search')) {
            $partners->appends(['search' => $request->search]);
        }
        
        return view('masters.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('masters.partners.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'partner_code' => 'required|string|max:20|unique:partners,partner_code',
            'partner_name' => 'required|string|max:100',
            'branch_name' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:50',
            'invoice_number' => 'nullable|string|max:50',
            'closing_day' => 'nullable|integer|min:1|max:31',
            'payment_month' => 'nullable|integer|min:0|max:2',
            'payment_day' => 'nullable|integer|min:1|max:31',
            'is_active' => 'nullable|boolean',
            'remarks' => 'nullable|string|max:500',
        ];

        $messages = [
            'partner_code.required' => '取引先コードは必須です。',
            'partner_code.unique' => 'この取引先コードは既に使用されています。',
            'partner_code.max' => '取引先コードは20文字以内で入力してください。',
            'partner_name.required' => '会社名は必須です。',
            'partner_name.max' => '会社名は100文字以内で入力してください。',
            'branch_name.max' => '支店名は100文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は200文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'manager_name.max' => '担当者名は50文字以内で入力してください。',
            'invoice_number.max' => 'インボイス番号は50文字以内で入力してください。',
            'closing_day.min' => '締め日は1以上で入力してください。',
            'closing_day.max' => '締め日は31以下で入力してください。',
            'payment_month.min' => '支払月は0以上で入力してください。',
            'payment_month.max' => '支払月は2以下で入力してください。',
            'payment_day.min' => '支払日は1以上で入力してください。',
            'payment_day.max' => '支払日は31以下で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        Partner::create($validated);

        return redirect()->route('masters.partners.index')
            ->with([
                'success' => '取引先を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $partner = Partner::findOrFail($id);
        return view('masters.partners.show', compact('partner'));
    }

    public function edit($id)
    {
        $partner = Partner::findOrFail($id);
        return view('masters.partners.edit', compact('partner'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'partner_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('partners')->ignore($id),
            ],
            'partner_name' => 'required|string|max:100',
            'branch_name' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:50',
            'invoice_number' => 'nullable|string|max:50',
            'closing_day' => 'nullable|integer|min:1|max:31',
            'payment_month' => 'nullable|integer|min:0|max:2',
            'payment_day' => 'nullable|integer|min:1|max:31',
            'is_active' => 'nullable|boolean',
            'remarks' => 'nullable|string|max:500',
        ];

        $messages = [
            'partner_code.required' => '取引先コードは必須です。',
            'partner_code.unique' => 'この取引先コードは既に使用されています。',
            'partner_code.max' => '取引先コードは20文字以内で入力してください。',
            'partner_name.required' => '会社名は必須です。',
            'partner_name.max' => '会社名は100文字以内で入力してください。',
            'branch_name.max' => '支店名は100文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は200文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'manager_name.max' => '担当者名は50文字以内で入力してください。',
            'invoice_number.max' => 'インボイス番号は50文字以内で入力してください。',
            'closing_day.min' => '締め日は1以上で入力してください。',
            'closing_day.max' => '締め日は31以下で入力してください。',
            'payment_month.min' => '支払月は0以上で入力してください。',
            'payment_month.max' => '支払月は2以下で入力してください。',
            'payment_day.min' => '支払日は1以上で入力してください。',
            'payment_day.max' => '支払日は31以下で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $partner = Partner::findOrFail($id);
        $partner->update($validated);

        return redirect()->route('masters.partners.index')
            ->with([
                'success' => '取引先情報を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $partner = Partner::findOrFail($id);
        $partner->delete();

        return redirect()->route('masters.partners.index')
            ->with([
                'success' => '取引先を削除しました。',
                'alert-type' => 'success'
            ]);
    }
}