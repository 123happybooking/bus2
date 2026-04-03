@extends('layouts.app')

@section('title', '入金詳細の編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.payments.index', ['group_id' => request('group_id')]) }}">入金消し込み履歴</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.payments.show', ['payment' => $payment, 'group_id' => request('group_id')]) }}">入金詳細</a></li>
                    <li class="breadcrumb-item active" aria-current="page">編集</li>
                </ol>
            </nav>
            
            <!-- 错误提示 -->
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> 入力内容にエラーがあります
                <ul class="mb-0 mt-2 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <!-- 卡片主体 -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square"></i> 入金詳細の編集
                    </h5>
                    <div>
                        <span class="badge bg-light text-primary me-2">編集中</span>
                        <span class="badge bg-light text-dark font-monospace">{{ $payment->batch_token }}</span>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.payments.update', ['payment' => $payment, 'group_id' => request('group_id')]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- 第一部分：基本情报 (可编辑部分高亮) -->
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-info-circle"></i> 基本情報
                        </h6>
                        <div class="row mb-4">
                            
                            <!-- 【可编辑】入金日 -->
                            <div class="col-md-3 mb-3">
                                <label for="payment_date" class="form-label text-muted small">入金日 <span class="text-danger">*</span></label>
                                <input type="date" 
                                       id="payment_date" 
                                       name="payment_date" 
                                       class="form-control @error('payment_date') is-invalid @enderror" 
                                       value="{{ old('payment_date', \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d')) }}"
                                       required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>



                            <!-- 【可编辑】処理担当者 -->
                            <div class="col-md-3 mb-3">
                                <label for="staff_id" class="form-label text-muted small">請求担当</label>
                                <select id="staff_id" 
                                        name="staff_id" 
                                        class="form-select @error('staff_id') is-invalid @enderror" 
                                        required>
                                    <option value="0">請求担当選択</option>
                                    <!-- 循环员工列表 -->
                                    @foreach($staffs as $staff)
                                        
                                        <option value="{{ $staff->id }}" 
                                                {{ old('staff_id', $payment->staff_id) == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('staff_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="bank_id" class="form-label text-muted small">銀行</label>
                                
                                <select id="bank_id" 
                                        name="bank_id" 
                                        class="form-select @error('bank_id') is-invalid @enderror" 
                                        required>

                                    <!-- 循环员工列表 -->
                                    @foreach($banks as $bank)
                                        <option value="0">銀行を選択</option>
                                        <option value="{{ $bank->id }}" 
                                                {{ old('bank_id', $payment->bank_id) == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->bank_name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('bank_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                            </div>
                            
                            <!-- 【可编辑】備考 -->
                            <div class="col-md-12 mb-3">
                                <label for="remark" class="form-label text-muted small">備考</label>
                                <textarea id="remark" 
                                          name="remark" 
                                          rows="2" 
                                          class="form-control @error('remark') is-invalid @enderror" 
                                          placeholder="">{{ old('remark', $payment->remark) }}</textarea>
                                @error('remark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- 第二部分：金额汇总 (只读) -->
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-calculator"></i> 金額サマリー (変更不可)
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body text-center">
                                        <div class="text-muted small mb-1">合計入金額</div>
                                        <div class="fs-4 fw-bold text-success font-monospace">
                                            {{ number_format($payment->total_amount, 0) }} <small class="fs-6">{{$payment->currency_code}}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body text-center">
                                        <div class="text-muted small mb-1">対象件数</div>
                                        <div class="fs-4 fw-bold text-primary font-monospace">
                                            {{ $details->count() }} <small class="fs-6">件</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body text-center">
                                        <div class="text-muted small mb-1">登録日時</div>
                                        <div class="fs-6 fw-bold text-dark mt-2">
                                            {{ $payment->created_at->format('Y/m/d H:i') }}
                                        </div>
                                        <div class="fs-6 text-muted small">
                                            更新: {{ $payment->updated_at->format('Y/m/d H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 第三部分：核销明细列表 (只读) -->
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-list-check"></i> 消し込み明細 (変更不可)
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle bg-light opacity-75">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">No.</th>
                                        <th class="text-center" style="width: 150px;">請求書番号</th>
                                        <th class="text-center" style="width: 200px;">請求先</th>
                                        <th class="text-center" style="width: 120px;">請求日</th>
                                        <th class="text-end" style="width: 120px;">請求金額</th>
                                        <th class="text-end" style="width: 120px;">消込金額</th>
                                        <th class="text-end" style="width: 120px;">残高</th>
                                        <th class="text-center" style="width: 100px;">状態</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($details as $index => $detail)
                                        @php
                                            $invoice = $detail->invoice;
                                            $remaining = ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0);
                                            $statusClass = '';
                                            $statusText = '';
                                            if (($invoice->paid_amount ?? 0) >= ($invoice->total_amount ?? 0)) {
                                                $statusClass = 'bg-success';
                                                $statusText = '完了';
                                            } elseif (($invoice->paid_amount ?? 0) > 0) {
                                                $statusClass = 'bg-warning text-dark';
                                                $statusText = '一部';
                                            } else {
                                                $statusClass = 'bg-secondary';
                                                $statusText = '未済';
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                                            <td class="text-center font-monospace fw-bold text-primary">
                                                {{ $invoice->invoice_number ?? '' }}
                                            </td>
                                            <td class="text-center">
                                                {{ $invoice->agency->agency_name ?? '' }}
                                            </td>
                                            <td class="text-center small">
                                                {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y/m/d') : '-' }}
                                            </td>
                                            <td class="text-end font-monospace">
                                                {{ number_format($invoice->total_amount ?? 0, 0) }}
                                            </td>
                                            <td class="text-end font-monospace fw-bold text-success">
                                                {{ number_format($detail->write_off_amount, 0) }}
                                            </td>
                                            <td class="text-end font-monospace text-muted">
                                                {{ number_format($remaining, 0) }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                明細データがありません
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- 按钮区域 -->
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.payments.show', $payment) }}" class="btn btn-secondary">
                                    <i class="bi bi-eye"></i> 詳細を見る
                                </a>
                                <a href="{{ route('masters.payments.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection