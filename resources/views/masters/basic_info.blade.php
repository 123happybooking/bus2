@extends('layouts.app')

@section('content')
<div class="container-fluid mb-5">
    <form action="{{ route('masters.basic-info.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="bi bi-gear-wide-connected me-2"></i>基本情報マスタ</h3>
            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">更新する</button>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">契約・会社基本情報</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tr>
                        <th class="bg-light w-25">契約会社名 / プラン</th>
                        <td>
                            <div class="row">
                                <div class="col-6"><input type="text" name="contract_company_name" class="form-control" value="{{ $info->contract_company_name }}"></div>
                                <div class="col-6"><input type="text" name="contract_plan" class="form-control" value="{{ $info->contract_plan }}"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">会社名</th>
                        <td><input type="text" name="company_name" class="form-control" value="{{ $info->company_name }}"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">所在地 (郵便番号 / 住所)</th>
                        <td>
                            <div class="row">
                                <div class="col-3"><input type="text" name="postal_code" class="form-control" value="{{ $info->postal_code }}"></div>
                                <div class="col-9"><input type="text" name="address" class="form-control" value="{{ $info->address }}"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">連絡先 (TEL / FAX)</th>
                        <td>
                            <div class="row">
                                <div class="col-6"><input type="text" name="phone_number" class="form-control" value="{{ $info->phone_number }}"></div>
                                <div class="col-6"><input type="text" name="fax_number" class="form-control" value="{{ $info->fax_number }}"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">代表者 / 運行管理者</th>
                        <td>
                            <div class="row">
                                <div class="col-3"><input type="text" name="representative_director" class="form-control" value="{{ $info->representative_director }}" placeholder="代表者"></div>
                                <div class="col-3"><input type="text" name="operation_manager_1" class="form-control" value="{{ $info->operation_manager_1 }}" placeholder="管理者1"></div>
                                <div class="col-3"><input type="text" name="operation_manager_2" class="form-control" value="{{ $info->operation_manager_2 }}" placeholder="管理者2"></div>
                                <div class="col-3"><input type="text" name="operation_manager_3" class="form-control" value="{{ $info->operation_manager_3 }}" placeholder="管理者3"></div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">振込先銀行口座 (1〜6)</div>
            <div class="card-body p-3">
                <div class="row g-3">
                    @for($i=1; $i<=6; $i++)
                    <div class="col-md-4">
                        <label class="small fw-bold">口座 {{ $i }}</label>
                        <input type="text" name="bank_account_{{ $i }}" class="form-control" value="{{ $info->{'bank_account_'.$i} }}">
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">運行管理・帳票（指示書・請求書）設定</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tr>
                        <th class="bg-light w-25">インボイス登録番号</th>
                        <td><input type="text" name="qualified_invoice_issuer_number" class="form-control" value="{{ $info->qualified_invoice_issuer_number }}"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">出庫点呼開始時間</th>
                        <td><input type="time" name="roll_call_start_time" class="form-control w-25" value="{{ $info->roll_call_start_time }}"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">指示書設定 (詳細出力 / 車両名表示)</th>
                        <td>
                            <div class="form-check form-check-inline">
                                <input type="hidden" name="instruction_detail_output" value="0">
                                <input type="checkbox" name="instruction_detail_output" value="1" class="form-check-input" {{ $info->instruction_detail_output ? 'checked' : '' }}>
                                <label class="form-check-label">詳細出力あり</label>
                            </div>
                            <div class="form-check form-check-inline ms-4">
                                <input type="hidden" name="instruction_vehicle_name_display" value="0">
                                <input type="checkbox" name="instruction_vehicle_name_display" value="1" class="form-check-input" {{ $info->instruction_vehicle_name_display ? 'checked' : '' }}>
                                <label class="form-check-label">車両名表示</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">運賃計算 / 安全コスト率</th>
                        <td>
                            <div class="row">
                                <div class="col-4"><input type="text" name="calculated_fare" class="form-control" placeholder="計算運賃" value="{{ $info->calculated_fare }}"></div>
                                <div class="col-4"><input type="text" name="authorized_fare" class="form-control" placeholder="届出運賃" value="{{ $info->authorized_fare }}"></div>
                                <div class="col-4"><input type="number" step="0.1" name="safety_cost_rate" class="form-control" placeholder="安全コスト率" value="{{ $info->safety_cost_rate }}"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">備考（請求書等）</th>
                        <td><textarea name="invoice_payment_note" class="form-control" rows="2">{{ $info->invoice_payment_note }}</textarea></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="text-center mb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">上記の内容で更新する</button>
        </div>
    </form>
</div>
@endsection