<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Facility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $query = Facility::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('facility_code', 'like', "%{$search}%")
                  ->orWhere('facility_name', 'like', "%{$search}%")
                  ->orWhere('facility_kana', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }
        
        $facilities = $query->orderBy('facility_code')->paginate(20);
        
        if ($request->has('search')) {
            $facilities->appends(['search' => $request->search]);
        }
        
        return view('masters.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('masters.facilities.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'facility_code' => 'required|string|max:20|unique:facilities,facility_code',
            'category' => 'nullable|string|max:50',
            'facility_name' => 'required|string|max:100',
            'facility_kana' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'bus_parking_available' => 'nullable|boolean',
            'parking_remarks' => 'nullable|string|max:500',
        ];

        $messages = [
            'facility_code.required' => '施設コードは必須です。',
            'facility_code.unique' => 'この施設コードは既に使用されています。',
            'facility_code.max' => '施設コードは20文字以内で入力してください。',
            'category.max' => 'カテゴリは50文字以内で入力してください。',
            'facility_name.required' => '施設名は必須です。',
            'facility_name.max' => '施設名は100文字以内で入力してください。',
            'facility_kana.max' => '施設名（カナ）は100文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は200文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'parking_remarks.max' => '駐車場備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['bus_parking_available'] = $request->has('bus_parking_available') ? 1 : 0;

        try {
            Facility::create($validated);

            return redirect()->route('masters.facilities.index')
                ->with([
                    'success' => '施設を登録しました。',
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

    public function show(Facility $facility)
    {
        return view('masters.facilities.show', compact('facility'));
    }

    public function edit(Facility $facility)
    {
        return view('masters.facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $rules = [
            'facility_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('facilities')->ignore($facility->id),
            ],
            'category' => 'nullable|string|max:50',
            'facility_name' => 'required|string|max:100',
            'facility_kana' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'bus_parking_available' => 'nullable|boolean',
            'parking_remarks' => 'nullable|string|max:500',
        ];

        $messages = [
            'facility_code.required' => '施設コードは必須です。',
            'facility_code.unique' => 'この施設コードは既に使用されています。',
            'facility_code.max' => '施設コードは20文字以内で入力してください。',
            'category.max' => 'カテゴリは50文字以内で入力してください。',
            'facility_name.required' => '施設名は必須です。',
            'facility_name.max' => '施設名は100文字以内で入力してください。',
            'facility_kana.max' => '施設名（カナ）は100文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は200文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'parking_remarks.max' => '駐車場備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['bus_parking_available'] = $request->has('bus_parking_available') ? 1 : 0;

        try {
            $facility->update($validated);

            return redirect()->route('masters.facilities.index')
                ->with([
                    'success' => '施設情報を更新しました。',
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

    public function destroy(Facility $facility)
    {
        try {
            $facility->delete();

            return redirect()->route('masters.facilities.index')
                ->with([
                    'success' => '施設を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            return redirect()->route('masters.facilities.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}