<?php
namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverExpense;
use App\Models\Driver\DriverExpenseType;
use App\Models\Driver\DriverPaymentMethod;
use App\Models\Driver\DriverExpensesReceipt;
use App\Models\Masters\DailyItinerary;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DriverExpenseController extends Controller
{
    public function index($itineraryId)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return redirect()->route('driver.dashboard');
        }
        
        $itinerary = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $driverId)
            ->findOrFail($itineraryId);
        
        $expenses = DriverExpense::with(['expenseType', 'paymentMethod'])
            ->where('itinerary_id', $itineraryId)
            ->where('driver_id', $driverId)
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $expenseTypes = DriverExpenseType::orderBy('id')->get();
        $paymentMethods = DriverPaymentMethod::orderBy('id')->get();
        
        $formattedDate = Carbon::parse($itinerary->date)->format('m月d日');
        
        return view('driver.advance-payment', compact(
            'itinerary',
            'expenses',
            'expenseTypes',
            'paymentMethods',
            'formattedDate'
        ));
    }
    
    public function store(Request $request)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return response()->json(['success' => false, 'message' => '認証エラー'], 401);
        }
        
        $request->validate([
            'itinerary_id' => 'required|exists:daily_itinerary,id',
            'bus_assignment_id' => 'nullable|exists:bus_assignment,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'type_id' => 'required|exists:driver_expense_types,id',
            'payment_method_id' => 'required|exists:driver_payment_methods,id',
            'agency_flag' => 'nullable|boolean',
            'remark' => 'nullable|string|max:500',
            'receipts' => 'nullable|array',
            'receipts.*' => 'image|mimes:jpeg,png,jpg|max:5120'
        ]);
        
        $expense = DriverExpense::create([
            'bus_assignment_id' => $request->bus_assignment_id,
            'itinerary_id' => $request->itinerary_id,
            'driver_id' => $driverId,
            'expense_date' => $request->expense_date,
            'amount' => $request->amount,
            'type_id' => $request->type_id,
            'payment_method_id' => $request->payment_method_id,
            'agency_flag' => $request->input('agency_flag', 0),
            'remark' => $request->remark,
        ]);
        
        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('receipts/' . now()->format('Y/m'), $filename, 'public');
                
                DriverExpensesReceipt::create([
                    'bus_assignment_id' => $expense->bus_assignment_id,
                    'itinerary_id' => $expense->itinerary_id,
                    'expense_id' => $expense->id,
                    'driver_id' => $driverId,
                    'expense_date' => $expense->expense_date,
                    'image_path' => $path,
                ]);
            }
        }
        
        $expense->load(['expenseType', 'paymentMethod']);
        
        return response()->json([
            'success' => true,
            'message' => '登録しました',
            'expense_id' => $expense->id,
            'expense' => [
                'id' => $expense->id,
                'expense_date' => Carbon::parse($expense->expense_date)->format('Y/m/d'),
                'amount' => number_format($expense->amount),
                'type_name' => $expense->expenseType->type_name ?? '',
                'payment_method_name' => $expense->paymentMethod->method_name ?? '',
                'agency_flag' => $expense->agency_flag,
                'remark' => $expense->remark,
            ]
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return response()->json(['success' => false, 'message' => '認証エラー'], 401);
        }
        
        $expense = DriverExpense::where('id', $id)
            ->where('driver_id', $driverId)
            ->firstOrFail();
        
        $request->validate([
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'type_id' => 'required|exists:driver_expense_types,id',
            'payment_method_id' => 'required|exists:driver_payment_methods,id',
            'agency_flag' => 'nullable|boolean',
            'remark' => 'nullable|string|max:500',
            'receipts' => 'nullable|array',
            'receipts.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'deleted_receipt_ids' => 'nullable|array',
            'deleted_receipt_ids.*' => 'integer|exists:driver_expenses_receipt,id'
        ]);
        
        $expense->update([
            'expense_date' => $request->expense_date,
            'amount' => $request->amount,
            'type_id' => $request->type_id,
            'payment_method_id' => $request->payment_method_id,
            'agency_flag' => $request->input('agency_flag', 0),
            'remark' => $request->remark,
        ]);
        
        if ($request->has('deleted_receipt_ids')) {
            foreach ($request->deleted_receipt_ids as $receiptId) {
                $receipt = DriverExpensesReceipt::where('id', $receiptId)
                    ->where('driver_id', $driverId)
                    ->first();
                if ($receipt) {
                    if (Storage::disk('public')->exists($receipt->image_path)) {
                        Storage::disk('public')->delete($receipt->image_path);
                    }
                    $receipt->delete();
                }
            }
        }
        
        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('receipts/' . now()->format('Y/m'), $filename, 'public');
                
                DriverExpensesReceipt::create([
                    'bus_assignment_id' => $expense->bus_assignment_id,
                    'itinerary_id' => $expense->itinerary_id,
                    'expense_id' => $expense->id,
                    'driver_id' => $driverId,
                    'expense_date' => $expense->expense_date,
                    'image_path' => $path,
                ]);
            }
        }
        
        $expense->load(['expenseType', 'paymentMethod']);
        
        return response()->json([
            'success' => true,
            'message' => '更新しました',
            'expense' => [
                'id' => $expense->id,
                'expense_date' => Carbon::parse($expense->expense_date)->format('Y/m/d'),
                'amount' => number_format($expense->amount),
                'type_name' => $expense->expenseType->type_name ?? '',
                'payment_method_name' => $expense->paymentMethod->method_name ?? '',
                'agency_flag' => $expense->agency_flag,
                'remark' => $expense->remark,
            ]
        ]);
    }
    
    public function destroy($id)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return response()->json(['success' => false, 'message' => '認証エラー'], 401);
        }
        
        $expense = DriverExpense::where('id', $id)
            ->where('driver_id', $driverId)
            ->firstOrFail();
        
        $receipts = DriverExpensesReceipt::where('expense_id', $id)
            ->where('driver_id', $driverId)
            ->get();
        
        foreach ($receipts as $receipt) {
            if (Storage::disk('public')->exists($receipt->image_path)) {
                Storage::disk('public')->delete($receipt->image_path);
            }
            $receipt->delete();
        }
        
        $expense->delete();
        
        return response()->json([
            'success' => true,
            'message' => '削除しました'
        ]);
    }
    
    public function getExpensesData($itineraryId)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return response()->json(['success' => false, 'message' => '認証エラー'], 401);
        }
        
        $expenses = DriverExpense::with(['expenseType', 'paymentMethod'])
            ->where('itinerary_id', $itineraryId)
            ->where('driver_id', $driverId)
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $formattedExpenses = [];
        foreach ($expenses as $expense) {
            $formattedExpenses[] = [
                'id' => $expense->id,
                'expense_date' => Carbon::parse($expense->expense_date)->format('Y/m/d'),
                'expense_date_raw' => Carbon::parse($expense->expense_date)->format('Y-m-d'),
                'amount' => number_format($expense->amount),
                'amount_raw' => $expense->amount,
                'type_id' => $expense->type_id,
                'type_name' => $expense->expenseType->type_name ?? '',
                'payment_method_id' => $expense->payment_method_id,
                'payment_method_name' => $expense->paymentMethod->method_name ?? '',
                'agency_flag' => (bool)$expense->agency_flag,
                'remark' => $expense->remark,
            ];
        }
        
        return response()->json([
            'success' => true,
            'expenses' => $formattedExpenses
        ]);
    }
    
    public function getReceiptsByExpense(Request $request)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return response()->json(['success' => false, 'message' => '認証エラー'], 401);
        }
        
        $expenseId = $request->query('expense_id');
        
        if (!$expenseId) {
            return response()->json(['success' => false, 'message' => '経費IDが指定されていません'], 400);
        }
        
        $receipts = DriverExpensesReceipt::where('driver_id', $driverId)
            ->where('expense_id', $expenseId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $formattedReceipts = [];
        foreach ($receipts as $receipt) {
            $formattedReceipts[] = [
                'id' => $receipt->id,
                'url' => asset('storage/' . $receipt->image_path),
            ];
        }
        
        return response()->json([
            'success' => true,
            'receipts' => $formattedReceipts,
        ]);
    }
    
    public function uploadReceipt(Request $request)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return response()->json(['success' => false, 'message' => '認証エラー'], 401);
        }
        
        $request->validate([
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'expense_id' => 'required|exists:driver_expenses,id',
        ]);
        
        $expense = DriverExpense::where('id', $request->expense_id)
            ->where('driver_id', $driverId)
            ->firstOrFail();
        
        $file = $request->file('receipt_image');
        $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('receipts/' . now()->format('Y/m'), $filename, 'public');
        
        $receipt = DriverExpensesReceipt::create([
            'bus_assignment_id' => $expense->bus_assignment_id,
            'itinerary_id' => $expense->itinerary_id,
            'expense_id' => $expense->id,
            'driver_id' => $driverId,
            'expense_date' => $expense->expense_date,
            'image_path' => $path,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '領収書をアップロードしました',
            'receipt' => [
                'id' => $receipt->id,
                'url' => asset('storage/' . $path),
            ],
        ]);
    }
    
    public function deleteReceipt($id)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return response()->json(['success' => false, 'message' => '認証エラー'], 401);
        }
        
        $receipt = DriverExpensesReceipt::where('id', $id)
            ->where('driver_id', $driverId)
            ->firstOrFail();
        
        if (Storage::disk('public')->exists($receipt->image_path)) {
            Storage::disk('public')->delete($receipt->image_path);
        }
        
        $receipt->delete();
        
        return response()->json([
            'success' => true,
            'message' => '削除しました',
        ]);
    }
}