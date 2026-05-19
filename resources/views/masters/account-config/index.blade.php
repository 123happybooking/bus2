@extends('layouts.app')

@section('title', '基本配置')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h4>基本配置</h4>
    </div>

    <div class="row">
        <div class="col-md-12">
            <!-- 消息提示 -->
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
            
            <div class="card shadow-sm card-edit">
                <div class="card-body">
                    <!-- 表单开始 -->
                    <form action="{{ route('masters.account-config.update', $config->id) }}" method="POST" enctype="multipart/form-data" id="configForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            @php
                                // 1. 预先查找当前配置对应的科目对象，方便后面直接显示
                                // 使用 optional() 防止因数据为空报错
                                $currentCashAccount = optional($accounts->find($config->account_cash_id));
                                $currentDepositAccount = optional($accounts->find($config->account_deposit_id));
                                
                                // 2. 格式化显示文本 (代码 - 名称)
                                $cashDisplayValue = $currentCashAccount ? $currentCashAccount->code . ' - ' . $currentCashAccount->name : '';
                                $depositDisplayValue = $currentDepositAccount ? $currentDepositAccount->code . ' - ' . $currentDepositAccount->name : '';
                            @endphp

                            <div class="col-md-4">
                                <label for="search-account-input" class="form-label">現金</label>
                                <!-- 防止自动填充的隐藏项 -->
                                <input type="text" style="display: none;" tabindex="-1">
                                
                                <!-- 修改点：在这里添加 value 属性 -->
                                <input type="text" 
                                    id="search-account-input" 
                                    class="form-control form-control-sm form-control-datalist" 
                                    list="account-list-search" 
                                    placeholder="科目コードまたは名前を入力" 
                                    autocomplete="off"
                                    data-target="account_cash_id"
                                    value="{{ $cashDisplayValue }}"> <!-- 回显显示文本 -->
                                
                                <!-- 隐藏域：实际提交的 ID -->
                                <input type="hidden" id="account_cash_id" name="account_cash_id" value="{{ $config->account_cash_id }}">

                                <datalist id="account-list-search">
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->code }} - {{ $account->name }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <!-- 预金部分的修改逻辑是一样的 -->
                            <div class="col-md-4">
                                <label for="search-account-input2" class="form-label">預金</label>
                                <input type="text" style="display: none;" tabindex="-1">
                                
                                <input type="text" 
                                    id="search-account-input2" 
                                    class="form-control form-control-sm form-control-datalist" 
                                    list="account-list-search2" 
                                    placeholder="科目コードまたは名前を入力" 
                                    autocomplete="off"
                                    data-target="account_deposit_id"
                                    value="{{ $depositDisplayValue }}"> <!-- 回显显示文本 -->
                                
                                <input type="hidden" id="account_deposit_id" name="account_deposit_id" value="{{ $config->account_deposit_id }}">

                                <datalist id="account-list-search2">
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->code }} - {{ $account->name }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div class="row">
                            @php
                                // 1. 预先查找当前配置对应的科目对象，方便后面直接显示
                                // 使用 optional() 防止因数据为空报错
                                $currentMgjAccount = optional($accounts->find($config->account_mgj_id));
                                $currentSpmsgAccount = optional($accounts->find($config->account_spmsg_id));
                                
                                // 2. 格式化显示文本 (代码 - 名称)
                                $maiDisplayValue = $currentMgjAccount ? $currentMgjAccount->code . ' - ' . $currentMgjAccount->name : '';
                                $SpmsgDisplayValue = $currentSpmsgAccount ? $currentSpmsgAccount->code . ' - ' . $currentSpmsgAccount->name : '';
                            @endphp

                            <div class="col-md-4">
                                <label for="search-account-input" class="form-label">売掛金</label>
                                <!-- 防止自动填充的隐藏项 -->
                                <input type="text" style="display: none;" tabindex="-1">
                                
                                <!-- 修改点：在这里添加 value 属性 -->
                                <input type="text" 
                                    id="search-account-input" 
                                    class="form-control form-control-sm form-control-datalist" 
                                    list="account-list-search" 
                                    placeholder="科目コードまたは名前を入力" 
                                    autocomplete="off"
                                    data-target="account_mgj_id"
                                    value="{{ $maiDisplayValue }}"> <!-- 回显显示文本 -->
                                
                                <!-- 隐藏域：实际提交的 ID -->
                                <input type="hidden" id="account_mgj_id" name="account_mgj_id" value="{{ $config->account_mgj_id }}">

                                <datalist id="account-list-search">
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->code }} - {{ $account->name }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <!-- 预金部分的修改逻辑是一样的 -->
                            <div class="col-md-4">
                                <label for="search-account-input2" class="form-label">商品売上高</label>
                                <input type="text" style="display: none;" tabindex="-1">
                                
                                <input type="text" 
                                    id="search-account-input2" 
                                    class="form-control form-control-sm form-control-datalist" 
                                    list="account-list-search2" 
                                    placeholder="科目コードまたは名前を入力" 
                                    autocomplete="off"
                                    data-target="account_spmsg_id"
                                    value="{{ $SpmsgDisplayValue }}"> <!-- 回显显示文本 -->
                                
                                <input type="hidden" id="account_spmsg_id" name="account_spmsg_id" value="{{ $config->account_spmsg_id }}">

                                <datalist id="account-list-search2">
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->code }} - {{ $account->name }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}
.card.border-danger {
    border-width: 2px;
}
.bg-light {
    background-color: #e9ecef !important;
}
</style>
@endpush

@push('scripts')
<script>
    // 1. 将后端的 Accounts 集合转换为 JS 可操作的数组对象
    // 这样 JS 可以通过 "代码 - 名称" 找到对应的 ID
    const accountsData = @json($accounts);

    // 2. 定义一个函数来查找 ID
    function findAccountId(displayValue) {
        // displayValue 格式为 "Code - Name"
        // 遍历 accountsData 寻找匹配项
        const account = accountsData.find(acc => {
            return (acc.code + ' - ' + acc.name) === displayValue;
        });
        
        return account ? account.id : null;
    }

    // 3. 绑定事件监听器
    document.addEventListener('DOMContentLoaded', function() {
        // 获取所有带有 datalist 功能的输入框
        const inputs = document.querySelectorAll('.form-control-datalist');

        inputs.forEach(input => {
            input.addEventListener('change', function() {
                const selectedValue = this.value; // 获取用户选择的 "Code - Name"
                const targetId = this.getAttribute('data-target'); // 获取对应的隐藏域 ID
                const hiddenInput = document.getElementById(targetId);

                if (hiddenInput) {
                    const accountId = findAccountId(selectedValue);
                    hiddenInput.value = accountId; // 将找到的 ID 赋值给隐藏域
                    
                    // 调试用：可以在控制台查看是否获取成功
                    console.log(`Selected: ${selectedValue}, ID: ${accountId}`);
                }
            });
        });
        
    });
</script>
@endpush