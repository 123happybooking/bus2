<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Itinerary;
use App\Models\Masters\ItineraryDetail;
use Illuminate\Http\Request;

class ItineraryController extends Controller
{
    public function index(Request $request)
    {
        $query = Itinerary::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('itinerary_code', 'like', "%{$search}%")
                  ->orWhere('itinerary_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%");
            });
        }
        
        $itineraries = $query->orderBy('id')->paginate(20);
        
        if ($request->has('search')) {
            $itineraries->appends(['search' => $request->search]);
        }
        
        return view('masters.itineraries.index', compact('itineraries'));
    }

    public function create()
    {
        return view('masters.itineraries.create');
    }

    public function store(Request $request)
    {
        $details = $request->input('details', []);
        $filteredDetails = [];
        
        foreach ($details as $detail) {
            if (!empty($detail['description']) || 
                !empty($detail['arrival_time']) || 
                !empty($detail['departure_time']) || 
                !empty($detail['remark'])) {
                $filteredDetails[] = $detail;
            }
        }
        
        $request->merge(['details' => $filteredDetails]);
    
        $rules = [
            'itinerary_code' => 'required|string|max:20|unique:itineraries,itinerary_code',
            'itinerary_name' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:500',
            'details' => 'array',
            'details.*.arrival_time' => 'nullable|date_format:H:i',
            'details.*.departure_time' => 'nullable|date_format:H:i',
            'details.*.description' => 'required|string|max:500',
            'details.*.remark' => 'nullable|string|max:255',
        ];
    
        $messages = [
            'itinerary_code.required' => '旅程コードは必須です。',
            'itinerary_code.unique' => 'この旅程コードは既に使用されています。',
            'itinerary_code.max' => '旅程コードは20文字以内で入力してください。',
            'itinerary_name.required' => '旅程名は必須です。',
            'itinerary_name.max' => '旅程名は100文字以内で入力してください。',
            'category.max' => 'カテゴリーは50文字以内で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
            'details.*.description.required' => '行程説明は必須です。',
            'details.*.description.max' => '行程説明は500文字以内で入力してください。',
            'details.*.remark.max' => '備考は255文字以内で入力してください。',
            'details.*.arrival_time.date_format' => '到着時間の形式が無効です。',
            'details.*.departure_time.date_format' => '出発時間の形式が無効です。',
        ];
    
        $validator = \Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        $validated = $validator->validated();
    
        \DB::beginTransaction();

        $itinerary = Itinerary::create([
            'itinerary_code' => $validated['itinerary_code'],
            'itinerary_name' => $validated['itinerary_name'],
            'category' => $validated['category'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        if (isset($validated['details'])) {
            foreach ($validated['details'] as $index => $detailData) {
                $detailData['display_order'] = $index + 1;
                $detailData['itinerary_id'] = $itinerary->id;
                $detailData['description'] = $detailData['description'] ?? null;
                $detailData['remark'] = $detailData['remark'] ?? null;
                ItineraryDetail::create($detailData);
            }
        }

        \DB::commit();

        return redirect()->route('masters.itineraries.index')
            ->with([
                'success' => '旅程を登録しました。',
                'alert-type' => 'success'
            ]);
    }

    public function show($id)
    {
        $itinerary = Itinerary::findOrFail($id);
        return view('masters.itineraries.show', compact('itinerary'));
    }

    public function edit($id)
    {
        $itinerary = Itinerary::with('details')->findOrFail($id);
        return view('masters.itineraries.edit', compact('itinerary'));
    }

    public function update(Request $request, $id)
    {
        $details = $request->input('details', []);
        $filteredDetails = [];
        
        foreach ($details as $detail) {
            if (!empty($detail['description']) || 
                !empty($detail['arrival_time']) || 
                !empty($detail['departure_time']) || 
                !empty($detail['remark'])) {
                $filteredDetails[] = $detail;
            }
        }
        
        $request->merge(['details' => $filteredDetails]);
    
        $rules = [
            'itinerary_code' => 'required|string|max:20|unique:itineraries,itinerary_code,' . $id,
            'itinerary_name' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:500',
            'details' => 'array',
            'details.*.arrival_time' => 'nullable|date_format:H:i',
            'details.*.departure_time' => 'nullable|date_format:H:i',
            'details.*.description' => 'required|string|max:500',
            'details.*.remark' => 'nullable|string|max:255',
        ];
    
        $messages = [
            'itinerary_code.required' => '旅程コードは必須です。',
            'itinerary_code.unique' => 'この旅程コードは既に使用されています。',
            'itinerary_code.max' => '旅程コードは20文字以内で入力してください。',
            'itinerary_name.required' => '旅程名は必須です。',
            'itinerary_name.max' => '旅程名は100文字以内で入力してください。',
            'category.max' => 'カテゴリーは50文字以内で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
            'details.*.description.required' => '行程説明は必須です。',
            'details.*.description.max' => '行程説明は500文字以内で入力してください。',
            'details.*.remark.max' => '備考は255文字以内で入力してください。',
            'details.*.arrival_time.date_format' => '到着時間の形式が無効です。',
            'details.*.departure_time.date_format' => '出発時間の形式が無効です。',
        ];
    
        $validator = \Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        $validated = $validator->validated();
    
        \DB::beginTransaction();

        $itinerary = Itinerary::findOrFail($id);
        $itinerary->update([
            'itinerary_code' => $validated['itinerary_code'],
            'itinerary_name' => $validated['itinerary_name'],
            'category' => $validated['category'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $existingDetailIds = [];
        
        if (isset($validated['details'])) {
            foreach ($validated['details'] as $index => $detailData) {
                $updateData = [
                    'display_order' => $index + 1,
                    'arrival_time' => $detailData['arrival_time'] ?? null,
                    'departure_time' => $detailData['departure_time'] ?? null,
                    'description' => $detailData['description'] ?? null,
                    'remark' => $detailData['remark'] ?? null,
                ];
                
                if (!empty($detailData['id'])) {
                    $detail = ItineraryDetail::where('id', $detailData['id'])
                        ->where('itinerary_id', $itinerary->id)
                        ->first();
                        
                    if ($detail) {
                        $detail->update($updateData);
                        $existingDetailIds[] = $detail->id;
                    }
                } else {
                    $updateData['itinerary_id'] = $itinerary->id;
                    $detail = ItineraryDetail::create($updateData);
                    $existingDetailIds[] = $detail->id;
                }
            }
        }

        ItineraryDetail::where('itinerary_id', $itinerary->id)
            ->whereNotIn('id', $existingDetailIds)
            ->delete();

        \DB::commit();

        return redirect()->route('masters.itineraries.index')
            ->with([
                'success' => '旅程を更新しました。',
                'alert-type' => 'success'
            ]);
    }

    public function destroy($id)
    {
        \DB::beginTransaction();
        
        $itinerary = Itinerary::findOrFail($id);
        $itinerary->details()->delete();
        $itinerary->delete();
        
        \DB::commit();
        
        return redirect()->route('masters.itineraries.index')
            ->with([
                'success' => '旅程を削除しました。',
                'alert-type' => 'success'
            ]);
    }
}