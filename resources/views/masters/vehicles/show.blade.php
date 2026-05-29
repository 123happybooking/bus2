@extends('layouts.app')

@section('title', '車両詳細: ' . ($vehicle->registration_number ?? $vehicle->vehicle_code))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.vehicles.index') }}">車両管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $vehicle->registration_number ?? $vehicle->vehicle_code }}</li>
                </ol>
            </nav>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif
            
            <div class="card shadow-sm card-edit">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-truck"></i> 車両詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.vehicles.edit', $vehicle) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.vehicles.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">車両コード</dt>
                                <dd class="col-sm-8">
                                    <code>{{ $vehicle->vehicle_code }}</code>
                                </dd>
                                
                                <dt class="col-sm-4">登録番号</dt>
                                <dd class="col-sm-8">{{ $vehicle->registration_number }}</dd>
                                
                                <dt class="col-sm-4">車両種類</dt>
                                <dd class="col-sm-8">
                                    @if($vehicle->vehicleType)
                                        {{ $vehicle->vehicleType->type_name }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">モデル</dt>
                                <dd class="col-sm-8">
                                    @if($vehicle->vehicleModel)
                                        {{ $vehicle->vehicleModel->model_name }}
                                        @if($vehicle->vehicleModel->maker)
                                            <small class="text-muted">({{ $vehicle->vehicleModel->maker }})</small>
                                        @endif
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">車両等級</dt>
                                <dd class="col-sm-8">
                                    @if($vehicle->vehicleGrade)
                                        {{ $vehicle->vehicleGrade->description ?? $vehicle->vehicleGrade->grade_name }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">共有設定</dt>
                                <dd class="col-sm-8">
                                    @if($vehicle->is_share)
                                        @if($vehicle->share_to == 'all')
                                            <span class="badge bg-success">共有中</span>
                                            <span class="text-muted ms-2">すべての友達会社と共有</span>
                                        @elseif($vehicle->share_to)
                                            @php
                                                $sharedCompanyIds = json_decode($vehicle->share_to, true);
                                                $sharedCompanyNames = [];
                                                if (is_array($sharedCompanyIds) && !empty($sharedCompanyIds)) {
                                                    $sharedCompanies = App\Models\Masters\User::on('mysql')
                                                        ->whereIn('id', $sharedCompanyIds)
                                                        ->select('user_company_name', 'name')
                                                        ->get();
                                                    foreach ($sharedCompanies as $company) {
                                                        $sharedCompanyNames[] = $company->user_company_name ?: $company->name;
                                                    }
                                                }
                                            @endphp
                                            <span class="badge bg-success">共有中</span>
                                            <span class="text-muted ms-2">特定の会社と共有: {{ implode('、', $sharedCompanyNames) }}</span>
                                        @else
                                            <span class="badge bg-secondary">共有なし</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">共有なし</span>
                                    @endif
                                </dd>
                                
                                <!--<dt class="col-sm-4">車両色</dt>-->
                                <!--<dd class="col-sm-8">-->
                                <!--    @if($vehicle->vehicle_color)-->
                                <!--        {{ $vehicle->vehicle_color }}-->
                                <!--    @else-->
                                <!--        <span class="text-muted">未設定</span>-->
                                <!--    @endif-->
                                <!--</dd>-->
                                
                                <dt class="col-sm-4">乗車定員</dt>
                                <dd class="col-sm-8">{{ $vehicle->seating_capacity }}名</dd>
                                
                                <dt class="col-sm-4">表示順序</dt>
                                <dd class="col-sm-8">
                                    @if($vehicle->display_order)
                                        <span class="badge bg-info">{{ $vehicle->display_order }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">ステータス</dt>
                                <dd class="col-sm-8">
                                    @if($vehicle->is_active)
                                        <span class="badge bg-success">有効</span>
                                    @else
                                        <span class="badge bg-secondary">無効</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">車両画像</dt>
                                <dd class="col-sm-8">
                                    @if($vehicle->image_path)
                                        <img src="{{ asset('storage/' . $vehicle->image_path) }}" 
                                             alt="{{ $vehicle->registration_number }}" 
                                             style="width: 100px; height: 100px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                    @else
                                        <span class="text-muted">画像はありません</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">所属・管理情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">所属営業所</dt>
                                <dd class="col-sm-8">
                                    @if($vehicle->branch)
                                        <div>
                                            <strong>{{ $vehicle->branch->branch_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                コード: {{ $vehicle->branch->branch_code }}
                                                @if($vehicle->branch->phone_number)
                                                    <br>電話: {{ $vehicle->branch->phone_number }}
                                                @endif
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">営業所住所</dt>
                                <dd class="col-sm-8">
                                    @if($vehicle->branch && $vehicle->branch->address)
                                        {{ $vehicle->branch->address }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">所有形態</dt>
                                <dd class="col-sm-8">
                                    @php
                                        $ownershipTypes = [
                                            'own' => '自社',
                                            'reservable' => '予約用',
                                            'rental' => '傭車'
                                        ];
                                    @endphp
                                    <span class="badge bg-info">{{ $ownershipTypes[$vehicle->ownership_type] ?? $vehicle->ownership_type }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">車検満了日</dt>
                                <dd class="col-sm-8">
                                    @php
                                        $date = $vehicle->inspection_expiration_date;
                                        if ($date instanceof \Carbon\Carbon) {
                                            $formattedDate = $date->format('Y年m月d日');
                                            $daysRemaining = now()->startOfDay()->diffInDays($date->startOfDay(), false);
                                        } else {
                                            try {
                                                $carbonDate = \Carbon\Carbon::parse($date)->startOfDay();
                                                $formattedDate = $carbonDate->format('Y年m月d日');
                                                $daysRemaining = now()->startOfDay()->diffInDays($carbonDate, false);
                                            } catch (Exception $e) {
                                                $formattedDate = $date;
                                                $daysRemaining = null;
                                            }
                                        }
                                    @endphp
                                    <span class="{{ isset($daysRemaining) && $daysRemaining < 0 ? 'text-danger' : (isset($daysRemaining) && $daysRemaining <= 30 && $daysRemaining >= 0 ? 'text-warning' : '') }}">
                                        {{ $formattedDate }}
                                    </span>
                                    @if(isset($daysRemaining))
                                        @if($daysRemaining < 0)
                                            <span class="badge bg-danger ms-1">期限切れ ({{ abs($daysRemaining) }}日前)</span>
                                        @elseif($daysRemaining <= 30 && $daysRemaining >= 0)
                                            <span class="badge bg-warning ms-1">間近 (あと{{ $daysRemaining }}日)</span>
                                        @else
                                            <span class="badge bg-success ms-1">有効 (あと{{ $daysRemaining }}日)</span>
                                        @endif
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($vehicle->remarks)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">備考</h6>
                            <dl class="row">
                                <dt class="col-sm-2">備考</dt>
                                <dd class="col-sm-10">{{ $vehicle->remarks }}</dd>
                            </dl>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-2">登録日時</dt>
                                <dd class="col-sm-4">{{ $vehicle->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4">{{ $vehicle->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.vehicles.edit', $vehicle) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.vehicles.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(registrationNumber) {
                                return confirm(`本当に「${registrationNumber}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.vehicles.destroy', $vehicle) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $vehicle->registration_number ?? $vehicle->vehicle_code }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> 削除
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    font-size: 0.85em;
}

.d-flex.gap-2 > * {
    margin-right: 0.5rem;
}
.d-flex.gap-2 > *:last-child {
    margin-right: 0;
}

.card-edit .card-body dl {
    margin-bottom: 0;
}

.card-edit .card-body dt {
    font-weight: 500;
    color: #6c757d;
    font-size: 0.85rem;
}

.card-edit .card-body dd {
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
}
</style>
@endpush