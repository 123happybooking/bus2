@extends('layouts.app')

@section('title', 'ダッシュボード')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="row">
        <div style="width: 500px;">
            <div class="message-panel">
                <div class="message-header">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-dots"></i> お知らせ
                    </h5>
                    @if($isAdmin)
                    <button type="button" id="addMessageBtn" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> 新規投稿
                    </button>
                    @endif
                </div>
                <div class="message-list" id="messageList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
        </div>
    </div>
</div>

<div id="messageModal" class="message-modal" style="display: none;">
    <div class="message-modal-overlay"></div>
    <div class="message-modal-content">
        <div class="message-modal-header">
            <h5 id="modalTitle">新規投稿</h5>
            <button type="button" id="closeModalBtn" class="modal-close-btn">×</button>
        </div>
        <div class="message-modal-body">
            <input type="hidden" id="editMessageId" value="">
            <div class="form-group">
                <label for="messageContent">メッセージ</label>
                <textarea id="messageContent" class="form-control" rows="4" placeholder="メッセージを入力してください..."></textarea>
            </div>
            <div class="form-group">
                <label>画像</label>
                <input type="file" id="messageImages" class="form-control" accept="image/jpeg,image/png,image/jpg,gif" multiple>
                <div class="image-preview" id="imagePreview"></div>
                <div class="existing-images" id="existingImages"></div>
            </div>
        </div>
        <div class="message-modal-footer">
            <button type="button" id="cancelModalBtn" class="btn btn-secondary btn-sm">キャンセル</button>
            <button type="button" id="submitMessageBtn" class="btn btn-primary btn-sm">投稿する</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.message-panel {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.message-header h5 {
    font-size: 15px;
    font-weight: 600;
    color: #374151;
}

.message-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px 12px;
}

.message-item {
    padding: 12px 14px;
    border-radius: 6px;
    margin-bottom: 8px;
    transition: background 0.2s;
    border: 1px solid transparent;
    position: relative;
}

.message-item:hover {
    background: #f3f4f6;
}

.message-item.pinned {
    background: #fffbeb;
    border-color: #fcd34d;
}

.message-item.pinned:hover {
    background: #fef3c7;
}

.message-item .pinned-badge {
    color: #f59e0b;
    font-size: 12px;
    font-weight: 600;
    margin-right: 6px;
}

.message-item .message-author {
    font-weight: 600;
    font-size: 13px;
    color: #1f2937;
}

.message-item .message-time {
    font-size: 11px;
    color: #9ca3af;
    margin-left: 8px;
}

.message-item .message-content {
    font-size: 13px;
    color: #374151;
    margin-top: 4px;
    word-wrap: break-word;
    white-space: pre-wrap;
}

.message-item .message-images {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
}

.message-item .message-images img {
    height: 60px;
    width: auto;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
    object-fit: cover;
}

.message-item .message-actions {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
    margin-top: 6px;
}

.message-item .message-actions button {
    padding: 2px 8px;
    font-size: 11px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background: transparent;
    color: #6b7280;
    transition: all 0.2s;
}

.message-item .message-actions button:hover {
    background: #e5e7eb;
}

.message-item .message-actions .edit-btn:hover {
    color: #2563eb;
}

.message-item .message-actions .delete-btn:hover {
    color: #dc2626;
}

.message-item .message-actions .pin-btn:hover {
    color: #f59e0b;
}

.message-item .message-actions .unpin-btn:hover {
    color: #6b7280;
}

.message-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.message-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
}

.message-modal-content {
    position: relative;
    background: #fff;
    border-radius: 12px;
    width: 520px;
    max-width: 95%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalIn 0.25s ease;
}

@keyframes modalIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.message-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.message-modal-header h5 {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.message-modal-header .modal-close-btn {
    background: none;
    border: none;
    font-size: 24px;
    color: #9ca3af;
    cursor: pointer;
    padding: 0 4px;
}

.message-modal-header .modal-close-btn:hover {
    color: #374151;
}

.message-modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.message-modal-body .form-group {
    margin-bottom: 14px;
}

.message-modal-body .form-group label {
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    display: block;
    margin-bottom: 4px;
}

.message-modal-body .form-group textarea {
    resize: vertical;
    font-size: 13px;
}

.message-modal-body .form-group input[type="file"] {
    font-size: 13px;
    padding: 6px 10px;
}

.message-modal-body .image-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.message-modal-body .image-preview .preview-item {
    position: relative;
    width: 80px;
    height: 60px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
}

.message-modal-body .image-preview .preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}

