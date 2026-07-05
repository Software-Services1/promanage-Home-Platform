<?php

namespace App\Support;

/**
 * المصدر الموحّد لأنواع الأعمال وقيمة نقاط كل نوع، والقوائم المرجعية.
 * تعريف واحد فقط لكل قائمة — تُستهلك من الخدمات والمتحكمات وواجهات Blade دون تكرار.
 */
class WorkTypes
{
    public const TASKS = [
        'project_file'   => ['label' => 'ملف مشروع',            'points' => 7,  'bonus' => 3, 'cat' => 'design'],
        'carousel'       => ['label' => 'كاروسيل',              'points' => 5,  'cat' => 'design'],
        'post_story'     => ['label' => 'بوست / استوري',        'points' => 3,  'cat' => 'design'],
        'project_board'  => ['label' => 'لوحة مشروع',           'points' => 5,  'cat' => 'design'],
        'insta_board'    => ['label' => 'لوحة إنستقرام',         'points' => 5,  'cat' => 'design'],
        'congrats_story' => ['label' => 'استوري تهنئة',         'points' => 1,  'cat' => 'design'],
        'video_edit'     => ['label' => 'مونتاج فيديو مشروع',    'points' => 15, 'cat' => 'video'],
        'reels'          => ['label' => 'ريلز',                 'points' => 7,  'cat' => 'video'],
        'reels_idea'     => ['label' => 'فكرة ريلز جديدة',       'points' => 3,  'cat' => 'idea'],
        'creative_idea'  => ['label' => 'تنفيذ فكرة إبداعية',    'points' => 5,  'cat' => 'sup'],
        'post_approved'  => ['label' => 'بوست/استوري معتمد',     'points' => 2,  'cat' => 'sup'],
        'post_published' => ['label' => 'بوست/استوري منشور',     'points' => 2,  'cat' => 'sup'],
    ];

    public const MAINTENANCE = [
        'add_page'     => ['label' => 'إضافة صفحة',                 'points' => 20],
        'minor_design' => ['label' => 'تعديل بسيط في التصميم',       'points' => 5],
        'major_design' => ['label' => 'تعديل كبير على مجموعة واجهات', 'points' => 10],
        'backend_edit' => ['label' => 'تعديل في الباك إند',          'points' => 15],
    ];

    public const PLATFORMS     = ['إنستقرام', 'فيسبوك', 'يوتيوب', 'إكس (X)', 'سناب شات', 'تيك توك', 'الموقع الإلكتروني'];
    public const CONTENT_TYPES = ['تعليمي', 'تفاعلي', 'مناسبات', 'ترفيهي', 'إلهامي', 'عرض عقاري', 'تعريفي'];
    public const POST_TYPES    = ['منشور', 'كاروسيل', 'بروفايل', 'استوري', 'ريلز', 'صورة', 'فيديو', 'نص', 'لوحة'];
    public const PLAN_STATUSES = ['فكرة', 'قيد التصميم', 'يحتاج تعديل', 'جاهز للنشر', 'مجدول في النشر التلقائي', 'تم النشر', 'مؤجل', 'ملغي'];
    public const STAGES        = ['فكرة', 'خطة', 'تصميم', 'تنفيذ', 'مراجعة', 'جاهز', 'منشور'];
    public const MAINTENANCE_STATUSES = ['قيد التنفيذ', 'تم'];

    public static function taskLabel(string $key): string { return self::TASKS[$key]['label'] ?? $key; }
    public static function maintenanceLabel(string $key): string { return self::MAINTENANCE[$key]['label'] ?? $key; }
    public static function maintenancePoints(string $key): int { return self::MAINTENANCE[$key]['points'] ?? 0; }
}
