<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\UserCompanyInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserCompanyInfoController extends Controller
{
    public function index(Request $request)
    {
        $UserCompanyInfo = UserCompanyInfo::first();
        
        return view('masters.user-company-info.index', compact('UserCompanyInfo'));
    }

    public function update(Request $request, $id)
    {
        try {
            $userCompany = UserCompanyInfo::findOrFail($id);
            
            $rules = [
                'company_name' => 'required|string|max:255',
                'postal_code' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'phone_number' => 'required|string|max:255',
                'fax_number' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'email_for_drv' => 'nullable|email|max:255',
                'phone_number_emergency' => 'required|string|max:255',
                'work_license_area' => 'required|string|max:255',
                'work_license_number' => 'required|string|max:255',
                'work_license_day' => 'required|date',
                'president_name' => 'required|string|max:255',
                'work_manager_name_1st' => 'required|string|max:255',
                'work_manager_name_2nd' => 'nullable|string|max:255',
                'work_manager_name_3rd' => 'nullable|string|max:255',
                'report_car_count' => 'nullable|integer|min:0',
                'report_employee_count' => 'nullable|integer|min:0',
                'report_drv_count' => 'nullable|integer|min:0',
                'accounting_manager_name' => 'nullable|string|max:255',
                'accounting_manager_department' => 'nullable|string|max:255',
                'optional_car_insurance' => 'nullable|string',
                'invoice_code' => 'nullable|string|max:255',
                'setup_start_time' => 'nullable|date_format:H:i',
                'setup_end_time' => 'nullable|date_format:H:i',
                'setup_bank_name' => 'nullable|string|max:255',
                'setup_company_seal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ];

            $messages = [
                'company_name.required' => '会社名は必須です。',
                'company_name.max' => '会社名は255文字以内で入力してください。',
                'postal_code.required' => '郵便番号は必須です。',
                'postal_code.max' => '郵便番号は255文字以内で入力してください。',
                'address.required' => '住所は必須です。',
                'address.max' => '住所は255文字以内で入力してください。',
                'phone_number.required' => 'Telは必須です。',
                'phone_number.max' => 'Telは255文字以内で入力してください。',
                'fax_number.max' => 'Faxは255文字以内で入力してください。',
                'email.email' => '有効なMailを入力してください。',
                'email.max' => 'Mailは255文字以内で入力してください。',
                'email_for_drv.email' => '有効な業務用Mailを入力してください。',
                'email_for_drv.max' => '業務用Mailは255文字以内で入力してください。',
                'phone_number_emergency.required' => '緊急電話は必須です。',
                'phone_number_emergency.max' => '緊急電話は255文字以内で入力してください。',
                'work_license_area.required' => '運行地域は必須です。',
                'work_license_area.max' => '運行地域は255文字以内で入力してください。',
                'work_license_number.required' => '事業者情報は必須です。',
                'work_license_number.max' => '事業者情報は255文字以内で入力してください。',
                'work_license_day.required' => '事業許可日は必須です。',
                'work_license_day.date' => '有効な事業許可日を入力してください。',
                'president_name.required' => '取締役は必須です。',
                'president_name.max' => '取締役は255文字以内で入力してください。',
                'work_manager_name_1st.required' => '責任者01は必須です。',
                'work_manager_name_1st.max' => '責任者01は255文字以内で入力してください。',
                'work_manager_name_2nd.max' => '責任者02は255文字以内で入力してください。',
                'work_manager_name_3rd.max' => '責任者03は255文字以内で入力してください。',
                'report_car_count.integer' => '報告台数は整数で入力してください。',
                'report_car_count.min' => '報告台数は0以上で入力してください。',
                'report_employee_count.integer' => '報告従業員数は整数で入力してください。',
                'report_employee_count.min' => '報告従業員数は0以上で入力してください。',
                'report_drv_count.integer' => '報告運転手数は整数で入力してください。',
                'report_drv_count.min' => '報告運転手数は0以上で入力してください。',
                'accounting_manager_name.max' => '会計責任者名は255文字以内で入力してください。',
                'accounting_manager_department.max' => '会計責任者所属は255文字以内で入力してください。',
                'optional_car_insurance.max' => '保険内容は正しく入力してください。',
                'invoice_code.max' => '登録番号は255文字以内で入力してください。',
                'setup_start_time.date_format' => '開始時刻初期設定値は正しい時刻形式(HH:MM)で入力してください。',
                'setup_end_time.date_format' => '終了時刻初期設定値は正しい時刻形式(HH:MM)で入力してください。',
                'setup_bank_name.max' => '入金銀行初期設定値は255文字以内で入力してください。',
                'setup_company_seal.image' => '社印は画像ファイルをアップロードしてください。',
                'setup_company_seal.mimes' => '社印はjpeg、png、jpg、gif形式のファイルをアップロードしてください。',
                'setup_company_seal.max' => '社印のファイルサイズは2MB以内でアップロードしてください。',
            ];

            $validated = $request->validate($rules, $messages);

            if ($request->hasFile('setup_company_seal') && $request->file('setup_company_seal')->isValid()) {
                if ($userCompany->setup_company_seal) {
                    Storage::disk('public')->delete($userCompany->setup_company_seal);
                }
                
                $file = $request->file('setup_company_seal');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                $path = $file->storeAs('company_seals', $fileName, 'public');
                
                if ($path) {
                    $validated['setup_company_seal'] = $path;
                }
            }

            $validated['updated_by'] = auth()->user()->name ?? 'system';
            
            $userCompany->update($validated);

            return redirect()->route('masters.user-company-info.index')
                ->with('success', '運行会社情報を更新しました。');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '更新に失敗しました。');
        }
    }
}