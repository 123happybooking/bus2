<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\BasicInfo;
use Illuminate\Http\Request;

class BasicInfoController extends Controller
{
    public function index(Request $request)
    {
        $basicInfos = BasicInfo::all();
        
        return view('masters.basicinfo.index', compact('basicInfos'));
    }

    public function edit($basicinfo)
    {
        $basicInfo = BasicInfo::findOrFail($basicinfo);
        return view('masters.basicinfo.edit', compact('basicInfo'));
    }

    public function update(Request $request, $basicinfo)
    {
        $basicInfo = BasicInfo::findOrFail($basicinfo);
        
        $rules = [
            'contract_company_name' => 'required|string|max:255',
            'contract_plan' => 'required|string|max:100',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'company_name' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'required|string|max:500',
            'phone_number' => 'required|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'email_address' => 'required|email|max:255',
            'admin_email_address' => 'nullable|email|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'operator_info' => 'nullable|string|max:500',
            'representative_director' => 'nullable|string|max:255',
            'operation_manager_1' => 'nullable|string|max:255',
            'operation_manager_2' => 'nullable|string|max:255',
            'operation_manager_3' => 'nullable|string|max:255',
            'business_vehicle_count' => 'nullable|integer|min:0',
            'employee_count' => 'nullable|integer|min:0',
            'accounting_person' => 'nullable|string|max:255',
            'bank_account_1' => 'nullable|string|max:500',
            'bank_account_2' => 'nullable|string|max:500',
            'bank_account_3' => 'nullable|string|max:500',
            'bank_account_4' => 'nullable|string|max:500',
            'bank_account_5' => 'nullable|string|max:500',
            'bank_account_6' => 'nullable|string|max:500',
            'voluntary_insurance_mutual_aid' => 'nullable|string|max:500',
            'qualified_invoice_issuer_number' => 'nullable|string|max:100',
            'pdf_output_setting' => 'nullable|string|max:50',
            'sticker_size_pdf' => 'nullable|string|max:50',
            'transport_application_form' => 'nullable|string|max:50',
            'roll_call_start_time' => 'nullable|date_format:H:i',
            'pre_post_binding_time' => 'nullable|integer',
            'driver_selection_method' => 'nullable|string|max:50',
            'driver_duplicate_check' => 'nullable|boolean',
            'itinerary_detail_order' => 'nullable|string|max:50',
            'use_common_location_master' => 'nullable|boolean',
            'operation_caution_alert' => 'nullable|boolean',
            'continuous_driving_warning' => 'nullable|boolean',
            'editable_until_date' => 'nullable|integer',
            'finalized_status_after_operation' => 'nullable|boolean',
            'company_operation_division' => 'nullable|boolean',
            'instruction_detail_output' => 'nullable|boolean',
            'instruction_inspection_record' => 'nullable|boolean',
            'instruction_vehicle_name_display' => 'nullable|boolean',
            'instruction_enlargement' => 'nullable|boolean',
            'instruction_arrival_departure_date' => 'nullable|boolean',
            'instruction_version' => 'nullable|string|max:50',
            'instruction_copy_print' => 'nullable|boolean',
            'daily_report_inspection_record' => 'nullable|boolean',
            'daily_report_origin_destination' => 'nullable|boolean',
            'service_result_list' => 'nullable|string|max:50',
            'roll_call_exclude_received_vehicle' => 'nullable|boolean',
            'roll_call_display_order' => 'nullable|string|max:50',
            'roll_call_midnight_threshold' => 'nullable|integer',
            'duty_schedule_notification_email' => 'nullable|string|max:255',
            'fee_calculation_method' => 'nullable|string|max:50',
            'authorized_fare_check_invoice' => 'nullable|boolean',
            'invoice_company_name_line_2' => 'nullable|string|max:255',
            'invoice_reservation_order' => 'nullable|string|max:50',
            'show_reservation_id_invoice' => 'nullable|boolean',
            'invoice_destination_display' => 'nullable|string|max:50',
            'invoice_application_setting' => 'nullable|string|max:50',
            'invoice_carryover_request' => 'nullable|boolean',
            'invoice_line_unit' => 'nullable|string|max:50',
            'invoice_detail_consumption_tax' => 'nullable|boolean',
            'prev_month_balance_tax' => 'nullable|boolean',
            'invoice_payment_note' => 'nullable|string|max:500',
            'invoice_amount_tax_included' => 'nullable|boolean',
            'receipt_setting' => 'nullable|string|max:50',
            'operation_closing_day' => 'nullable|integer|min:1|max:31',
            'quote_validity_period' => 'nullable|integer',
            'internal_management_id' => 'nullable|string|max:100',
            'ledger_remaining_display' => 'nullable|boolean',
            'ledger_initial_setting' => 'nullable|string|max:50',
            'driver_ledger_remarks_display' => 'nullable|boolean',
            'calculated_fare' => 'nullable|boolean',
            'authorized_fare' => 'nullable|boolean',
            'safety_cost_rate' => 'nullable|numeric|between:0,100'
        ];

        $messages = [
            'contract_company_name.required' => '契約会社名は必須です。',
            'contract_company_name.max' => '契約会社名は255文字以内で入力してください。',
            'contract_plan.required' => '契約プランは必須です。',
            'contract_plan.max' => '契約プランは100文字以内で入力してください。',
            'contract_start_date.required' => '契約開始日は必須です。',
            'contract_start_date.date' => '契約開始日は有効な日付形式で入力してください。',
            'contract_end_date.date' => '契約終了日は有効な日付形式で入力してください。',
            'contract_end_date.after' => '契約終了日は契約開始日より後の日付を指定してください。',
            'company_name.required' => '会社名は必須です。',
            'company_name.max' => '会社名は255文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.required' => '住所は必須です。',
            'address.max' => '住所は500文字以内で入力してください。',
            'phone_number.required' => '電話番号は必須です。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'email_address.required' => 'メールアドレスは必須です。',
            'email_address.email' => '有効なメールアドレスを入力してください。',
            'email_address.max' => 'メールアドレスは255文字以内で入力してください。',
            'admin_email_address.email' => '有効な管理者メールアドレスを入力してください。',
            'admin_email_address.max' => '管理者メールアドレスは255文字以内で入力してください。',
            'emergency_contact_phone.max' => '緊急連絡先電話番号は20文字以内で入力してください。',
            'operator_info.max' => '事業者情報は500文字以内で入力してください。',
            'representative_director.max' => '代表取締役は255文字以内で入力してください。',
            'operation_manager_1.max' => '運営担当者1は255文字以内で入力してください。',
            'operation_manager_2.max' => '運営担当者2は255文字以内で入力してください。',
            'operation_manager_3.max' => '運営担当者3は255文字以内で入力してください。',
            'business_vehicle_count.integer' => '営業車両数は整数で入力してください。',
            'business_vehicle_count.min' => '営業車両数は0以上の数値で入力してください。',
            'employee_count.integer' => '従業員数は整数で入力してください。',
            'employee_count.min' => '従業員数は0以上の数値で入力してください。',
            'accounting_person.max' => '経理担当者は255文字以内で入力してください。',
            'bank_account_1.max' => '銀行口座1は500文字以内で入力してください。',
            'bank_account_2.max' => '銀行口座2は500文字以内で入力してください。',
            'bank_account_3.max' => '銀行口座3は500文字以内で入力してください。',
            'bank_account_4.max' => '銀行口座4は500文字以内で入力してください。',
            'bank_account_5.max' => '銀行口座5は500文字以内で入力してください。',
            'bank_account_6.max' => '銀行口座6は500文字以内で入力してください。',
            'voluntary_insurance_mutual_aid.max' => '任意保険・共済は500文字以内で入力してください。',
            'qualified_invoice_issuer_number.max' => '適格請求書発行事業者登録番号は100文字以内で入力してください。',
            'pdf_output_setting.max' => 'PDF出力設定は50文字以内で入力してください。',
            'sticker_size_pdf.max' => 'ステッカーサイズPDFは50文字以内で入力してください。',
            'transport_application_form.max' => '輸送申込書は50文字以内で入力してください。',
            'roll_call_start_time.date_format' => '点呼開始時間は時:分の形式で入力してください。',
            'pre_post_binding_time.integer' => '前後拘束時間は整数で入力してください。',
            'driver_selection_method.max' => 'ドライバー選抜方法は50文字以内で入力してください。',
            'driver_duplicate_check.boolean' => 'ドライバー重複チェックは正しい値で入力してください。',
            'itinerary_detail_order.max' => '行程詳細表示順は50文字以内で入力してください。',
            'use_common_location_master.boolean' => '共通場所マスタ使用は正しい値で入力してください。',
            'operation_caution_alert.boolean' => '運行注意アラートは正しい値で入力してください。',
            'continuous_driving_warning.boolean' => '連続運転警告は正しい値で入力してください。',
            'editable_until_date.integer' => '編集可能期限は整数で入力してください。',
            'finalized_status_after_operation.boolean' => '運行後確定ステータスは正しい値で入力してください。',
            'company_operation_division.boolean' => '会社別運行区分は正しい値で入力してください。',
            'instruction_detail_output.boolean' => '指示書詳細出力は正しい値で入力してください。',
            'instruction_inspection_record.boolean' => '指示書点検記録は正しい値で入力してください。',
            'instruction_vehicle_name_display.boolean' => '指示書車両名表示は正しい値で入力してください。',
            'instruction_enlargement.boolean' => '指示書拡大表示は正しい値で入力してください。',
            'instruction_arrival_departure_date.boolean' => '指示書入出庫日表示は正しい値で入力してください。',
            'instruction_version.max' => '指示書バージョンは50文字以内で入力してください。',
            'instruction_copy_print.boolean' => '指示書複写印刷は正しい値で入力してください。',
            'daily_report_inspection_record.boolean' => '日報点検記録は正しい値で入力してください。',
            'daily_report_origin_destination.boolean' => '日報出発地目的地は正しい値で入力してください。',
            'service_result_list.max' => '実績一覧は50文字以内で入力してください。',
            'roll_call_exclude_received_vehicle.boolean' => '点呼受領車除外は正しい値で入力してください。',
            'roll_call_display_order.max' => '点呼表示順は50文字以内で入力してください。',
            'roll_call_midnight_threshold.integer' => '点呼深夜閾値は整数で入力してください。',
            'duty_schedule_notification_email.max' => '勤務表通知メールは255文字以内で入力してください。',
            'fee_calculation_method.max' => '料金計算方法は50文字以内で入力してください。',
            'authorized_fare_check_invoice.boolean' => '認可運賃チェック（請求書）は正しい値で入力してください。',
            'invoice_company_name_line_2.max' => '請求書会社名2行目は255文字以内で入力してください。',
            'invoice_reservation_order.max' => '請求書予約表示順は50文字以内で入力してください。',
            'show_reservation_id_invoice.boolean' => '請求書予約ID表示は正しい値で入力してください。',
            'invoice_destination_display.max' => '請求書宛先表示は50文字以内で入力してください。',
            'invoice_application_setting.max' => '請求書申込書設定は50文字以内で入力してください。',
            'invoice_carryover_request.boolean' => '請求書繰越要請は正しい値で入力してください。',
            'invoice_line_unit.max' => '請求書行単位は50文字以内で入力してください。',
            'invoice_detail_consumption_tax.boolean' => '請求書明細消費税は正しい値で入力してください。',
            'prev_month_balance_tax.boolean' => '前月残高消費税は正しい値で入力してください。',
            'invoice_payment_note.max' => '請求書支払条件は500文字以内で入力してください。',
            'invoice_amount_tax_included.boolean' => '請求書金額税込表示は正しい値で入力してください。',
            'receipt_setting.max' => '領収書設定は50文字以内で入力してください。',
            'operation_closing_day.integer' => '営業締日は整数で入力してください。',
            'operation_closing_day.min' => '営業締日は1以上で入力してください。',
            'operation_closing_day.max' => '営業締日は31以下で入力してください。',
            'quote_validity_period.integer' => '見積有効期間は整数で入力してください。',
            'internal_management_id.max' => '社内管理IDは100文字以内で入力してください。',
            'ledger_remaining_display.boolean' => '台帳残高表示は正しい値で入力してください。',
            'ledger_initial_setting.max' => '台帳初期設定は50文字以内で入力してください。',
            'driver_ledger_remarks_display.boolean' => 'ドライバー台帳備考表示は正しい値で入力してください。',
            'calculated_fare.boolean' => '計算運賃は正しい値で入力してください。',
            'authorized_fare.boolean' => '認可運賃は正しい値で入力してください。',
            'safety_cost_rate.numeric' => '安全コスト率は数値で入力してください。',
            'safety_cost_rate.between' => '安全コスト率は0から100の間で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $booleanFields = [
            'driver_duplicate_check', 'use_common_location_master', 'operation_caution_alert',
            'continuous_driving_warning', 'finalized_status_after_operation', 'company_operation_division',
            'instruction_detail_output', 'instruction_inspection_record', 'instruction_vehicle_name_display',
            'instruction_enlargement', 'instruction_arrival_departure_date', 'instruction_copy_print',
            'daily_report_inspection_record', 'daily_report_origin_destination',
            'roll_call_exclude_received_vehicle', 'authorized_fare_check_invoice',
            'show_reservation_id_invoice', 'invoice_carryover_request', 'invoice_detail_consumption_tax',
            'prev_month_balance_tax', 'invoice_amount_tax_included', 'ledger_remaining_display',
            'driver_ledger_remarks_display', 'calculated_fare', 'authorized_fare'
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = $request->has($field) ? 1 : 0;
        }

        try {
            $basicInfo->update($validated);

            return redirect()->route('masters.basicinfo.index')
                ->with([
                    'success' => '基本情報を更新しました。',
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
}