.message-modal-body .image-preview .preview-item .remove-image {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #dc2626;
    color: #fff;
    border: none;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.message-modal-body .existing-images {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.message-modal-body .existing-images .existing-item {
    position: relative;
    width: 80px;
    height: 60px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
}

.message-modal-body .existing-images .existing-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}

.message-modal-body .existing-images .existing-item .remove-existing {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #dc2626;
    color: #fff;
    border: none;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.message-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 12px 20px;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.message-empty {
    text-align: center;
    padding: 40px 0;
    color: #9ca3af;
}

.message-empty i {
    font-size: 36px;
    display: block;
    margin-bottom: 12px;
}

.message-empty p {
    font-size: 14px;
    margin: 0;
}
</style>
@endpush

@push('scripts')
<script>
let currentEditingId = null;
let pendingFiles = [];
let existingFiles = [];
let deletedExistingFiles = [];

function loadMessages() {
    const list = document.getElementById('messageList');
    list.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';

    fetch('/masters/system-messages')
        .then(response => response.json())
        .then(data => {
            if (!data.success || data.messages.length === 0) {
                list.innerHTML = `
                    <div class="message-empty">
                        <i class="bi bi-chat-dots"></i>
                        <p>メッセージはありません</p>
                    </div>
                `;
                return;
            }

            let html = '';
            data.messages.forEach(msg => {
                const isPinned = msg.is_pinned;
                const isOwner = msg.is_owner;
                const images = msg.images || [];

                let imagesHtml = '';
                if (images.length > 0) {
                    imagesHtml = '<div class="message-images">';
                    images.forEach(img => {
                        imagesHtml += `<img src="/storage/${img}" alt="画像">`;
                    });
                    imagesHtml += '</div>';
                }

                let actionsHtml = '';
                if (isOwner) {
                    actionsHtml = `
                        <div class="message-actions">
                            <button class="edit-btn" data-id="${msg.id}" data-content="${msg.content.replace(/"/g, '&quot;')}" data-images='${JSON.stringify(images)}'>編集</button>
                            <button class="delete-btn" data-id="${msg.id}">削除</button>
                            ${isPinned ? `<button class="unpin-btn" data-id="${msg.id}">固定解除</button>` : `<button class="pin-btn" data-id="${msg.id}">トップ固定</button>`}
                        </div>
                    `;
                }

                const pinnedBadge = isPinned ? '<span class="pinned-badge">📌</span>' : '';

                html += `
                    <div class="message-item ${isPinned ? 'pinned' : ''}">
                        <div>
                            ${pinnedBadge}
                            <span class="message-author">${msg.staff_name}</span>
                            <span class="message-time">${msg.created_at}</span>
                        </div>
                        <div class="message-content">${msg.content}</div>
                        ${imagesHtml}
                        ${actionsHtml}
                    </div>
                `;
            });

            list.innerHTML = html;
            bindEvents();
        })
        .catch(() => {
            list.innerHTML = `
                <div class="message-empty">
                    <i class="bi bi-exclamation-triangle"></i>
                    <p>読み込みに失敗しました</p>
                </div>
            `;
        });
}

function bindEvents() {
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            const content = this.dataset.content;
            const images = JSON.parse(this.dataset.images || '[]');
            openModal('edit', id, content, images);
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (!confirm('このメッセージを削除してもよろしいですか？')) return;
            deleteMessage(this.dataset.id);
        });
    });

    document.querySelectorAll('.pin-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            togglePin(this.dataset.id);
        });
    });

    document.querySelectorAll('.unpin-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            togglePin(this.dataset.id);
        });
    });
}

