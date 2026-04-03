<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\GroupInfoDateRemark;
use Illuminate\Http\Request;

class GroupInfoDateRemarkController extends Controller
{
    private function canEdit()
    {
        $role = session('role');
        return $role === 'admin' || $role === 'manager' || $role === 'operations_manager';
    }

    public function show($date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(404);
        }
    
        $dateRemark = GroupInfoDateRemark::getByDate($date);
        
        $remark = $dateRemark ? $dateRemark->remark : '';
        $stopOrder = $dateRemark && $dateRemark->stop_order ? '1' : '0';
        $canEdit = $this->canEdit();
    
        return view('masters.operation-ledger.date-remark', compact('date', 'remark', 'stopOrder', 'canEdit'));
    }

    public function store(Request $request)
    {
        try {
            if (!$this->canEdit()) {
                return response()->json([
                    'success' => false, 
                    'message' => '編集権限がありません。'
                ], 403);
            }

            $validated = $request->validate([
                'date' => 'required|date|date_format:Y-m-d',
                'remark' => 'nullable|string|max:500',
                'stop_order' => 'nullable|boolean',
            ]);

            $userId = session('user_id', auth()->id() ?? 0);
            $stopOrder = filter_var($validated['stop_order'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $isEmptyRemark = empty($validated['remark']);
            $isNoStopOrder = !$stopOrder;
            
            if ($isEmptyRemark && $isNoStopOrder) {
                GroupInfoDateRemark::deleteByDate($validated['date']);
                return response()->json([
                    'success' => true,
                    'message' => '予定を削除しました。'
                ]);
            }

            GroupInfoDateRemark::updateOrCreateRemark(
                $validated['date'],
                $validated['remark'] ?? null,
                $stopOrder,
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => '保存しました。'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '入力内容を確認してください。',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '保存に失敗しました'], 500);
        }
    }

    public function destroy($date)
    {
        try {
            if (!$this->canEdit()) {
                return response()->json([
                    'success' => false, 
                    'message' => '編集権限がありません。'
                ], 403);
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return response()->json(['success' => false, 'message' => '日付形式エラー'], 400);
            }

            GroupInfoDateRemark::deleteByDate($date);

            return response()->json([
                'success' => true,
                'message' => '削除しました。'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '削除に失敗しました'], 500);
        }
    }
}