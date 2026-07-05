<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // الوضع الافتراضي لاحتساب نقاط الإشراف: auto (توزيع تلقائي) — يمكن لمدير النظام تغييره
        if (! Setting::query()->where('key', 'supervisor_credit_mode')->exists()) {
            Setting::put('supervisor_credit_mode', 'auto');
        }
    }
}
