<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaintenanceRequest;
use App\Models\MaintenanceItem;
use App\Support\WorkTypes;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $this->activeMonth($request);
        $rows  = MaintenanceItem::with('user')->forMonth($month)->orderByDesc('work_date')->get();

        return view('maintenance.index', [
            'month' => $month, 'rows' => $rows, 'types' => WorkTypes::MAINTENANCE,
            'statuses' => WorkTypes::MAINTENANCE_STATUSES,
        ]);
    }

    public function store(MaintenanceRequest $request)
    {
        MaintenanceItem::create($request->validated() + ['user_id' => $request->user()->id]);
        return back()->with('ok', 'تمت إضافة العملية.');
    }

    public function update(MaintenanceRequest $request, MaintenanceItem $maintenance)
    {
        $maintenance->update($request->validated());
        return back()->with('ok', 'تم الحفظ.');
    }

    public function destroy(MaintenanceItem $maintenance)
    {
        $maintenance->delete();
        return back()->with('ok', 'تم حذف العملية.');
    }
}
