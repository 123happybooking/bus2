<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>場所施設 追加</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #f5f5f5;
            padding: 24px;
            font-size: 13px;
        }
        .form-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .form-item {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }
        .form-label {
            width: 50px;
            font-weight: 500;
            flex-shrink: 0;
            font-size: 13px;
        }
        .form-label.required::before {
            content: "* ";
            color: #dc3545;
        }
        .form-input {
            flex: 1;
            padding: 8px 12px;
            font-size: 13px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: #fff;
        }
        .form-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .form-textarea {
            flex: 1;
            padding: 8px 12px;
            font-size: 13px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: #fff;
            resize: vertical;
            font-family: inherit;
        }
        .form-textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .row-2cols {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }
        .row-2cols .col {
            flex: 1;
            display: flex;
            align-items: center;
        }
        .row-2cols .col .form-label {
            width: 50px;
        }
        .row-2cols .col .form-input {
            flex: 1;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 24px;
        }
        .btn {
            padding: 8px 24px;
            font-size: 13px;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            font-weight: 500;
        }
        .btn-primary {
            background-color: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
        }
        .btn-secondary:hover {
            background-color: #d1d5db;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div id="alert-container"></div>
        
        <form id="locationForm">
            @csrf
            
            <div class="form-item">
                <label class="form-label">場所施設名</label>
                <input type="text" class="form-input" id="name" name="name" placeholder="例: 東京タワー">
            </div>
            
            <div class="form-item">
                <label class="form-label">住所</label>
                <input type="text" class="form-input" id="address" name="address" placeholder="例: 東京都港区芝公園4-2-8">
            </div>
            
            <div class="form-item">
                <label class="form-label">Tel</label>
                <input type="tel" class="form-input" id="phone" name="phone" placeholder="例: 03-1234-5678">
            </div>
            
            <div class="row-2cols">
                <div class="col">
                    <label class="form-label">分類</label>
                    <input type="text" class="form-input" id="category" name="category" placeholder="例: 観光施設">
                </div>
                <div class="col">
                    <label class="form-label">地区</label>
                    <input type="text" class="form-input" id="area" name="area" placeholder="例: 東京">
                </div>
            </div>
            
            <div class="form-item">
                <label class="form-label">備考</label>
                <textarea class="form-textarea" id="remark" name="remark" rows="3" placeholder="備考があれば入力してください"></textarea>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary" id="submitBtn">保存</button>
                <button type="button" class="btn btn-secondary" onclick="window.parent.closeLocationModal()">キャンセル</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#locationForm').on('submit', function(e) {
                e.preventDefault();
                
                const name = $('#name').val().trim();
                if (!name) {
                    showAlert('施設名を入力してください。', 'danger');
                    $('#name').addClass('is-invalid');
                    return;
                }
                $('#name').removeClass('is-invalid');
                
                const $submitBtn = $('#submitBtn');
                const originalText = $submitBtn.html();
                $submitBtn.html('保存中...').prop('disabled', true);
                
                $('.is-invalid').removeClass('is-invalid');
                
                $.ajax({
                    url: '{{ route("masters.locations.store") }}',
                    method: 'POST',
                    data: $('#locationForm').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('保存しました。ページを更新すると追加データが表示されます。', 'success');
                            setTimeout(function() {
                                if (window.parent && window.parent.closeLocationModal) {
                                    window.parent.closeLocationModal();
                                }
                            }, 1000);
                        } else {
                            showAlert(response.message || '保存に失敗しました。', 'danger');
                            $submitBtn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = '保存に失敗しました。';
                        
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            for (const key in errors) {
                                $(`[name="${key}"]`).addClass('is-invalid');
                            }
                            errorMsg = '入力内容を確認してください。';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        
                        showAlert(errorMsg, 'danger');
                        $submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });
        });
        
        function showAlert(message, type) {
            const alertHtml = '<div class="alert alert-' + type + '">' + message + '</div>';
            $('#alert-container').html(alertHtml);
            setTimeout(function() {
                $('#alert-container .alert').fadeOut(function() {
                    $(this).remove();
                });
            }, 2000);
        }
    </script>
</body>
</html>