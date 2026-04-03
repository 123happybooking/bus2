<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\DatabaseConnectionService;
use Illuminate\Support\Facades\DB;

class BaseUserModel extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->connection = DB::connection()->getName();
    }
    
    public function getConnectionName()
    {
        return DB::connection()->getName();
    }
}