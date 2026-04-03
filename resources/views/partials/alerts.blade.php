
{{-- 显示成功消息 --}}

@if(session('success'))

<div class="alert alert-success alert-dismissible fade show" role="alert">

    <i class="bi bi-check-circle"></i> {{ session('success') }}

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

</div>

@endif



{{-- 显示错误消息 --}}

@if(session('error'))

<div class="alert alert-danger alert-dismissible fade show" role="alert">

    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

</div>

@endif



{{-- 显示验证错误 --}}

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



{{-- 显示警告消息 --}}

@if(session('warning'))

<div class="alert alert-warning alert-dismissible fade show" role="alert">

    <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

</div>

@endif



{{-- 显示信息消息 --}}

@if(session('info'))

<div class="alert alert-info alert-dismissible fade show" role="alert">

    <i class="bi bi-info-circle"></i> {{ session('info') }}

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

</div>

@endif

