<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\SystemMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SystemMessageController extends Controller
{
    public function index()
    {
        $messages = SystemMessage::orderBy('is_pinned', 'desc')
            ->orderByRaw("CASE WHEN is_pinned = 1 THEN updated_at ELSE created_at END DESC")
            ->get();

        $staffId = session('staff_id');

        $result = [];
        foreach ($messages as $message) {
            $staff = DB::table('staffs')->where('id', $message->staff_id)->first();
            $images = $message->images ? json_decode($message->images, true) : [];

            $result[] = [
                'id' => $message->id,
                'staff_id' => $message->staff_id,
                'staff_name' => $staff->name ?? '不明',
                'content' => $message->content,
                'images' => $images,
                'is_pinned' => (bool)$message->is_pinned,
                'created_at' => $message->created_at ? $message->created_at->format('Y-m-d H:i') : '',
                'updated_at' => $message->updated_at ? $message->updated_at->format('Y-m-d H:i') : '',
                'is_owner' => $message->staff_id == $staffId,
            ];
        }

        return response()->json(['success' => true, 'messages' => $result]);
    }

    public function store(Request $request)
    {
        $staffId = session('staff_id');
        $staffName = session('staff_name');

        if (!$staffId) {
            return response()->json(['success' => false, 'message' => 'ログインが必要です。'], 401);
        }

        $role = session('role', '');
        if (!in_array($role, ['admin', 'administrator', 'manager'])) {
            return response()->json(['success' => false, 'message' => '権限がありません。'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('system_messages/' . now()->format('Y/m'), $filename, 'public');
                $imagePaths[] = $path;
            }
        }

        $message = SystemMessage::create([
            'staff_id' => $staffId,
            'content' => $request->content,
            'images' => !empty($imagePaths) ? json_encode($imagePaths) : null,
            'is_pinned' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => '投稿しました。',
            'id' => $message->id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $staffId = session('staff_id');

        if (!$staffId) {
            return response()->json(['success' => false, 'message' => 'ログインが必要です。'], 401);
        }

        $message = SystemMessage::findOrFail($id);

        if ($message->staff_id != $staffId) {
            return response()->json(['success' => false, 'message' => '権限がありません。'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'string',
        ]);

        $imagePaths = $message->images ? json_decode($message->images, true) : [];

        if ($request->has('deleted_images')) {
            foreach ($request->deleted_images as $deletedPath) {
                $key = array_search($deletedPath, $imagePaths);
                if ($key !== false) {
                    unset($imagePaths[$key]);
                    if (Storage::disk('public')->exists($deletedPath)) {
                        Storage::disk('public')->delete($deletedPath);
                    }
                }
            }
            $imagePaths = array_values($imagePaths);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('system_messages/' . now()->format('Y/m'), $filename, 'public');
                $imagePaths[] = $path;
            }
        }

        $message->update([
            'content' => $request->content,
            'images' => !empty($imagePaths) ? json_encode($imagePaths) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => '更新しました。',
        ]);
    }

    public function destroy($id)
    {
        $staffId = session('staff_id');

        if (!$staffId) {
            return response()->json(['success' => false, 'message' => 'ログインが必要です。'], 401);
        }

        $message = SystemMessage::findOrFail($id);

        if ($message->staff_id != $staffId) {
            return response()->json(['success' => false, 'message' => '権限がありません。'], 403);
        }

        $images = $message->images ? json_decode($message->images, true) : [];
        foreach ($images as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => '削除しました。',
        ]);
    }

    public function togglePin($id)
    {
        $staffId = session('staff_id');

        if (!$staffId) {
            return response()->json(['success' => false, 'message' => 'ログインが必要です。'], 401);
        }

        $message = SystemMessage::findOrFail($id);

        if ($message->staff_id != $staffId) {
            return response()->json(['success' => false, 'message' => '権限がありません。'], 403);
        }

        $message->update([
            'is_pinned' => !$message->is_pinned,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->is_pinned ? 'トップ固定しました。' : 'トップ固定を解除しました。',
            'is_pinned' => (bool)$message->is_pinned,
        ]);
    }
}