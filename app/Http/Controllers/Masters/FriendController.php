<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('friends');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $friendCompanyIds = $this->searchFriendCompanyIds($search);
            if (!empty($friendCompanyIds)) {
                $query->whereIn('friend_company_id', $friendCompanyIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        $perPage = $request->input('per_page', 20);
        $friends = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function($friend) {
                $friendInfo = $this->getCompanyInfo($friend->friend_company_id);
                
                return (object)[
                    'id' => $friend->id,
                    'friend_company_id' => $friend->friend_company_id,
                    'friend_company_name' => $friendInfo['name'] ?? '',
                    'status' => $friend->status,
                    'is_sender' => $friend->is_sender,
                    'created_at' => $friend->created_at,
                ];
            });
        
        if ($request->has('search')) {
            $friends->appends(['search' => $request->search]);
        }
        if ($request->has('status')) {
            $friends->appends(['status' => $request->status]);
        }
        
        return view('masters.friends.index', compact('friends'));
    }
    
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 1) {
            return response()->json([]);
        }
        
        $userCompanyId = session('company_id');
        
        $companies = User::on('mysql')
            ->where('id', '!=', $userCompanyId)
            ->where('is_active', 1)
            ->where(function($q) use ($query) {
                $q->where('user_company_name', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->user_company_name ?: $company->name,
                ];
            });
        
        $friendCompanyIds = DB::table('friends')
            ->pluck('friend_company_id')
            ->toArray();
        
        $results = $companies->filter(function($company) use ($friendCompanyIds, $userCompanyId) {
            return !in_array($company['id'], $friendCompanyIds);
        })->values();
        
        return response()->json($results);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'friend_company_id' => 'required|integer',
        ]);
        
        $friendCompanyId = $request->friend_company_id;
        
        $companyExists = User::on('mysql')->where('id', $friendCompanyId)->exists();
        
        if (!$companyExists) {
            return redirect()->route('masters.friends.index')
                ->with('error', '指定された会社は存在しません。');
        }
        
        $userCompanyId = session('company_id');
        
        if ($userCompanyId == $friendCompanyId) {
            return redirect()->route('masters.friends.index')
                ->with('error', '自社を友達に追加することはできません。');
        }
        
        $existing = DB::table('friends')
            ->where('friend_company_id', $friendCompanyId)
            ->first();
        
        if ($existing) {
            if ($existing->status == 'accepted') {
                return redirect()->route('masters.friends.index')
                    ->with('error', '既に友達登録されています。');
            }
            
            DB::table('friends')
                ->where('id', $existing->id)
                ->update([
                    'status' => 'pending',
                    'is_sender' => 1,
                    'updated_at' => now(),
                ]);
            
            return redirect()->route('masters.friends.index')
                ->with('success', '友達申請を再送信しました。');
        }
        
        try {
            DB::beginTransaction();
            
            DB::table('friends')->insert([
                'friend_company_id' => $friendCompanyId,
                'status' => 'pending',
                'is_sender' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->insertIntoFriendDatabase($friendCompanyId, $userCompanyId, 0);
            
            DB::commit();
            
            return redirect()->route('masters.friends.index')
                ->with('success', '友達申請を送信しました。');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('masters.friends.index')
                ->with('error', '友達申請の送信に失敗しました。');
        }
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);
        
        $friend = DB::table('friends')->where('id', $id)->first();
        
        if (!$friend) {
            return redirect()->route('masters.friends.index')
                ->with('error', '友達情報が見つかりません。');
        }
        
        if ($friend->is_sender == 1) {
            return redirect()->route('masters.friends.index')
                ->with('error', '操作権限がありません。');
        }
        
        if ($friend->status != 'pending') {
            return redirect()->route('masters.friends.index')
                ->with('error', 'この申請は既に処理されています。');
        }
        
        $friendCompanyId = $friend->friend_company_id;
        
        try {
            DB::beginTransaction();
            
            DB::table('friends')
                ->where('id', $id)
                ->update([
                    'status' => $request->status,
                    'updated_at' => now(),
                ]);
            
            $this->updateFriendStatus($friendCompanyId, $request->status);
            
            DB::commit();
            
            $message = $request->status == 'accepted' ? '友達申請を承認しました。' : '友達申請を拒否しました。';
            
            return redirect()->route('masters.friends.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('masters.friends.index')
                ->with('error', '操作に失敗しました。');
        }
    }
    
    public function cancel($id)
    {
        $friend = DB::table('friends')->where('id', $id)->first();
        
        if (!$friend) {
            return redirect()->route('masters.friends.index')
                ->with('error', '友達情報が見つかりません。');
        }
        
        if ($friend->is_sender != 1) {
            return redirect()->route('masters.friends.index')
                ->with('error', '操作権限がありません。');
        }
        
        if ($friend->status != 'pending') {
            return redirect()->route('masters.friends.index')
                ->with('error', 'この申請は既に処理されています。');
        }
        
        $friendCompanyId = $friend->friend_company_id;
        
        try {
            DB::beginTransaction();
            
            DB::table('friends')->where('id', $id)->delete();
            
            $this->deleteFriendRecord($friendCompanyId);
            
            DB::commit();
            
            return redirect()->route('masters.friends.index')
                ->with('success', '友達申請を取り消しました。');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('masters.friends.index')
                ->with('error', '取消に失敗しました。');
        }
    }
    
    public function destroy($id)
    {
        $friend = DB::table('friends')->where('id', $id)->first();
        
        if (!$friend) {
            return redirect()->route('masters.friends.index')
                ->with('error', '友達情報が見つかりません。');
        }
        
        $friendCompanyId = $friend->friend_company_id;
        $currentCompanyId = session('company_id');
        
        try {
            DB::beginTransaction();
            
            $this->removeShareToCompany($currentCompanyId, $friendCompanyId);
            
            DB::table('friends')->where('id', $id)->delete();
            
            DB::commit();
            
            return redirect()->route('masters.friends.index')
                ->with('success', '友達を削除しました。');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('masters.friends.index')
                ->with('error', '削除に失敗しました。');
        }
    }
    
    private function switchToCompanyDatabase($companyId)
    {
        $databaseName = 'bus_user_' . $companyId;
        
        config(['database.connections.user_db' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]]);
        
        DB::purge('user_db');
        DB::connection('user_db')->reconnect();
    }
    
    private function cleanupCompanyDatabase()
    {
        DB::purge('user_db');
    }
    
    private function insertIntoFriendDatabase($companyId, $friendCompanyId, $isSender)
    {
        try {
            $this->switchToCompanyDatabase($companyId);
            
            $existing = DB::connection('user_db')->table('friends')
                ->where('friend_company_id', $friendCompanyId)
                ->first();
            
            if ($existing) {
                if ($existing->status != 'accepted') {
                    DB::connection('user_db')->table('friends')
                        ->where('id', $existing->id)
                        ->update([
                            'status' => 'pending',
                            'is_sender' => $isSender,
                            'updated_at' => now(),
                        ]);
                }
            } else {
                DB::connection('user_db')->table('friends')->insert([
                    'friend_company_id' => $friendCompanyId,
                    'status' => 'pending',
                    'is_sender' => $isSender,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            $this->cleanupCompanyDatabase();
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    private function updateFriendStatus($companyId, $status)
    {
        $currentCompanyId = session('company_id');
        
        try {
            $this->switchToCompanyDatabase($companyId);
            
            DB::connection('user_db')->table('friends')
                ->where('friend_company_id', $currentCompanyId)
                ->where('status', 'pending')
                ->where('is_sender', 1)
                ->update([
                    'status' => $status,
                    'updated_at' => now(),
                ]);
            
            $this->cleanupCompanyDatabase();
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    private function deleteFriendRecord($companyId)
    {
        $currentCompanyId = session('company_id');
        
        try {
            $this->switchToCompanyDatabase($companyId);
            
            DB::connection('user_db')->table('friends')
                ->where('friend_company_id', $currentCompanyId)
                ->delete();
            
            $this->cleanupCompanyDatabase();
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    private function searchFriendCompanyIds($search)
    {
        return User::on('mysql')
            ->where('is_active', 1)
            ->where(function($q) use ($search) {
                $q->where('user_company_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            })
            ->pluck('id')
            ->toArray();
    }
    
    private function getCompanyInfo($companyId)
    {
        $company = User::on('mysql')->find($companyId);
        
        if ($company) {
            return [
                'name' => $company->user_company_name ?: $company->name,
            ];
        }
        
        return [
            'name' => '不明',
        ];
    }
    
    
    private function removeShareToCompany($currentCompanyId, $friendCompanyId)
    {
        try {
            $this->switchToCompanyDatabase($currentCompanyId);
            
            $vehicles = DB::connection('user_db')->table('vehicles')
                ->where('is_share', 1)
                ->whereNotNull('share_to')
                ->where('share_to', '!=', 'all')
                ->get();
            
            $updatedCount = 0;
            
            foreach ($vehicles as $vehicle) {
                $shareTo = json_decode($vehicle->share_to, true);
                
                if (!is_array($shareTo) || empty($shareTo)) {
                    continue;
                }
                
                if (in_array($friendCompanyId, $shareTo)) {
                    $newShareTo = array_values(array_diff($shareTo, [$friendCompanyId]));
                    
                    if (empty($newShareTo)) {
                        DB::connection('user_db')->table('vehicles')
                            ->where('id', $vehicle->id)
                            ->update([
                                'is_share' => 0,
                                'share_to' => null,
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::connection('user_db')->table('vehicles')
                            ->where('id', $vehicle->id)
                            ->update([
                                'share_to' => json_encode($newShareTo),
                                'updated_at' => now(),
                            ]);
                    }
                    
                    $updatedCount++;
                }
            }
            
            $this->cleanupCompanyDatabase();
            
            if ($updatedCount > 0) {
                \Log::info("Removed share for {$updatedCount} vehicles from company {$friendCompanyId}");
            }
            
        } catch (\Exception $e) {
            $this->cleanupCompanyDatabase();
            throw $e;
        }
    }
}