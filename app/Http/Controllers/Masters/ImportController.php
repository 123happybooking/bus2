<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\ImportHistory;
use App\Models\Masters\VehicleModel;
use App\Models\Masters\GroupInfoFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ImportController extends Controller
{
    private $columnMapping = [
        'A' => 'no',
        'B' => 'agency',
        'C' => 'agency_contact_name',
        'D' => 'payment_method',
        'E' => 'collection_date',
        'F' => 'amount',
        'G' => 'start_date',
        'H' => 'start_time',
        'I' => 'end_date',
        'J' => 'end_time',
        'K' => 'group_name',
        'L' => 'representative',
        'M' => 'representative_phone',
        'N' => 'content',
        'O' => 'vehicle_type',
        'P' => 'driver',
        'Q' => 'vehicle_number',
        'R' => 'sticker',
        'S' => 'remarks',
        'T' => 'remarks2',
        'U' => 'etc',
        'V' => 'parking',
        'W' => 'accommodation_fee',
        'X' => 'overtime',
        'Y' => 'advance_payment',
        'Z' => 'resale',
    ];

    public function index()
    {
        $histories = ImportHistory::orderBy('imported_at', 'desc')->paginate(20);
        return view('masters.group-infos.import', compact('histories'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('imports', 'public');

        $history = ImportHistory::create([
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status' => 'processing',
            'imported_by' => session('user_id'),
            'imported_by_name' => session('staff_name'),
            'imported_at' => now(),
        ]);

        try {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $dataRows = [];
            $errors = [];
            $rowIndex = 1;

            foreach ($rows as $index => $row) {
                if ($index < 1) {
                    continue;
                }

                $hasData = false;
                foreach ($row as $cell) {
                    $cell = trim((string)$cell ?? '');
                    if ($cell !== '' && $cell !== null) {
                        $hasData = true;
                        break;
                    }
                }

                if (!$hasData) {
                    continue;
                }

                $rowData = [];
                $rowErrors = [];
                $rowNumber = $index + 1;

                $startDate = $this->getCellValue($row, 'G');
                if (!empty($startDate)) {
                    $parsedStart = $this->parseDate($startDate);
                    if (!$parsedStart) {
                        $rowErrors[] = '開始日の形式が不正です';
                        $rowData['start_date'] = date('Y-m-d');
                    } else {
                        $rowData['start_date'] = $parsedStart;
                    }
                } else {
                    $rowData['start_date'] = date('Y-m-d');
                }

                $endDate = $this->getCellValue($row, 'I');
                if (!empty($endDate)) {
                    $parsedEnd = $this->parseDate($endDate);
                    if (!$parsedEnd) {
                        $rowErrors[] = '終了日の形式が不正です';
                        $rowData['end_date'] = $rowData['start_date'];
                    } else {
                        $rowData['end_date'] = $parsedEnd;
                    }
                } else {
                    $rowData['end_date'] = $rowData['start_date'];
                }

                $amount = $this->getCellValue($row, 'F');
                if (!empty($amount)) {
                    $cleanedAmount = str_replace(['¥', ',', ' ', '　'], '', trim($amount));
                    $nonNumericKeywords = ['合併', '未定', '合并', '取消', 'CXL'];
                    $isNonNumeric = false;
                    foreach ($nonNumericKeywords as $keyword) {
                        if (strpos($cleanedAmount, $keyword) !== false) {
                            $isNonNumeric = true;
                            break;
                        }
                    }
                    if ($isNonNumeric) {
                        $rowData['amount'] = null;
                    } elseif (!is_numeric($cleanedAmount)) {
                        $rowErrors[] = '金額は数値で入力してください';
                        $rowData['amount'] = null;
                    } else {
                        $rowData['amount'] = (float)$cleanedAmount;
                    }
                } else {
                    $rowData['amount'] = null;
                }

                $startTime = $this->getCellValue($row, 'H');
                if (!empty($startTime)) {
                    $parsedStartTime = $this->parseTime($startTime);
                    if (!$parsedStartTime) {
                        $rowErrors[] = '開始時刻の形式が不正です';
                        $rowData['start_time'] = '08:00:00';
                    } else {
                        $rowData['start_time'] = $parsedStartTime;
                    }
                } else {
                    $rowData['start_time'] = '08:00:00';
                }

                $endTime = $this->getCellValue($row, 'J');
                if (!empty($endTime)) {
                    $parsedEndTime = $this->parseTime($endTime);
                    if (!$parsedEndTime) {
                        $rowErrors[] = '終了時刻の形式が不正です';
                        $rowData['end_time'] = '18:00:00';
                    } else {
                        $rowData['end_time'] = $parsedEndTime;
                    }
                } else {
                    $rowData['end_time'] = '18:00:00';
                }

                $rowData['agency'] = $this->getCellValue($row, 'B');
                $rowData['agency_contact_name'] = $this->getCellValue($row, 'C');
                $rowData['payment_method'] = $this->getCellValue($row, 'D');
                $rowData['group_name'] = $this->getCellValue($row, 'K');
                $rowData['representative'] = $this->getCellValue($row, 'L');
                $rowData['representative_phone'] = $this->getCellValue($row, 'M');
                $rowData['vehicle_type'] = $this->getCellValue($row, 'O');

                $stickerText = $this->getCellValue($row, 'R');
                $stickerLink = $this->getCellHyperlink($worksheet, $index, 'R');
                if (!empty($stickerText) || !empty($stickerLink)) {
                    $isNamePattern = preg_match('/^[A-Z][a-z]+\.?\s+[A-Z][a-z]+/', trim($stickerText ?? ''));
                    if ($isNamePattern && strpos($stickerText, '.') === false) {
                        $stickerText = '';
                    }
                    $rowData['sticker'] = [
                        'text' => $stickerText,
                        'link' => $stickerLink,
                    ];
                } else {
                    $rowData['sticker'] = null;
                }

                $rowData['remarks'] = $this->getCellValue($row, 'T');

                if (!empty($rowData['vehicle_type'])) {
                    $vehicleModel = VehicleModel::where('model_code', $rowData['vehicle_type'])->first();
                    if ($vehicleModel) {
                        $rowData['vehicle_model_code'] = $vehicleModel->model_code;
                    }
                }

                if (empty($rowErrors)) {
                    $dataRows[] = $rowData;
                } else {
                    $errors[] = [
                        'row' => $rowNumber,
                        'data' => $rowData,
                        'errors' => $rowErrors,
                    ];
                }
            }

            $successCount = 0;
            $failedCount = 0;
            $importedData = [];

            DB::beginTransaction();

            try {
                foreach ($dataRows as $index => $rowData) {
                    try {
                        $result = $this->importRow($rowData);
                        if ($result['success']) {
                            $successCount++;
                            $rowData['group_info_id'] = $result['group_id'];
                            $importedData[] = array_merge($rowData, [
                                'status' => 'success',
                                'error' => null,
                                'row_number' => $index + 1,
                            ]);
                        } else {
                            $failedCount++;
                            $errorMsg = implode('、', $result['errors']);
                            $errors[] = [
                                'row' => $index + 1,
                                'data' => $rowData,
                                'errors' => $result['errors'],
                            ];
                            $importedData[] = array_merge($rowData, [
                                'status' => 'failed',
                                'error' => $errorMsg,
                                'row_number' => $index + 1,
                            ]);
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        $errors[] = [
                            'row' => $index + 1,
                            'data' => $rowData,
                            'errors' => [$e->getMessage()],
                        ];
                        $importedData[] = array_merge($rowData, [
                            'status' => 'failed',
                            'error' => $e->getMessage(),
                            'row_number' => $index + 1,
                        ]);
                    }
                }

                DB::commit();

                $history->update([
                    'status' => $failedCount > 0 ? 'failed' : 'completed',
                    'total_rows' => count($dataRows),
                    'success_rows' => $successCount,
                    'failed_rows' => $failedCount,
                    'imported_data' => $importedData,
                    'error_log' => $errors,
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'total' => count($dataRows),
                    'success_rows' => $successCount,
                    'failed_rows' => $failedCount,
                    'errors' => $errors,
                    'history_id' => $history->id,
                    'result_data' => array_map(function($item) {
                        return [
                            'agency' => $item['agency'] ?? null,
                            'group_name' => $item['group_name'] ?? null,
                            'start_date' => $item['start_date'] ?? null,
                            'start_time' => isset($item['start_time']) ? substr($item['start_time'], 0, 5) : null,
                            'end_date' => $item['end_date'] ?? null,
                            'end_time' => isset($item['end_time']) ? substr($item['end_time'], 0, 5) : null,
                            'amount' => $item['amount'] ?? null,
                            'status' => $item['status'] ?? 'failed',
                            'error' => $item['error'] ?? null,
                        ];
                    }, $importedData),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                $history->update([
                    'status' => 'failed',
                    'error_log' => [['message' => $e->getMessage()]],
                    'updated_at' => now(),
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            $history->update([
                'status' => 'failed',
                'error_log' => [['message' => $e->getMessage()]],
                'updated_at' => now(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function importRow($rowData)
    {
        $userId = session('user_id', 0);

        if (empty($rowData['start_date'])) {
            $rowData['start_date'] = date('Y-m-d');
        }

        $startDate = $rowData['start_date'];
        $endDate = $rowData['end_date'] ?? $startDate;
        $daysDiff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

        $groupInfo = GroupInfo::create([
            'agency' => $rowData['agency'] ?? null,
            'agency_contact_name' => $rowData['agency_contact_name'] ?? null,
            'reservation_status' => '予約',
            'payment_method' => $rowData['payment_method'] ?? null,
            'amount' => $rowData['amount'] ?? null,
            'vehicle_model_code' => $rowData['vehicle_model_code'] ?? null,
            'start_date' => $startDate,
            'start_time' => $rowData['start_time'] ?? '08:00:00',
            'end_date' => $endDate,
            'end_time' => $rowData['end_time'] ?? '18:00:00',
            'group_name' => $rowData['group_name'] ?? null,
            'vehicle_type' => $rowData['vehicle_type'] ?? null,
            'remarks' => $rowData['remarks'] ?? null,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $busAssignment = BusAssignment::create([
            'group_info_id' => $groupInfo->id,
            'representative' => $rowData['representative'] ?? null,
            'representative_phone' => $rowData['representative_phone'] ?? null,
            'start_date' => $startDate,
            'start_time' => $rowData['start_time'] ?? '08:00:00',
            'end_date' => $endDate,
            'end_time' => $rowData['end_time'] ?? '18:00:00',
            'count_daily' => $daysDiff,
            'vehicle_index' => 1,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        if (!empty($rowData['sticker'])) {
            $stickerText = $rowData['sticker']['text'] ?? '';
            $stickerLink = $rowData['sticker']['link'] ?? '';
            $fileName = !empty($stickerText) ? $stickerText : pathinfo($stickerLink, PATHINFO_BASENAME);
            if (!empty($fileName)) {
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                if (empty($extension) || strlen($extension) > 10) {
                    $extension = '';
                }
                if (strpos($fileName, '.') === false) {
                    $extension = '';
                }
                GroupInfoFile::create([
                    'group_info_id' => $groupInfo->id,
                    'bus_assignment_id' => $busAssignment->id,
                    'file_name' => $fileName,
                    'file_path' => $stickerLink,
                    'file_size' => 0,
                    'file_type' => null,
                    'file_extension' => $extension,
                    'uploaded_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $firstItineraryId = null;

        for ($i = 0; $i < $daysDiff; $i++) {
            $currentDate = Carbon::parse($startDate)->addDays($i);

            $itinerary = DailyItinerary::create([
                'group_info_id' => $groupInfo->id,
                'bus_assignment_id' => $busAssignment->id,
                'date' => $currentDate->format('Y-m-d'),
                'time_start' => $rowData['start_time'] ?? '08:00:00',
                'time_end' => $rowData['end_time'] ?? '18:00:00',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            if ($i === 0) {
                $firstItineraryId = $itinerary->id;
            }
        }

        if ($firstItineraryId) {
            $busAssignment->update(['daily_itinerary_id' => $firstItineraryId]);
        }

        return ['success' => true, 'group_id' => $groupInfo->id];
    }

    public function history($id)
    {
        $history = ImportHistory::findOrFail($id);
        return view('masters.group-infos.import-history', compact('history'));
    }

    public function getHistoryData($id)
    {
        $history = ImportHistory::findOrFail($id);
        return response()->json([
            'data' => $history->imported_data,
            'errors' => $history->error_log,
            'status' => $history->status,
        ]);
    }

    public function updateHistoryData(Request $request, $id)
    {
        $history = ImportHistory::findOrFail($id);
        $data = $history->imported_data;
        $rowIndex = $request->input('row_index');
        $field = $request->input('field');
        $value = $request->input('value');

        if (!isset($data[$rowIndex])) {
            return response()->json(['success' => false, 'message' => '行データが見つかりません']);
        }

        $oldRowData = $data[$rowIndex];
        $newRowData = $oldRowData;
        $newRowData[$field] = $value;

        if ($oldRowData[$field] == $value) {
            return response()->json(['success' => true, 'message' => '変更はありません']);
        }

        $data[$rowIndex][$field] = $value;
        $data[$rowIndex]['status'] = 'pending';
        $data[$rowIndex]['error'] = null;

        if (isset($oldRowData['group_info_id']) && $oldRowData['group_info_id'] > 0) {
            $groupId = $oldRowData['group_info_id'];
            $groupInfo = GroupInfo::find($groupId);

            if ($groupInfo) {
                DB::beginTransaction();
                try {
                    $groupInfo->update([
                        'agency' => $newRowData['agency'] ?? $groupInfo->agency,
                        'agency_contact_name' => $newRowData['agency_contact_name'] ?? $groupInfo->agency_contact_name,
                        'payment_method' => $newRowData['payment_method'] ?? $groupInfo->payment_method,
                        'amount' => $newRowData['amount'] ?? $groupInfo->amount,
                        'vehicle_model_code' => $newRowData['vehicle_model_code'] ?? $groupInfo->vehicle_model_code,
                        'start_date' => $newRowData['start_date'] ?? $groupInfo->start_date,
                        'end_date' => $newRowData['end_date'] ?? $groupInfo->end_date,
                        'group_name' => $newRowData['group_name'] ?? $groupInfo->group_name,
                        'vehicle_type' => $newRowData['vehicle_type'] ?? $groupInfo->vehicle_type,
                        'remarks' => $newRowData['remarks'] ?? $groupInfo->remarks,
                    ]);

                    $busAssignment = BusAssignment::where('group_info_id', $groupId)->first();
                    if ($busAssignment) {
                        $busAssignment->update([
                            'representative' => $newRowData['representative'] ?? $busAssignment->representative,
                            'representative_phone' => $newRowData['representative_phone'] ?? $busAssignment->representative_phone,
                        ]);
                    }

                    if (!empty($newRowData['start_date'])) {
                        $itineraries = DailyItinerary::where('group_info_id', $groupId)->get();
                        $daysCount = $itineraries->count();

                        if ($daysCount == 1) {
                            $itinerary = $itineraries->first();
                            $itinerary->update([
                                'date' => $newRowData['start_date'],
                                'time_start' => $newRowData['start_time'] ?? '08:00:00',
                                'time_end' => $newRowData['end_time'] ?? '18:00:00',
                            ]);
                        } else {
                            $startDate = Carbon::parse($newRowData['start_date']);
                            foreach ($itineraries as $index => $itinerary) {
                                $itinerary->update([
                                    'date' => $startDate->copy()->addDays($index)->format('Y-m-d'),
                                    'time_start' => $newRowData['start_time'] ?? '08:00:00',
                                    'time_end' => $newRowData['end_time'] ?? '18:00:00',
                                ]);
                            }
                        }
                    }

                    $data[$rowIndex]['status'] = 'pending';
                    $data[$rowIndex]['error'] = null;
                    $history->update(['imported_data' => $data]);

                    DB::commit();

                    return response()->json(['success' => true, 'message' => 'データを更新しました']);

                } catch (\Exception $e) {
                    DB::rollBack();
                    $data[$rowIndex]['status'] = 'failed';
                    $data[$rowIndex]['error'] = $e->getMessage();
                    $history->update(['imported_data' => $data]);
                    return response()->json(['success' => false, 'message' => $e->getMessage()]);
                }
            }
        }

        $history->update(['imported_data' => $data]);

        try {
            $result = $this->importRow($newRowData);
            if ($result['success']) {
                $data[$rowIndex]['status'] = 'pending';
                $data[$rowIndex]['error'] = null;
                $data[$rowIndex]['group_info_id'] = $result['group_id'];
                $history->update(['imported_data' => $data]);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            $data[$rowIndex]['status'] = 'failed';
            $data[$rowIndex]['error'] = $e->getMessage();
            $history->update(['imported_data' => $data]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function reimport(Request $request, $id)
    {
        $history = ImportHistory::findOrFail($id);
        $data = $history->imported_data;
        $userId = session('user_id', 0);
        $errors = [];
        $successCount = 0;
        $failedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($data as $index => $row) {
                if (isset($row['status']) && $row['status'] === 'success') {
                    continue;
                }

                try {
                    if (isset($row['group_info_id']) && $row['group_info_id'] > 0) {
                        $groupId = $row['group_info_id'];
                        $groupInfo = GroupInfo::find($groupId);

                        if ($groupInfo) {
                            $groupInfo->update([
                                'agency' => $row['agency'] ?? $groupInfo->agency,
                                'agency_contact_name' => $row['agency_contact_name'] ?? $groupInfo->agency_contact_name,
                                'payment_method' => $row['payment_method'] ?? $groupInfo->payment_method,
                                'amount' => $row['amount'] ?? $groupInfo->amount,
                                'vehicle_model_code' => $row['vehicle_model_code'] ?? $groupInfo->vehicle_model_code,
                                'start_date' => $row['start_date'] ?? $groupInfo->start_date,
                                'end_date' => $row['end_date'] ?? $groupInfo->end_date,
                                'group_name' => $row['group_name'] ?? $groupInfo->group_name,
                                'vehicle_type' => $row['vehicle_type'] ?? $groupInfo->vehicle_type,
                                'remarks' => $row['remarks'] ?? $groupInfo->remarks,
                            ]);

                            $busAssignment = BusAssignment::where('group_info_id', $groupId)->first();
                            if ($busAssignment) {
                                $busAssignment->update([
                                    'representative' => $row['representative'] ?? $busAssignment->representative,
                                    'representative_phone' => $row['representative_phone'] ?? $busAssignment->representative_phone,
                                ]);
                            }

                            if (!empty($row['start_date'])) {
                                $itineraries = DailyItinerary::where('group_info_id', $groupId)->get();
                                $daysCount = $itineraries->count();

                                if ($daysCount == 1) {
                                    $itinerary = $itineraries->first();
                                    $itinerary->update([
                                        'date' => $row['start_date'],
                                        'time_start' => $row['start_time'] ?? '08:00:00',
                                        'time_end' => $row['end_time'] ?? '18:00:00',
                                    ]);
                                } else {
                                    $startDate = Carbon::parse($row['start_date']);
                                    foreach ($itineraries as $idx => $itinerary) {
                                        $itinerary->update([
                                            'date' => $startDate->copy()->addDays($idx)->format('Y-m-d'),
                                            'time_start' => $row['start_time'] ?? '08:00:00',
                                            'time_end' => $row['end_time'] ?? '18:00:00',
                                        ]);
                                    }
                                }
                            }

                            $successCount++;
                            $data[$index]['status'] = 'success';
                            $data[$index]['error'] = null;
                            continue;
                        }
                    }

                    $result = $this->importRow($row);
                    if ($result['success']) {
                        $successCount++;
                        $data[$index]['status'] = 'success';
                        $data[$index]['error'] = null;
                        $data[$index]['group_info_id'] = $result['group_id'];
                    } else {
                        $failedCount++;
                        $errors[] = [
                            'row' => $index + 1,
                            'data' => $row,
                            'errors' => $result['errors'],
                        ];
                        $data[$index]['status'] = 'failed';
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'row' => $index + 1,
                        'data' => $row,
                        'errors' => [$e->getMessage()],
                    ];
                    $data[$index]['status'] = 'failed';
                    $data[$index]['error'] = $e->getMessage();
                }
            }

            DB::commit();

            $history->update([
                'status' => $failedCount > 0 ? 'failed' : 'completed',
                'total_rows' => count($data),
                'success_rows' => $successCount,
                'failed_rows' => $failedCount,
                'imported_data' => $data,
                'error_log' => $errors,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'success_rows' => $successCount,
                'failed_rows' => $failedCount,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $history = ImportHistory::findOrFail($id);
        if ($history->file_path && Storage::disk('public')->exists($history->file_path)) {
            Storage::disk('public')->delete($history->file_path);
        }
        $history->delete();
        return response()->json(['success' => true]);
    }

    public function downloadTemplate()
    {
        $headers = [
            'No.', 'AGT', '担当', '支払方法', '回収日', '金額',
            '開始日', '開始時刻', '終了日', '終了時刻',
            '団体名', '代表者', '代表者Tel', '内容',
            '車種', '司機', '車號', 'ステッカー', '備註', '備考',
            'ETC', '駐車代', '宿泊代', '残業代', '立替', '転売'
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $index => $header) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        $sheet->setCellValue('A2', '例: 1');
        $sheet->setCellValue('B2', 'R807037');
        $sheet->setCellValue('C2', '賀');
        $sheet->setCellValue('D2', '振込み');
        $sheet->setCellValue('F2', '10000');
        $sheet->setCellValue('G2', '2026-07-01');
        $sheet->setCellValue('H2', '08:00');
        $sheet->setCellValue('I2', '2026-07-01');
        $sheet->setCellValue('J2', '12:00');
        $sheet->setCellValue('K2', 'IT26040020');
        $sheet->setCellValue('O2', 'A');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'import_template.xlsx')->deleteFileAfterSend(true);
    }

    private function getCellValue($row, $column)
    {
        $index = ord($column) - ord('A');
        return $row[$index] ?? null;
    }

    private function getCellHyperlink($worksheet, $rowIndex, $column)
    {
        try {
            $cellAddress = $column . ($rowIndex + 1);
            $cell = $worksheet->getCell($cellAddress);
            if ($cell && $cell->hasHyperlink()) {
                return $cell->getHyperlink()->getUrl();
            }
        } catch (\Exception $e) {
        }
        return null;
    }

    private function parseDate($value)
    {
        if (is_numeric($value)) {
            $timestamp = ($value - 25569) * 86400;
            return date('Y-m-d', $timestamp);
        }

        $value = trim($value);

        if (strpos($value, ' ') !== false) {
            $value = explode(' ', $value)[0];
        }

        $formats = ['Y-m-d', 'Y/m/d', 'Y.n.j', 'm/d/Y', 'n/j/Y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    private function parseTime($value)
    {
        if (is_numeric($value)) {
            $totalSeconds = round($value * 86400);
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        $value = trim($value);

        if (strpos($value, ' ') !== false) {
            $value = explode(' ', $value)[0];
        }

        $formats = ['H:i:s', 'H:i', 'h:i:s A', 'h:i A'];

        foreach ($formats as $format) {
            $time = \DateTime::createFromFormat($format, $value);
            if ($time) {
                return $time->format('H:i:s');
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('H:i:s', $timestamp);
        }

        return null;
    }

    public function historyList()
    {
        $histories = ImportHistory::orderBy('imported_at', 'desc')->paginate(20);
        return view('masters.group-infos.import-history-list', compact('histories'));
    }
}