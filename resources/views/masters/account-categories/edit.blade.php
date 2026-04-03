@extends('layouts.app')

@section('title', '勘定科目区分編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account-categories.index') }}">勘定科目区分マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">編集</li>
                </ol>
            </nav>
            
            <!-- 成功消息 -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- 错误消息 (Session) -->
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- 验证错误列表 -->
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
            
            <!-- 卡片表单 -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> 勘定科目区分編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.account-categories.update', $category) }}" method="POST" id="categoryForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- 第一行：名称 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required">区分名称</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name', $category->name) }}" 
                                       required maxlength="50" placeholder="">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">类别</label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type">
                                    <option value="">選択してください</option>
                                    <option value="PL" {{ old('type', $category->type) == 'PL' ? 'selected' : '' }}>PL</option>
                                    <option value="BS" {{ old('type', $category->type) == 'BS' ? 'selected' : '' }}>BS</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>



                        <!-- 第二行：借贷区分 与 层级 -->
                        <div class="row">
                            <!-- 借贷区分 (Radio Group) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">貸借区分</label><span class="text-danger">*</span>
                                <div class="d-flex gap-4 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input @error('mark') is-invalid @enderror" 
                                               type="radio" name="mark" id="mark_debit" value="借" 
                                               {{ old('mark', $category->mark) === '借' ? 'checked' : '' }} required>
                                        <label class="form-check-label fw-bold text-primary" for="mark_debit">
                                            借
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('mark') is-invalid @enderror" 
                                               type="radio" name="mark" id="mark_credit" value="貸" 
                                               {{ old('mark', $category->mark) === '貸' ? 'checked' : '' }} required>
                                        <label class="form-check-label fw-bold text-danger" for="mark_credit">
                                            貸
                                        </label>
                                    </div>
                                </div>
                                @error('mark')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 层级 (Select) -->
                            <div class="col-md-6 mb-3">
                                <label for="level" class="form-label">レベル (階層)</label>
                                <select class="form-select @error('level') is-invalid @enderror" 
                                        id="level" name="level">
                                    <option value="0" >選択してください</option>
                                    <option value="1" {{ old('level', $category->level) == '1' ? 'selected' : '' }}>五大要素</option>
                                    <option value="2" {{ old('level', $category->level) == '2' ? 'selected' : '' }}>大分類</option>
                                    <option value="3" {{ old('level', $category->level) == '3' ? 'selected' : '' }}>中分類</option>
                                </select>
                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <!-- 按钮区域 -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.account-categories.index') }}" class="btn btn-secondary ms-2">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="{{ route('masters.account-categories.show', $category) }}" class="btn btn-info text-white">
                                    <i class="bi bi-eye"></i> 詳細を見る
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 可选：简单的交互优化，例如选择层级时显示更详细的提示
    const levelSelect = document.getElementById('level');
    const helpText = levelSelect.nextElementSibling;

    // 初始化提示文本颜色
    if (levelSelect.value === '1') {
        helpText.classList.add('text-primary');
    } else if (levelSelect.value === '2') {
        helpText.classList.add('text-info');
    }

    levelSelect.addEventListener('change', function() {
        if (this.value === '1') {
            helpText.textContent = "レベル 1: 貸借対照表 (B/S) の大分類 (資産、負債、純資産など) に使用されます。";
            helpText.className = "form-text text-primary";
        } else if (this.value === '2') {
            helpText.textContent = "レベル 2: 損益計算書 (P/L) の分類 (収益、費用) や、詳細な資産分類に使用されます。";
            helpText.className = "form-text text-info";
        } else {
            helpText.textContent = "レベル (階層) を選択してください。";
            helpText.className = "form-text";
        }
    });
});
</script>
@endpush