<?php

namespace Database\Seeders;

use App\Models\ContentPlan;
use App\Models\Leave;
use App\Models\MaintenanceItem;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * بيانات تجريبية للشهر الحالي والشهر السابق — لإثبات أن كل شهر منفصل تماماً.
 */
class DemoDataSeeder extends Seeder
{
    private array $u = [];

    public function run(): void
    {
        $this->u = [
            'admin'      => User::where('email', 'mohammed@easyhome.sa')->first()->id,
          
        ];

        $cur  = Carbon::now()->startOfMonth();
        $prev = Carbon::now()->subMonth()->startOfMonth();

        $this->seedMonth($cur, true);
        $this->seedMonth($prev, false);
    }

    private function d(Carbon $base, int $day): string
    {
        return $base->copy()->day($day)->toDateString();
    }

    private function dayName(string $iso): string
    {
        $names = ['الأحد','الإثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
        return $names[Carbon::parse($iso)->dayOfWeek];
    }

    private function seedMonth(Carbon $base, bool $rich): void
    {
        $de = $this->u['designer'];
        $ed = $this->u['editor'];
        $su = $this->u['supervisor'];

        if ($rich) {
            $tasks = [
                ['ملف مشروع كمباوند الواحة','project_file','منشور',$de,false,true,5],
      
            ];
        } else {
            $tasks = [
                ['ملف مشروع برج النخيل','project_file','منشور',$de,false,false,6],
           
            ];
        }

        foreach ($tasks as [$title,$type,$stage,$uid,$late,$creative,$day]) {
            Task::create([
                'title' => $title, 'type' => $type, 'stage' => $stage, 'user_id' => $uid,
                'due_date' => $this->d($base, $day), 'is_late' => $late, 'is_creative' => $creative,
            ]);
        }

        if ($rich) {
            $plans = [
                ['إنستقرام',5,'19:00','عرض عقاري','منشور','صور كمباوند الواحة','كمباوند الواحة — حياة تليق بك','اكتشف وحدتك الآن','تأكيد ألوان الهوية','تم النشر','approved',$de],
            ];
        } else {
            $plans = [
                ['إنستقرام',6,'19:00','عرض عقاري','منشور','برج النخيل','إطلالة لا تُنسى','سجّل اهتمامك','','تم النشر','approved',$de],
            ];
        }

        foreach ($plans as [$platform,$day,$time,$ctype,$ptype,$dc,$dt,$cap,$notes,$status,$appr,$assignee]) {
            $iso = $this->d($base, $day);
            ContentPlan::create([
                'platform' => $platform, 'plan_date' => $iso, 'day_name' => $this->dayName($iso),
                'plan_time' => $time, 'content_type' => $ctype, 'post_type' => $ptype,
                'design_content' => $dc, 'design_text' => $dt, 'caption' => $cap, 'notes' => $notes,
                'status' => $status, 'assigned_to' => $assignee,
                'approval_state' => $appr, 'approval_note' => $appr === 'rejected' ? 'يحتاج تعديل' : null,
            ]);
        }

        if ($rich) {
            $maint = [
                ['صفحة مشاريع جديدة — إيزي هوم','add_page',2,'تم'],
            ];
            $leaves = [
                [$de,3,3,1,'approved','ظرف عائلي'],
            ];
        } else {
            $maint = [
                ['تحديث صور الصفحة الرئيسية — جيرة','minor_design',4,'تم'],
            ];
            $leaves = [
                [$ed,2,5,4,'approved','إجازة سنوية'],
            ];
        }

        foreach ($maint as [$title,$type,$day,$status]) {
            MaintenanceItem::create([
                'title' => $title, 'type' => $type, 'user_id' => $su,
                'work_date' => $this->d($base, $day), 'status' => $status,
            ]);
        }

        foreach ($leaves as [$uid,$from,$to,$days,$status,$reason]) {
            Leave::create([
                'user_id' => $uid, 'from_date' => $this->d($base, $from), 'to_date' => $this->d($base, $to),
                'days' => $days, 'status' => $status, 'reason' => $reason,
                'requested_at' => $this->d($base, max(1, $from - 2)),
            ]);
        }
    }
}
