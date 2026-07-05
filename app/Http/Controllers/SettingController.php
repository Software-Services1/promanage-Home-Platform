<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /** الحقول المتاحة للعرض على كارت خطة المحتوى. */
    public const CARD_FIELDS = [
        'company_name' => 'اسم الشركة',
        'platform'     => 'المنصة',
        'plan_date'    => 'التاريخ',
        'plan_time'    => 'التوقيت',
        'day_name'     => 'اليوم',
        'content_type' => 'نوع المحتوى',
        'post_type'    => 'نوع المنشور',
        'work_type'    => 'نوع العمل',
        'assignee'     => 'المصمم المسؤول',
        'supervisor'   => 'المشرف المتابِع',
        'approval'     => 'حالة الاعتماد',
        'status'       => 'الحالة',
        'caption'      => 'الكابشن',
    ];

    public const DEFAULT_CARD_FIELDS = ['company_name', 'platform', 'content_type', 'assignee', 'approval', 'status'];

    /** الحقول المُختارة حالياً (مع الافتراضي). */
    public static function cardFields(): array
    {
        $raw = Setting::get('content_card_fields');
        $fields = $raw ? json_decode($raw, true) : null;
        return is_array($fields) && $fields ? $fields : self::DEFAULT_CARD_FIELDS;
    }

    public function index()
    {
        $mode = Setting::get('supervisor_credit_mode', 'auto');
        $cardFields = self::cardFields();
        $allCardFields = self::CARD_FIELDS;
        $payroll = [
            'bonus_pct'     => Setting::get('payroll_bonus_pct', 5),
            'deduction_pct' => Setting::get('payroll_deduction_pct', 20),
            'threshold'     => Setting::get('payroll_threshold'),
        ];
        return view('settings.index', compact('mode', 'cardFields', 'allCardFields', 'payroll'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'supervisor_credit_mode' => ['required', Rule::in(['auto', 'assigned'])],
            'content_card_fields'    => ['array'],
            'content_card_fields.*'  => [Rule::in(array_keys(self::CARD_FIELDS))],
            'payroll_bonus_pct'      => ['required', 'numeric', 'min:0', 'max:100'],
            'payroll_deduction_pct'  => ['required', 'numeric', 'min:0', 'max:100'],
            'payroll_threshold'      => ['nullable', 'integer', 'min:0'],
        ]);

        Setting::put('supervisor_credit_mode', $data['supervisor_credit_mode']);
        Setting::put('content_card_fields', json_encode(array_values($data['content_card_fields'] ?? self::DEFAULT_CARD_FIELDS)));
        Setting::put('payroll_bonus_pct', $data['payroll_bonus_pct']);
        Setting::put('payroll_deduction_pct', $data['payroll_deduction_pct']);
        Setting::put('payroll_threshold', $data['payroll_threshold'] ?: null);

        return back()->with('ok', 'تم حفظ إعدادات النظام.');
    }
}
