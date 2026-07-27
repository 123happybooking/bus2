@extends('layouts.app')

@section('title', '車両種類編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.vehicle-types.index') }}">車両種類管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">車両種類編集</li>
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
            
            <form action="{{ route('masters.vehicle-types.update', $vehicleType) }}" method="POST" id="vehicleTypeForm">
                @csrf
                @method('PUT')
                
                <div class="card shadow-sm mb-4 card-edit">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-truck"></i> 車両種類基本情報
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <td class="bg-light" style="width: 15%;">
                                        <label for="type_name" class="form-label required mb-0">車両種類名</label>
                                    </td>
                                    <td class="bg-white">
                                        <input type="text" class="form-control @error('type_name') is-invalid @enderror" 
                                               id="type_name" name="type_name" 
                                               value="{{ old('type_name', $vehicleType->type_name) }}" 
                                               required maxlength="255" placeholder="例: 小型トラック">
                                        @error('type_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="bg-light" style="width: 25%;">
                                        <small class="form-text text-muted mb-0">※ 必須、255文字以内、他と重複不可</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4 card-edit">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
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
                                        <th style="width: 40px;">No.</th>
                                        <th style="width: 150px;">モデル名</th>
                                        <th style="width: 120px;">モデルCode</th>
                                        <th style="width: 120px;">メーカー</th>
                                        <th>備考</th>
                                        <th style="width: 200px;">操作</th>
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
                                        <tr data-index="{{ $numericIndex }}" data-id="{{ $oldModel['id'] ?? '' }}">
                                            <td class="text-center align-middle display-order">{{ $numericIndex + 1 }}</td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm model-name" 
                                                       name="models[{{ $numericIndex }}][model_name]" 
                                                       value="{{ $oldModel['model_name'] ?? '' }}"
                                                       maxlength="100" placeholder="例: ハイエース">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm model-code" 
                                                       name="models[{{ $numericIndex }}][model_code]" 
                                                       value="{{ $oldModel['model_code'] ?? '' }}"
                                                       maxlength="50" placeholder="例: HIACE-001">
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
                                                <input type="hidden" name="models[{{ $numericIndex }}][id]" value="{{ $oldModel['id'] ?? '' }}">
                                                <input type="hidden" name="models[{{ $numericIndex }}][display_order]" value="{{ $numericIndex + 1 }}" class="display-order-input">
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        @foreach($vehicleType->models as $index => $model)
                                        @php
                                            $numericIndex = (int)$index;
                                        @endphp
                                        <tr data-index="{{ $numericIndex }}" data-id="{{ $model->id }}">
                                            <td class="text-center align-middle display-order">{{ $numericIndex + 1 }}</td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm model-name" 
                                                       name="models[{{ $numericIndex }}][model_name]" 
                                                       value="{{ $model->model_name }}"
                                                       maxlength="100" placeholder="例: ハイエース">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm model-code" 
                                                       name="models[{{ $numericIndex }}][model_code]" 
                                                       value="{{ $model->model_code ?? '' }}"
                                                       maxlength="50" placeholder="例: HIACE-001">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm maker" 
                                                       name="models[{{ $numericIndex }}][maker]" 
                                                       value="{{ $model->maker }}"
                                                       maxlength="50" placeholder="例: トヨタ">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm remark" 
                                                       name="models[{{ $numericIndex }}][remarks]" 
                                                       value="{{ $model->remarks }}" 
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
                                                <input type="hidden" name="models[{{ $numericIndex }}][id]" value="{{ $model->id }}">
                                                <input type="hidden" name="models[{{ $numericIndex }}][display_order]" value="{{ $numericIndex + 1 }}" class="display-order-input">
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                    
                                    <tr id="newRowTemplate" class="d-none">
                                        <td class="text-center align-middle display-order"></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm model-name" 
                                                   name="models[__index__][model_name]" 
                                                   maxlength="100" placeholder="例: ハイエース">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm model-code" 
                                                   name="models[__index__][model_code]" 
                                                   maxlength="50" placeholder="例: HIACE-001">
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
                            <i class="bi bi-check-circle"></i> 全ての変更を保存
                        </button>
                        <a href="{{ route('masters.vehicle-types.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> キャンセル
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-danger" 
                                onclick="if(confirm('本当にこの車両種類を削除しますか？\\n関連する車両モデルも全て削除されます。\\nこの操作は元に戻せません。')) { document.getElementById('deleteForm').submit(); }">
                            <i class="bi bi-trash"></i> 車両種類全体を削除
                        </button>
                    </div>
                </div>
            </form>
            
            <form id="deleteForm" action="{{ route('masters.vehicle-types.destroy', $vehicleType) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    function renumberRows() {
        const tbody = document.getElementById('modelsBody');
        const rows = Array.from(tbody.querySelectorAll('tr:not(#newRowTemplate)'));
        
        rows.forEach((row, index) => {
            const newIndex = index;
            const newOrder = index + 1;
            
            const orderCell = row.querySelector('.display-order');
            if (orderCell) {
                orderCell.textContent = newOrder;
            }
            
            const displayOrderInput = row.querySelector('.display-order-input');
            if (displayOrderInput) {
                displayOrderInput.value = newOrder;
            }
            
            const fields = row.querySelectorAll('input[name^="models["], textarea[name^="models["]');
            fields.forEach(field => {
                const name = field.getAttribute('name');
                const newName = name.replace(/models\[\d+\]/, `models[${newIndex}]`);
                field.setAttribute('name', newName);
            });
            
            row.setAttribute('data-index', newIndex);
            
            const upBtn = row.querySelector('.move-up-btn');
            const downBtn = row.querySelector('.move-down-btn');
            
            if (upBtn) {
                upBtn.disabled = index === 0;
            }
            if (downBtn) {
                downBtn.disabled = index === rows.length - 1;
            }
        });
    }

    function addModelRow(button) {
        const template = document.getElementById('newRowTemplate');
        const tbody = document.getElementById('modelsBody');
        
        if (!template || !tbody) return;
        
        const newRow = template.cloneNode(true);
        newRow.removeAttribute('id');
        newRow.classList.remove('d-none');
        
        const currentRows = tbody.querySelectorAll('tr:not(#newRowTemplate)');
        const newIndex = currentRows.length;
        
        let html = newRow.innerHTML;
        html = html.replace(/__index__/g, newIndex);
        newRow.innerHTML = html;
        
        const inputs = newRow.querySelectorAll('input:not([type=hidden]), textarea');
        inputs.forEach(input => {
            input.value = '';
        });
        
        const idInput = newRow.querySelector('input[name$="[id]"]');
        if (idInput) {
            idInput.value = '';
        }
        
        if (button) {
            const currentRow = button.closest('tr');
            if (currentRow) {
                tbody.insertBefore(newRow, currentRow.nextSibling);
            } else {
                tbody.appendChild(newRow);
            }
        } else {
            tbody.appendChild(newRow);
        }
        
        renumberRows();
    }

    function deleteRow(button) {
        const rows = document.querySelectorAll('#modelsBody tr:not(#newRowTemplate)');
        
        if (rows.length <= 1) {
            alert('少なくとも1行の車両モデルが必要です！');
            return;
        }
        
        if (confirm('このモデルを削除してもよろしいですか？')) {
            const row = button.closest('tr');
            if (row) {
                row.remove();
                renumberRows();
            }
        }
    }

    function moveRowUp(button) {
        const row = button.closest('tr');
        const prevRow = row.previousElementSibling;
        
        if (prevRow && !prevRow.id) {
            row.parentNode.insertBefore(row, prevRow);
            renumberRows();
        }
    }

    function moveRowDown(button) {
        const row = button.closest('tr');
        const nextRow = row.nextElementSibling;
        
        if (nextRow && !nextRow.id) {
            row.parentNode.insertBefore(nextRow, row);
            renumberRows();
        }
    }

    function validateAndPrepareForm() {
        renumberRows();
        
        const rows = document.querySelectorAll('#modelsBody tr:not(#newRowTemplate)');
        let hasEmptyRow = false;
        
        rows.forEach((row, index) => {
            const modelName = row.querySelector('.model-name');
            if (modelName && !modelName.value.trim()) {
                hasEmptyRow = true;
                modelName.classList.add('is-invalid');
            } else if (modelName) {
                modelName.classList.remove('is-invalid');
            }
        });
        
        if (hasEmptyRow) {
            alert('モデル名は必須です。');
            return false;
        }
        
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        renumberRows();
        
        const addBtn = document.getElementById('addModelRowBtn');
        if (addBtn) {
            addBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addModelRow();
            });
        }
        
        const table = document.getElementById('modelsTable');
        if (table) {
            table.addEventListener('click', function(e) {
                const target = e.target;
                
                if (target.closest('.delete-row-btn')) {
                    e.preventDefault();
                    deleteRow(target.closest('.delete-row-btn'));
                }
                else if (target.closest('.move-up-btn')) {
                    e.preventDefault();
                    moveRowUp(target.closest('.move-up-btn'));
                }
                else if (target.closest('.move-down-btn')) {
                    e.preventDefault();
                    moveRowDown(target.closest('.move-down-btn'));
                }
                else if (target.closest('.add-row-btn')) {
                    e.preventDefault();
                    addModelRow(target.closest('.add-row-btn'));
                }
            });
        }
        
        const form = document.getElementById('vehicleTypeForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validateAndPrepareForm()) {
                    e.preventDefault();
                    return false;
                }
                return true;
            });
        }
    });
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