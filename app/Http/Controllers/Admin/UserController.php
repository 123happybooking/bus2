<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Masters\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('login_id', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('id', 'desc')->paginate(15);
        
        if ($request->has('search')) {
            $users->appends(['search' => $request->search]);
        }
        
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_company_name' => 'required|string|max:255',
            'user_plan' => 'required|string|in:basic,premium,enterprise',
            'user_start_day' => 'required|date',
        ];

        $messages = [
            'name.required' => '名前は必須です。',
            'name.max' => '名前は255文字以内で入力してください。',
            'login_id.required' => 'ログインIDは必須です。',
            'login_id.max' => 'ログインIDは255文字以内で入力してください。',
            'login_id.unique' => 'このログインIDは既に使用されています。',
            'password.required' => 'パスワードは必須です。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードと確認用パスワードが一致しません。',
            'user_company_name.max' => '会社名は255文字以内で入力してください。',
            'user_company_name.required' => '会社名フィールドは必須です。',
            'user_plan.in' => 'プランは有効な値を選択してください。',
            'user_plan.required' => 'ユーザー予定フィールドは必須です。',
            'user_start_day.date' => '契約開始日は有効な日付を入力してください。',
            'user_start_day.required' => 'ユーザー開始日フィールドは必須です。',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'login_id' => $validated['login_id'],
                'password' => Hash::make($validated['password']),
                'user_company_name' => $validated['user_company_name'] ?? null,
                'user_plan' => $validated['user_plan'] ?? null,
                'user_start_day' => $validated['user_start_day'] ?? null,
            ]);

            if (!$user || !$user->exists) {
                throw new \Exception('ユーザーの作成に失敗しました。');
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => 'ユーザーの作成に失敗しました。',
                    'alert-type' => 'danger'
                ]);
        }

        try {
            $this->copyTemplateDatabase($user, $validated);
            
        } catch (\Exception $e) {
            try {
                $user->delete();
            } catch (\Exception $deleteError) {
            }
            
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => 'データベースの作成に失敗しました。',
                    'alert-type' => 'danger'
                ]);
        }
        
        return redirect('/admin/users')
            ->with([
                'success' => 'ユーザーを登録しました。',
                'alert-type' => 'success'
            ]);
    }

    private function copyTemplateDatabase($user, $validated)
    {
        $newDatabaseName = 'bus_user_' . $user->id;
        $templateDatabaseName = 'bus_user_0';

        $templateExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.schemata 
            WHERE schema_name = ?
        ", [$templateDatabaseName]);

        if ($templateExists[0]->count == 0) {
            throw new \Exception("テンプレートデータベース '{$templateDatabaseName}' が見つかりません。");
        }

        $dbExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.schemata 
            WHERE schema_name = ?
        ", [$newDatabaseName]);

        if ($dbExists[0]->count > 0) {
            DB::statement("DROP DATABASE IF EXISTS `{$newDatabaseName}`");
        }

        DB::statement("CREATE DATABASE `{$newDatabaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $tables = DB::select("
            SELECT TABLE_NAME 
            FROM information_schema.tables 
            WHERE table_schema = ?
            AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
        ", [$templateDatabaseName]);

        DB::statement("SET FOREIGN_KEY_CHECKS=0");

        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;
            
            try {
                DB::statement("DROP TABLE IF EXISTS `{$newDatabaseName}`.`{$tableName}`");
                
                $createTableSQL = DB::select("SHOW CREATE TABLE `{$templateDatabaseName}`.`{$tableName}`");
                
                if (!empty($createTableSQL)) {
                    $createSQL = $createTableSQL[0]->{'Create Table'};
                    
                    $createSQL = preg_replace(
                        "/CREATE TABLE `{$tableName}`/",
                        "CREATE TABLE `{$newDatabaseName}`.`{$tableName}`",
                        $createSQL
                    );
                    
                    DB::statement($createSQL);
                    DB::statement("INSERT INTO `{$newDatabaseName}`.`{$tableName}` SELECT * FROM `{$templateDatabaseName}`.`{$tableName}`");
                }
                
            } catch (\Exception $e) {
                continue;
            }
        }

        DB::statement("SET FOREIGN_KEY_CHECKS=1");

        $currentConfig = DB::connection()->getConfig();
        
        $newConfig = [
            'driver' => $currentConfig['driver'],
            'host' => $currentConfig['host'],
            'port' => $currentConfig['port'],
            'database' => $newDatabaseName,
            'username' => $currentConfig['username'],
            'password' => $currentConfig['password'],
            'charset' => $currentConfig['charset'],
            'collation' => $currentConfig['collation'],
            'prefix' => $currentConfig['prefix'],
            'strict' => false,
            'engine' => null,
        ];

        config(["database.connections.{$newDatabaseName}" => $newConfig]);
        
        $newConnection = DB::connection($newDatabaseName);
        $newConnection->statement("SET SESSION sql_mode = ''");

        $this->initializeUserData($newConnection, $user, $validated);

        return true;
    }

    private function initializeUserData($connection, $user, $validated)
    {
        try {
            $adminUser = $connection->table('staffs')
                ->where('login_id', 'admin')
                ->first();

            if ($adminUser) {
                $connection->table('staffs')
                    ->where('login_id', 'admin')
                    ->update([
                        'user_company_id' => $user->id,
                        'name' => $validated['name'],
                        'login_id' => $validated['login_id'],
                        'password' => Hash::make($validated['password']),
                        'updated_at' => now()
                    ]);
            } else {
                $branchId = 1;
                $branch = $connection->table('branches')->first();
                if ($branch && isset($branch->id)) {
                    $branchId = $branch->id;
                }
                
                $staffColumns = $connection->getSchemaBuilder()->getColumnListing('staffs');
                
                $staffData = [
                    'user_company_id' => $user->id,
                    'branch_id' => $branchId,
                    'name' => $validated['name'],
                    'login_id' => $validated['login_id'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'admin',
                    'display_order' => 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $staffData = array_intersect_key($staffData, array_flip($staffColumns));
                $connection->table('staffs')->insert($staffData);
            }

        } catch (\Exception $e) {
            throw new \Exception('スタッフ情報の初期化に失敗しました。');
        }

        try {
            $companyColumns = $connection->getSchemaBuilder()->getColumnListing('user_company_info');
            
            $companyData = [
                'user_company_id' => $user->id,
                'user_company_name' => $validated['user_company_name'] ?? null,
                'user_plan' => $validated['user_plan'] ?? null,
                'user_start_day' => $validated['user_start_day'] ?? null,
                'company_name' => $validated['user_company_name'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            $optionalFields = ['postal_code', 'address', 'phone_number', 'fax_number', 'email'];
            foreach ($optionalFields as $field) {
                if (in_array($field, $companyColumns)) {
                    $companyData[$field] = '';
                }
            }
            
            $companyData = array_intersect_key($companyData, array_flip($companyColumns));
            
            $exists = $connection->table('user_company_info')
                ->where('user_company_id', $user->id)
                ->exists();
            
            if ($exists) {
                $connection->table('user_company_info')
                    ->where('user_company_id', $user->id)
                    ->update($companyData);
            } else {
                $connection->table('user_company_info')->insert($companyData);
            }

        } catch (\Exception $e) {
            throw new \Exception('会社情報の初期化に失敗しました。');
        }

        $tablesWithUserCompanyId = ['agencies', 'customers', 'drivers', 'facilities', 'guides', 'partners', 'vehicles'];
        
        foreach ($tablesWithUserCompanyId as $tableName) {
            try {
                if ($connection->getSchemaBuilder()->hasColumn($tableName, 'user_company_id')) {
                    $connection->table($tableName)
                        ->where('user_company_id', 0)
                        ->orWhereNull('user_company_id')
                        ->update(['user_company_id' => $user->id]);
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255|unique:users,login_id,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'user_company_name' => 'required|string|max:255',
            'user_plan' => 'required|string|in:basic,premium,enterprise',
            'user_start_day' => 'required|date',
        ];

        $messages = [
            'name.required' => '名前は必須です。',
            'name.max' => '名前は255文字以内で入力してください。',
            'login_id.required' => 'ログインIDは必須です。',
            'login_id.max' => 'ログインIDは255文字以内で入力してください。',
            'login_id.unique' => 'このログインIDは既に使用されています。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードと確認用パスワードが一致しません。',
            'user_company_name.max' => '会社名は255文字以内で入力してください。',
            'user_company_name.required' => '会社名フィールドは必須です。',
            'user_plan.in' => 'プランは有効な値を選択してください。',
            'user_plan.required' => 'ユーザー予定フィールドは必須です。',
            'user_start_day.date' => '契約開始日は有効な日付を入力してください。',
            'user_start_day.required' => 'ユーザー開始日フィールドは必須です。',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $data = [
                'name' => $validated['name'],
                'login_id' => $validated['login_id'],
                'user_company_name' => $validated['user_company_name'] ?? null,
                'user_plan' => $validated['user_plan'] ?? null,
                'user_start_day' => $validated['user_start_day'] ?? null,
            ];

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);
            
            if (!empty($validated['password'])) {
                try {
                    $this->updateStaffPassword($user, $validated['password']);
                } catch (\Exception $e) {
                }
            }
            
            return redirect('/admin/users')
                ->with([
                    'success' => 'ユーザーを更新しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '更新に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    private function updateStaffPassword($user, $password)
    {
        $databaseName = 'bus_user_' . $user->id;
        
        $dbExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.schemata 
            WHERE schema_name = ?
        ", [$databaseName]);
        
        if ($dbExists[0]->count == 0) {
            return;
        }
        
        $currentConfig = DB::connection()->getConfig();
        
        $userDbConfig = [
            'driver' => $currentConfig['driver'],
            'host' => $currentConfig['host'],
            'port' => $currentConfig['port'],
            'database' => $databaseName,
            'username' => $currentConfig['username'],
            'password' => $currentConfig['password'],
            'charset' => $currentConfig['charset'],
            'collation' => $currentConfig['collation'],
            'prefix' => $currentConfig['prefix'],
            'strict' => false,
            'engine' => null,
        ];
        
        config(["database.connections.{$databaseName}" => $userDbConfig]);
        
        $userConnection = DB::connection($databaseName);
        
        $userConnection->table('staffs')
            ->where('login_id', $user->login_id)
            ->update(['password' => Hash::make($password)]);
    }

    public function destroy(User $user)
    {
        try {
            $databaseName = 'bus_user_' . $user->id;
            
            try {
                DB::statement("DROP DATABASE IF EXISTS `{$databaseName}`");
            } catch (\Exception $e) {
            }
            
            $user->delete();
            
            return redirect('/admin/users')
                ->with([
                    'success' => 'ユーザーを削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect('/admin/users')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}