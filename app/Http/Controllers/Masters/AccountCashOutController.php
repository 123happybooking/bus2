<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountCashOut;
use App\Models\Masters\AccountCashIn;
use App\Models\Masters\AccountCashoutData;
use App\Models\Masters\AccountPeriod;
use App\Models\Masters\UserCompanyInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class AccountCashOutController extends Controller
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
        $cashOuts= $datas['cashOuts'];
        $types = $datas['types'];

        return view('masters.account-cash-outs.index', compact('cashOuts','types','periods','period_id'));
    }

    public function create()
    {
        $types = AccountCashOut::$type;
        $cashIns = AccountCashIn::orderBy('type_id', 'asc')->orderBy('sort', 'desc')->get();
        return view('masters.account-cash-outs.create',compact('types','cashIns'));
    }

    public function store(Request $request)
    {
        $rules = [
            'type_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'sort' => 'nullable|integer|min:0',
            'cashin_id' => 'nullable|numeric',
        ];

        $messages = [
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'sort.integer' => 'ソート順は整数で入力してください。',
            'sort.min' => 'ソート順は0以上で入力してください。',
            'type_id.integer' => 'タイプIDは整数で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // sort 字段的默认值处理
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 0;
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        // type_id 处理：如果是空字符串则转为 null
        if (!isset($validated['type_id']) || $validated['type_id'] === '') {
            $validated['type_id'] = null;
        } else {
            $validated['type_id'] = (int)$validated['type_id'];
        }

        AccountCashOut::create($validated);

        return redirect()->route('masters.account-cash-outs.index')
            ->with([
                'success' => '現金出力を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $cashOut = AccountCashOut::findOrFail($id);
        $cashIns = AccountCashIn::orderBy('type_id', 'asc')->orderBy('sort', 'desc')->get();
        return view('masters.account-cash-outs.show', compact('cashOut','cashIns'));
    }

    public function edit($id)
    {
        $types = AccountCashOut::$type;
        $cashOut = AccountCashOut::findOrFail($id);
        $cashIns = AccountCashIn::orderBy('type_id', 'asc')->orderBy('sort', 'desc')->get();
        return view('masters.account-cash-outs.edit', compact('cashOut','types','cashIns'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'type_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'sort' => 'nullable|integer|min:0',
            'cashin_id' => 'nullable|numeric',
        ];

        $messages = [
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'sort.integer' => 'ソート順は整数で入力してください。',
            'sort.min' => 'ソート順は0以上で入力してください。',
            'type_id.integer' => 'タイプIDは整数で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // sort 字段的默认值处理
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 0;
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        // type_id 处理
        if (!isset($validated['type_id']) || $validated['type_id'] === '') {
            $validated['type_id'] = null;
        } else {
            $validated['type_id'] = (int)$validated['type_id'];
        }

        $cashOut = AccountCashOut::findOrFail($id);
        $cashOut->update($validated);

        return redirect()->route('masters.account-cash-outs.index')
            ->with([
                'success' => '現金出力を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        $cashOut = AccountCashOut::findOrFail($id);
        $cashOut->delete();

        return redirect()->route('masters.account-cash-outs.index')
            ->with([
                'success' => '現金出力を削除しました。',
                'alert-type' => 'success'
            ]);
    }

    public function saveAll(Request $request)
    {
        $data = $request->json()->all();

        DB::beginTransaction();
        try {
            AccountCashoutData::where('period_id',$data['period_id'])->delete();
            foreach ($data['data'] as $row) {
                AccountCashoutData::Create(
                    [
                        'period_id'=>$data['period_id'],
                        'type_id' => $row['type_id'],
                        'cashout_id'=> $row['id'],
                        'current_amount' => $row['current_amount'],
                    ]
                );
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



        // $cashOuts= $datas['cashOuts'];
        // $types = $datas['types'];

        // return view('masters.account-cash-outs.pdf', compact('cashOuts','types','period_id','startDate','endDate','company'));
        try {
            // 1. 渲染 HTML
            $html = View::make('masters.account-cash-outs.pdf', $datas)->render();
            

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
        $cashOuts=[];
        $types = AccountCashOut::$type;
        foreach($types as $key=>$type){
            $cashOuts[$key]['title']  = $type;
            $items = AccountCashOut::with(['cashOutData' => function ($query) use ($period_id) {
                        $query->where('period_id', $period_id);
                    }])
                    ->where('type_id', $key)
                    ->orderBy('sort','desc')
                    ->get();
            $cashOuts[$key]['items'] = $items;
        }
        return [
            'cashOuts'=>$cashOuts,
            'types'=>$types
        ];
    }
}