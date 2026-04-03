@extends('layouts.app')

@section('title', '車両詳細')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.vehicles.index') }}">車両管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">車両詳細</li>
                </ol>
            </nav>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-truck"></i> 車両詳細情報
                    </h5>
                    <div class="d-flex gap-1">
                        <a href="{{ route('masters.vehicles.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> 一覧に戻る
                        </a>
                        <a href="{{ route('masters.vehicles.edit', $vehicle) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light text-white">
                                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> 基本情報</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">車両コード</div>
                                        <div class="col-md-8">
                                            <code>{{ $vehicle->vehicle_code }}</code>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">登録番号</div>
                                        <div class="col-md-8">{{ $vehicle->registration_number }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">車両種類</div>
                                        <div class="col-md-8">
                                            @if($vehicle->vehicleType)
                                                {{ $vehicle->vehicleType->type_name }}
                                            @else
                                                <span class="text-muted">未設定</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">モデル</div>
                                        <div class="col-md-8">
                                            @if($vehicle->vehicleModel)
                                                {{ $vehicle->vehicleModel->model_name }}
                                                @if($vehicle->vehicleModel->maker)
                                                    <small class="text-muted">({{ $vehicle->vehicleModel->maker }})</small>
                                                @endif
                                            @else
                                                <span class="text-muted">未設定</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">所属営業所</div>
                                        <div class="col-md-8">
                                            @if($vehicle->branch)
                                                {{ $vehicle->branch->branch_code }} - {{ $vehicle->branch->branch_name }}
                                            @else
                                                <span class="text-muted">未設定</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">乗車定員</div>
                                        <div class="col-md-8">{{ $vehicle->seating_capacity }}名</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light text-white">
                                    <h6 class="mb-0"><i class="bi bi-gear"></i> 管理情報</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">所有形態</div>
                                        <div class="col-md-8">
                                            @php
                                                $ownershipTypes = [
                                                    'company' => '会社所有',
                                                    'rental' => 'レンタル',
                                                    'personal' => '個人所有'
                                                ];
                                            @endphp
                                            <span class="badge bg-info">{{ $ownershipTypes[$vehicle->ownership_type] ?? $vehicle->ownership_type }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">車検満了日</div>
                                        <div class="col-md-8">
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
                                            {{ $formattedDate }}
                                            @if(isset($daysRemaining))
                                                @if($daysRemaining < 0)
                                                    <span class="badge bg-danger ms-2">期限切れ</span>
                                                @elseif($daysRemaining <= 30 && $daysRemaining >= 0)
                                                    <span class="badge bg-warning ms-2">残り{{ $daysRemaining }}日</span>
                                                @else
                                                    <span class="badge bg-success ms-2">有効</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">ステータス</div>
                                        <div class="col-md-8">
                                            @if($vehicle->is_active)
                                                <span class="badge bg-success">有効</span>
                                            @else
                                                <span class="badge bg-secondary">無効</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">表示順序</div>
                                        <div class="col-md-8">{{ $vehicle->display_order ?? '未設定' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">登録日時</div>
                                        <div class="col-md-8">
                                            {{ $vehicle->created_at->format('Y年m月d日 H:i') }}
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">更新日時</div>
                                        <div class="col-md-8">
                                            {{ $vehicle->updated_at->format('Y年m月d日 H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($vehicle->remarks)
                    <div class="row">
                        <div class="col-12">
                            <div class="info-section">
                                <h6 class="border-bottom pb-2 mb-3">備考</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $vehicle->remarks }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('masters.vehicles.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> 一覧に戻る
                        </a>
                        <a href="{{ route('masters.vehicles.edit', $vehicle) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> 編集する
                        </a>
                        <form action="{{ route('masters.vehicles.destroy', $vehicle) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('本当に削除しますか？')">
                                <i class="bi bi-trash"></i> 削除する
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection