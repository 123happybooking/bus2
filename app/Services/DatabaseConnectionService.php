<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionService
{
    public static function checkUserDatabaseExists($userId)
    {
        $databaseName = 'bus_user_' . $userId;
        
        try {
            self::connectToDefaultDatabase();
            
            $result = DB::select("
                SELECT SCHEMA_NAME 
                FROM INFORMATION_SCHEMA.SCHEMATA 
                WHERE SCHEMA_NAME = ?", [$databaseName]);
            
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function createUserDatabase($userId)
    {
        $databaseName = 'bus_user_' . $userId;
        
        try {
            self::connectToDefaultDatabase();
            
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function connectToUserDatabase($userId)
    {
        $databaseName = 'bus_user_' . $userId;
        $connectionName = 'user_db_' . $userId;
        
        try {
            if (!self::checkUserDatabaseExists($userId)) {
                throw new \Exception("Database {$databaseName} does not exist");
            }

            $defaultConfig = config('database.connections.mysql');
            $password = $defaultConfig['password'] ?? env('DB_PASSWORD', '');
            
            $newConfig = [
                'driver' => 'mysql',
                'host' => $defaultConfig['host'] ?? env('DB_HOST', '127.0.0.1'),
                'port' => $defaultConfig['port'] ?? env('DB_PORT', '3306'),
                'database' => $databaseName,
                'username' => $defaultConfig['username'] ?? env('DB_USERNAME', 'root'),
                'password' => $password,
                'charset' => $defaultConfig['charset'] ?? 'utf8mb4',
                'collation' => $defaultConfig['collation'] ?? 'utf8mb4_unicode_ci',
                'prefix' => $defaultConfig['prefix'] ?? '',
                'strict' => $defaultConfig['strict'] ?? false,
                'engine' => $defaultConfig['engine'] ?? null,
                'options' => $defaultConfig['options'] ?? [],
            ];

            Config::set("database.connections.{$connectionName}", $newConfig);
            DB::purge($connectionName);
            DB::setDefaultConnection($connectionName);
            DB::connection()->getPdo();
            
            return true;
            
        } catch (\Exception $e) {
            self::connectToDefaultDatabase();
            throw $e;
        }
    }

    public static function connectToDefaultDatabase()
    {
        $defaultConnection = config('database.default', 'mysql');
        DB::setDefaultConnection($defaultConnection);
        DB::reconnect($defaultConnection);
    }

    public static function getCurrentDatabase()
    {
        try {
            return DB::connection()->getDatabaseName();
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function testConnection($connection = null)
    {
        try {
            if ($connection) {
                DB::connection($connection)->getPdo();
            } else {
                DB::connection()->getPdo();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}