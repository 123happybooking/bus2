<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class UserCompanyInfo extends Model
{
    protected $table = 'user_company_info';

    protected $primaryKey = 'user_company_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'user_company_name',      // 運行会社名
        'user_plan',              // 契約種類
        'user_start_day',         // 使用開始日
        'company_name',           // 会社名
        'postal_code',            // 郵便番号
        'address',                // 住所
        'phone_number',           // Tel
        'fax_number',             // Fax
        'email',                  // Mail
        'email_for_drv',          // 業務用Mail
        'phone_number_emergency', // 緊急電話
        'work_license_area',      // 運行地域
        'work_license_number',    // 事業者情報
        'work_license_day',       // 事業許可日
        'president_name',         // 取締役
        'work_manager_name_1st',  // 責任者01
        'work_manager_name_2nd',  // 責任者02
        'work_manager_name_3tr',  // 責任者03
        'report_car_count',       // 報告台数
        'report_employee_count',  // 報告従業員数
        'report_drv_count',       // 報告運転手数
        'accounting_manager_name', // 会計責任者名
        'accounting_manager_department', // 会計責任者所属
        'optional_car_insurance', // 保険内容
        'invoice_code',           // 登録番号
        'setup_start_time',       // 開始時刻初期設定値
        'setup_end_time',         // 終了時刻初期設定値
        'setup_bank_name',        // 入金銀行初期設定値
        'setup_company_seal',     // 社印
        'created_by',             // 创建者
        'updated_by'              // 更新者
    ];

    protected $dates = [
        'user_start_day',
        'work_license_day',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'user_company_id' => 'integer',
        'report_car_count' => 'integer',
        'report_employee_count' => 'integer',
        'report_drv_count' => 'integer',
        'user_start_day' => 'date',
        'work_license_day' => 'date',
        'setup_start_time' => 'datetime:H:i:s',
        'setup_end_time' => 'datetime:H:i:s'
    ];

    public $timestamps = true;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';
}