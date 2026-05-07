@extends('admin.layouts.app')

@section('title', 'ダッシュボード')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">ダッシュボード</h2>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">総ユーザー数</h6>
                            <h2 class="mb-0">{{ $totalUsers }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">今日の新規ユーザー</h6>
                            <h2 class="mb-0">{{ $newUsersToday }}</h2>
                        </div>
                        <i class="fas fa-user-plus fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection