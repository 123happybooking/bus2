<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;

class BasicInfo extends Model
{
    protected $table = 'basicinfo';
    protected $primaryKey = 'info_id';

    protected $fillable = [
        'contract_company_name', 'contract_plan', 'contract_start_date', 'contract_end_date',
        'company_name', 'postal_code', 'address', 'phone_number', 'fax_number', 'email_address',
        'admin_email_address', 'emergency_contact_phone', 'operator_info', 'representative_director',
        'operation_manager_1', 'operation_manager_2', 'operation_manager_3', 'business_vehicle_count',
        'employee_count', 'accounting_person', 'bank_account_1', 'bank_account_2', 'bank_account_3',
        'bank_account_4', 'bank_account_5', 'bank_account_6', 'voluntary_insurance_mutual_aid',
        'qualified_invoice_issuer_number', 'pdf_output_setting', 'sticker_size_pdf', 'transport_application_form',
        'roll_call_start_time', 'pre_post_binding_time', 'driver_selection_method', 'driver_duplicate_check',
        'itinerary_detail_order', 'use_common_location_master', 'operation_caution_alert', 'continuous_driving_warning',
        'editable_until_date', 'finalized_status_after_operation', 'company_operation_division',
        'instruction_detail_output', 'instruction_inspection_record', 'instruction_vehicle_name_display',
        'instruction_enlargement', 'instruction_arrival_departure_date', 'instruction_version',
        'instruction_copy_print', 'daily_report_inspection_record', 'daily_report_origin_destination',
        'service_result_list', 'roll_call_exclude_received_vehicle', 'roll_call_display_order',
        'roll_call_midnight_threshold', 'duty_schedule_notification_email', 'fee_calculation_method',
        'authorized_fare_check_invoice', 'invoice_company_name_line_2', 'invoice_reservation_order',
        'show_reservation_id_invoice', 'invoice_destination_display', 'invoice_application_setting',
        'invoice_carryover_request', 'invoice_line_unit', 'invoice_detail_consumption_tax',
        'prev_month_balance_tax', 'invoice_payment_note', 'invoice_amount_tax_included', 'receipt_setting',
        'operation_closing_day', 'quote_validity_period', 'internal_management_id', 'ledger_remaining_display',
        'ledger_initial_setting', 'driver_ledger_remarks_display', 'calculated_fare', 'authorized_fare', 'safety_cost_rate'
    ];
}