<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Masters\Account;

class AccountConfigController extends Controller
{
    public function index(Request $request)
    {
        $config = AccountConfig::first();
        $accounts = Account::where('is_active', 1)->get();
        return view('masters.account-config.index', compact('config','accounts'));
    }

    public function update(Request $request, $id)
    {
        try {
            $config = AccountConfig::where('id',$id)->first();
            $data = [
                'account_cash_id' => $request->account_cash_id,
                'account_deposit_id' => $request->account_deposit_id,
                'account_mgj_id' => $request->account_mgj_id,
                'account_spmsg_id' => $request->account_spmsg_id,
            ];
            
            $config->update($data);

            return redirect()->route('masters.account-config.index')
                ->with('success', '更新しました。');

        } catch (\Exception $e) {
            \Log::error($e);
            return redirect()->back()
                ->withInput()
                ->with('error', '更新に失敗しました。');
        }
    }
}