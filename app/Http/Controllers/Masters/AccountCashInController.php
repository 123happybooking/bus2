<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountCashIn;
use App\Models\Masters\AccountCashinData;
use App\Models\Masters\AccountCashoutData;
use App\Models\Masters\AccountCashOut;
use App\Models\Masters\AccountPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use App\Models\Masters\UserCompanyInfo;

class AccountCashInController extends Controller
{
    public function index(Request $request)
    {
        $periods = AccountPeriod::orderBy('created_at', 'desc')->get();
        $period = AccountPeriod::orderBy('created_at', 'desc')->first();
        if ($request->period_id) {
            $period = AccountPeriod::find($request->period_id);
        }
        $period_id = $period->id ?? 0;

        $datas = $this->makeData($period_id);
        $cashIns= $datas['cashIns'];
        $cashSunIns= $datas['cashSunIns'];
        $cashQianIns = $datas['cashQianIns'];
        $types = $datas['types'];

        return view('masters.account-cash-ins.index', compact('cashIns','types','cashSunIns','cashQianIns','periods','period_id'));
    }

    public function create()
    {
        $types = AccountCashIn::$type;
        return view('masters.account-cash-ins.create',compact('types'));
    }

    public function store(Request $request)
    {
        $rules = [
            'mode'      => 'nullable|integer',
            'type_id'   => 'nullable|integer',
            'title'     => 'required|string|max:255',
            'sort'      => 'nullable|integer',
        ];

        $messages = [
            'title.required' => 'タイトルは必須です。',
            'title.max'      => 'タイトルは255文字以内で入力してください。',
            'mode.integer'   => 'モードは整数で入力してください。',
            'type_id.integer'=> 'タイプIDは整数で入力してください。',
            'sort.integer'   => 'ソート順は整数で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 处理空值或 Null
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 0;
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        if (!isset($validated['mode']) || $validated['mode'] === '' || $validated['mode'] === null) {
            $validated['mode'] = 0; // 假设默认为0，根据业务需求调整
        } else {
            $validated['mode'] = (int)$validated['mode'];
        }
        
        if (!isset($validated['type_id']) || $validated['type_id'] === '' || $validated['type_id'] === null) {
            $validated['type_id'] = null;
        } else {
            $validated['type_id'] = (int)$validated['type_id'];
        }

        AccountCashIn::create($validated);
        
        return redirect()->route('masters.account-cash-ins.index')
            ->with([
                'success' => '現金入力を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $cashIn = AccountCashIn::findOrFail($id);
        return view('masters.account-cash-ins.show', compact('cashIn'));
    }

    public function edit($id)
    {
        $cashIn = AccountCashIn::findOrFail($id);
        $types = AccountCashIn::$type;
        return view('masters.account-cash-ins.edit', compact('cashIn','types'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'mode'      => 'nullable|integer',
            'type_id'   => 'nullable|integer',
            'title'     => 'required|string|max:255',
            'sort'      => 'nullable|integer',
        ];

        $messages = [
            'title.required' => 'タイトルは必須です。',
            'title.max'      => 'タイトルは255文字以内で入力してください。',
            'mode.integer'   => 'モードは整数で入力してください。',
            'type_id.integer'=> 'タイプIDは整数で入力してください。',
            'sort.integer'   => 'ソート順は整数で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 处理空值或 Null
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 0;
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        if (!isset($validated['mode']) || $validated['mode'] === '' || $validated['mode'] === null) {
            $validated['mode'] = 0;
        } else {
            $validated['mode'] = (int)$validated['mode'];
        }

        if (!isset($validated['type_id']) || $validated['type_id'] === '' || $validated['type_id'] === null) {
            $validated['type_id'] = null;
        } else {
            $validated['type_id'] = (int)$validated['type_id'];
        }

        $cashIn = AccountCashIn::findOrFail($id);
        $cashIn->update($validated);
        
        return redirect()->route('masters.account-cash-ins.index')
            ->with([
                'success' => '現金入力を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $cashIn = AccountCashIn::findOrFail($id);
        $cashIn->delete();
        
        return redirect()->route('masters.account-cash-ins.index')
            ->with([
                'success' => '現金入力を削除しました。',
                'alert-type' => 'success'
            ]);
    }

    public function saveAll(Request $request)
    {
        $data = $request->json()->all();

        DB::beginTransaction();
        try {
            AccountCashinData::where('period_id',$data['period_id'])->delete();
            foreach ($data['data'] as $row) {
                AccountCashinData::Create(
                    [
                        'period_id'=>$data['period_id'],
                        'mod' => $row['mod'],
                        'cashin_id'=> $row['id'],
                        'current_amount' => $row['current_amount'],
                        'previous_amount' => $row['previous_amount'] ?? 0,
                    ]
                );

                // $cashOut = AccountCashOut::where('cashin_id',$row['id'])->first();
                // if($cashOut){
                //     $pre = $row['previous_amount'] ?? 0;
                //     AccountCashoutData::Create(
                //         [
                //             'period_id'=>$data['period_id'],
                //             'type_id' => $cashOut->type_id,
                //             'cashout_id'=> $cashOut->id,
                //             'current_amount' => $row['current_amount'] - $pre,
                //         ]
                //     );
                // }


            }
            //更新cashout数据
            $outData = new AccountCashoutData();
            $res = $outData->dealData($data['period_id']);
            if (!$res){
                return redirect()
                ->route('masters.account-cash-ins.index')
                ->with([
                    'error' => 'に失敗しました。',
                    'alert-type' => 'danger'
                ]);
            }
            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('cashin: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'システムエラーが発生しました。',
                    'errors' => ['general' => ['管理者にお問い合わせください。']]
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'システムエラーが発生しました。管理者にお問い合わせください。']);
        }

        
    }

    public function generatePdf(Request $request)
    {
        $period = AccountPeriod::orderBy('created_at', 'desc')->first();
        if ($request->period_id) {
            $period = AccountPeriod::find($request->period_id);
        }
        $period_id = $period->id ?? 0;
        $startDate = $period->start;
        $endDate = $period->end;

        $datas = $this->makeData($period_id);
        $company = UserCompanyInfo::first();
        $datas['startDate'] = $startDate;
        $datas['endDate'] = $endDate;
        $datas['company'] = $company;



        // $cashIns= $datas['cashIns'];
        // $cashSunIns= $datas['cashSunIns'];
        // $cashQianIns = $datas['cashQianIns'];
        // $types = $datas['types'];

        // return view('masters.account-cash-ins.pdf', compact('cashIns','types','cashSunIns','cashQianIns','period_id','startDate','endDate','company'));
        try {
            // 1. 渲染 HTML
            $html = View::make('masters.account-cash-ins.pdf', $datas)->render();
            

            // 2. 初始化 Browsershot
            // D:\Google\Chrome\Application
            $browsershot = Browsershot::html($html)
                ->paperSize(210, 297, 'mm')
                ->margins(10, 15, 5, 15) // 使用推荐的 margins 方法
                ->setOption('printBackground', true)
                ->waitUntilNetworkIdle()
                ->timeout(30000);

            // 2. 根据操作系统设置 Chrome 路径（仅在 Windows 下需要指定）
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows 环境：指定 chrome.exe 路径
                $browsershot->setChromePath('D:\Google\Chrome\Application\chrome.exe');
            } else {
                $browsershot->setNodePath('/usr/local/nodejs/bin/node');
                
                // 如果上面只指定 node 还不行，可以尝试加上 npm 路径
                $browsershot->setNpmPath('/usr/local/nodejs/bin/npm');
                $browsershot->setChromePath('/usr/local/chrome/chrome');
                // [Linux/生产环境] 取消下面这行的注释
                $browsershot->addChromiumArguments(['--no-sandbox', '--disable-setuid-sandbox']);
            }


            // 3. 【关键修改】获取 PDF 内容
            // 方法 A (推荐): 直接获取二进制字符串 (适用于大多数新版本)
            $pdfContent = $browsershot->getPdf();

            // 防御性检查：如果 getPdf() 返回的不是字符串（比如返回了对象或路径）
            if (!is_string($pdfContent)) {
                // 如果返回的是对象，尝试保存为临时文件再读取
                $tempFile = tempnam(sys_get_temp_dir(), 'invoice_') . '.pdf';
                $browsershot->savePdf($tempFile);
                $pdfContent = file_get_contents($tempFile);
                unlink($tempFile); // 立即删除临时文件
                
                // 如果还是不对，抛出异常以便调试
                if (!is_string($pdfContent)) {
                    throw new \Exception('Failed to get PDF content as string. Got: ' . gettype($pdfContent));
                }
            }

            // 4. 生成文件名
            $filename = 'キャッシュ·フロ一計算書作成シート'. '.pdf';

            // 5. 返回响应 (现在 strlen 接收的肯定是字符串了)
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdfContent), 
            ]);

        } catch (\Exception $e) {
            Log::error('PDF Generation Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            if (app()->environment('local')) {
                return response()->json([
                    'message' => 'PDF 生成失败',
                    'error' => $e->getMessage(),
                    'type' => gettype($e), // 显示错误类型
                ], 500);
            }
            return response()->view('errors.500', [], 500);
        }
    }

    private function makeData($period_id){
        $cashIns=[];
        $types = AccountCashIn::$type;
        foreach($types as $key=>$type){
            $cashIns[$key]['title']  = $type;
            $items = AccountCashIn::with(['cashInData' => function ($query) use ($period_id) {
                        $query->where('period_id', $period_id);
                    }])
                    ->where('mode', 1)
                    ->where('type_id', $key)
                    ->orderBy('sort','desc')
                    ->get();
            $cashIns[$key]['items'] = $items;
        }


        $cashSunIns = AccountCashIn::with(['cashInData' => function ($query) use ($period_id) {
                        $query->where('period_id', $period_id);
                    }])
                    ->where('mode', 2)
                    ->orderBy('sort','desc')
                    ->get();

        $cashQianIns= AccountCashIn::with(['cashInData' => function ($query) use ($period_id) {
                        $query->where('period_id', $period_id);
                    }])
                    ->where('mode', 3)
                    ->orderBy('sort','desc')
                    ->get();
        return [
            'cashIns'=>$cashIns,
            'cashSunIns'=>$cashSunIns,
            'cashQianIns'=>$cashQianIns,
            'types'=>$types
        ];
    }
}