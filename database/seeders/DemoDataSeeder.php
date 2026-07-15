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
            'supervisor' => User::where('email', 'moataz@easyhome.sa')->first()->id,
            'designer'   => User::where('email', 'abdullah@easyhome.sa')->first()->id,
            'editor'     => User::where('email', 'ashraf@easyhome.sa')->first()->id,
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
                ['كاروسيل وحدات سكنية','carousel','جاهز',$de,false,false,8],
                ['بوست عرض شقة دوبلكس','post_story','منشور',$de,false,false,9],
                ['بوست نصائح للمشترين','post_story','منشور',$de,true,false,10],
                ['لوحة مشروع جيرة','project_board','مراجعة',$de,false,false,14],
                ['استوري تهنئة اليوم الوطني','congrats_story','منشور',$de,false,false,11],
                ['مونتاج فيديو جولة مشروع','video_edit','منشور',$ed,false,false,6],
                ['ريلز قبل/بعد التشطيب','reels','جاهز',$ed,false,false,12],
                ['ريلز جولة سريعة بالموقع','reels','تصميم',$ed,true,false,15],
                ['فكرة ريلز «يوم في حياة وسيط»','reels_idea','منشور',$ed,false,false,13],
                ['بوست إعلان مشروع جديد','post_story','منشور',$ed,false,false,7],
                ['تنفيذ فكرة حملة «بيتك يستناك»','creative_idea','منشور',$su,false,false,3],
                ['اعتماد بوست عروض رمضان','post_approved','منشور',$su,false,false,4],
                ['نشر بوست العروض على المنصات','post_published','منشور',$su,false,false,4],
                ['اعتماد استوري التهنئة','post_approved','منشور',$su,false,false,11],
            ];
        } else {
            $tasks = [
                ['ملف مشروع برج النخيل','project_file','منشور',$de,false,false,6],
                ['كاروسيل عروض الصيف','carousel','منشور',$de,false,false,10],
                ['بوست ترحيبي','post_story','منشور',$de,false,false,12],
                ['ريلز افتتاح المعرض','reels','منشور',$ed,false,false,8],
                ['مونتاج إعلان قصير','video_edit','منشور',$ed,true,false,15],
                ['تنفيذ فكرة «جيرتك أمانة»','creative_idea','منشور',$su,false,false,5],
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
                ['إنستقرام',8,'13:30','تعليمي','كاروسيل','٥ خطوات لشراء عقار','دليلك المبسّط','احفظ المنشور وشاركه','','جاهز للنشر','approved',$de],
                ['إنستقرام',12,'20:00','ترفيهي','ريلز','مقاطع قبل/بعد','الفرق يستاهل','وش رأيك؟','موسيقى ترند','قيد التصميم','pending',$ed],
                ['فيسبوك',15,'11:00','تفاعلي','منشور','سؤال للجمهور','وين تحب تسكن؟','شاركنا رأيك','','فكرة','pending',$de],
                ['يوتيوب',18,'18:00','عرض عقاري','فيديو','جولة كاملة','جولة حصرية','الجولة على القناة','يوتيوب فقط','مجدول في النشر التلقائي','pending',$ed],
            ];
        } else {
            $plans = [
                ['إنستقرام',6,'19:00','عرض عقاري','منشور','برج النخيل','إطلالة لا تُنسى','سجّل اهتمامك','','تم النشر','approved',$de],
                ['فيسبوك',10,'12:00','تفاعلي','كاروسيل','عروض الصيف','فرصة محدودة','العروض تنتهي قريباً','','تم النشر','approved',$de],
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
                ['تعديل ألوان الهيدر — جيرة','minor_design',6,'تم'],
                ['إعادة تصميم صفحات الوحدات','major_design',14,'قيد التنفيذ'],
                ['ربط نموذج الطلبات بالبريد','backend_edit',16,'تم'],
            ];
            $leaves = [
                [$de,3,3,1,'approved','ظرف عائلي'],
                [$de,9,9,1,'approved','مراجعة طبية'],
                [$ed,17,19,3,'pending','سفر'],
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
