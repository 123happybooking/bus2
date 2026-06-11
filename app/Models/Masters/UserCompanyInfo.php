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
        'user_company_name',
        'user_plan',
        'user_start_day',
        'company_name',
        'company_name_en',
        'postal_code',
        'address',
        'address_en',
        'phone_number',
        'fax_number',
        'email',
        'email_for_drv',
        'phone_number_emergency',
        'work_license_area',
        'work_license_number',
        'work_license_day',
        'president_name',
        'work_manager_name_1st',
        'work_manager_name_2nd',
        'work_manager_name_3tr',
        'report_car_count',
        'report_employee_count',
        'report_drv_count',
        'accounting_manager_name',
        'accounting_manager_department',
        'optional_car_insurance',
        'invoice_code',
        'setup_start_time',
        'setup_end_time',
        'setup_bank_name',
        'setup_company_seal',
        'company_logo',
        'start_year',
        'fiscal_month',  
        'created_by',
        'updated_by'
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
        'start_year' => 'integer',
        'fiscal_month' => 'integer', 
        'user_start_day' => 'date',
        'work_license_day' => 'date',
        'setup_start_time' => 'datetime:H:i:s',
        'setup_end_time' => 'datetime:H:i:s'
    ];

    public $timestamps = true;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';
}