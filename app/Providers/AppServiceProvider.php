<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // الرواتب وإدارة المستخدمين: المشرف العام (admin) فقط
        Gate::define('view-salaries', fn ($user) => $user->hasRole('admin'));
        Gate::define('manage-users', fn ($user) => $user->hasRole('admin'));

        // مشاركة المتغيّرات المساعدة مع جميع الواجهات (القالب + كل الصفحات)
        View::composer('*', function ($view) {
            $arMonths = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];

            $view->with('arMonths', $arMonths);
            $view->with('roleLabels', [
                'admin' => 'مشرف عام', 'supervisor' => 'مشرف المحتوى',
                'designer' => 'مصمم جرافيك', 'editor' => 'مونتير ومصمم',
            ]);
            $view->with('avatarColor', fn ($id) => ['#191824', '#5B4BDB', '#059669', '#0284c7'][$id % 4]);
            $view->with('monthLabel', function ($ym) use ($arMonths) {
                [$y, $m] = explode('-', $ym);
                return $arMonths[(int) $m - 1] . ' ' . $y;
            });
            $view->with('activeMonth', session('active_month', now()->format('Y-m')));
        });
    }
}