function openModal(mode, id = null, content = '', images = []) {
    const modal = document.getElementById('messageModal');
    const title = document.getElementById('modalTitle');
    const contentInput = document.getElementById('messageContent');
    const editIdInput = document.getElementById('editMessageId');
    const previewContainer = document.getElementById('imagePreview');
    const existingContainer = document.getElementById('existingImages');
    const submitBtn = document.getElementById('submitMessageBtn');

    currentEditingId = id;
    pendingFiles = [];
    existingFiles = images;
    deletedExistingFiles = [];

    if (mode === 'edit') {
        title.textContent = 'メッセージ編集';
        contentInput.value = content;
        editIdInput.value = id;
        submitBtn.textContent = '更新する';
    } else {
        title.textContent = '新規投稿';
        contentInput.value = '';
        editIdInput.value = '';
        submitBtn.textContent = '投稿する';
        existingFiles = [];
    }

    previewContainer.innerHTML = '';
    existingContainer.innerHTML = '';

    if (existingFiles.length > 0) {
        existingFiles.forEach((path, index) => {
            const div = document.createElement('div');
            div.className = 'existing-item';
            div.innerHTML = `
                <img src="/storage/${path}" alt="画像">
                <button class="remove-existing" data-index="${index}">×</button>
            `;
            div.querySelector('.remove-existing').addEventListener('click', function(e) {
                e.stopPropagation();
                const idx = parseInt(this.dataset.index);
                const fullPath = existingFiles[idx];
                if (fullPath) {
                    deletedExistingFiles.push(fullPath);
                }
                existingFiles.splice(idx, 1);
                this.closest('.existing-item').remove();
            });
            existingContainer.appendChild(div);
        });
    }

    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('messageModal').style.display = 'none';
    document.getElementById('messageContent').value = '';
    document.getElementById('editMessageId').value = '';
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('existingImages').innerHTML = '';
    pendingFiles = [];
    existingFiles = [];
    deletedExistingFiles = [];
    currentEditingId = null;
}

function submitMessage() {
    const content = document.getElementById('messageContent').value.trim();
    if (!content) {
        alert('メッセージを入力してください。');
        return;
    }

    const isEdit = currentEditingId !== null;
    const url = isEdit ? `/masters/system-messages/${currentEditingId}` : '/masters/system-messages';
    const method = isEdit ? 'POST' : 'POST';

    const formData = new FormData();
    formData.append('content', content);
    formData.append('_method', isEdit ? 'PUT' : 'POST');

    pendingFiles.forEach(file => {
        formData.append('images[]', file);
    });

    if (isEdit && deletedExistingFiles.length > 0) {
        deletedExistingFiles.forEach(path => {
            formData.append('deleted_images[]', path);
        });
    }

    const btn = document.getElementById('submitMessageBtn');
    const originalText = btn.textContent;
    btn.textContent = '送信中...';
    btn.disabled = true;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            loadMessages();
        } else {
            alert(data.message || 'エラーが発生しました。');
        }
    })
    .catch(() => {
        alert('エラーが発生しました。');
    })
    .finally(() => {
        btn.textContent = originalText;
        btn.disabled = false;
    });
}

function deleteMessage(id) {
    fetch(`/masters/system-messages/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadMessages();
        } else {
            alert(data.message || '削除に失敗しました。');
        }
    })
    .catch(() => {
        alert('エラーが発生しました。');
    });
}

function togglePin(id) {
    fetch(`/masters/system-messages/${id}/toggle-pin`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadMessages();
        } else {
            alert(data.message || '操作に失敗しました。');
        }
    })
    .catch(() => {
        alert('エラーが発生しました。');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadMessages();

    document.getElementById('addMessageBtn')?.addEventListener('click', function() {
        openModal('create');
    });

    document.getElementById('closeModalBtn')?.addEventListener('click', closeModal);
    document.getElementById('cancelModalBtn')?.addEventListener('click', closeModal);
    document.getElementById('submitMessageBtn')?.addEventListener('click', submitMessage);

    document.getElementById('messageModal')?.addEventListener('click', function(e) {
        if (e.target === this || e.target.classList.contains('message-modal-overlay')) {
            closeModal();
        }
    });

    document.getElementById('messageImages')?.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const previewContainer = document.getElementById('imagePreview');

        files.forEach(file => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`ファイル ${file.name} は5MB以下にしてください。`);
                return;
            }
            pendingFiles.push(file);
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${event.target.result}" alt="プレビュー">
                    <button class="remove-image" data-filename="${file.name}">×</button>
                `;
                div.querySelector('.remove-image').addEventListener('click', function() {
                    const filename = this.dataset.filename;
                    pendingFiles = pendingFiles.filter(f => f.name !== filename);
                    this.closest('.preview-item').remove();
                });
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
        e.target.value = '';
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
});
</script>
@endpush