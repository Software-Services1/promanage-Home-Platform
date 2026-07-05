<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'salary', 'target', 'supervisor_share', 'join_date', 'is_active', 'last_login_at', 'last_seen_at'];

    // الراتب مخفي افتراضياً من أي تسلسل؛ يُكشف للأدمن فقط عبر طبقة العرض/الصلاحيات
    protected $hidden = ['password', 'remember_token', 'salary'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'join_date'         => 'date',
            'salary'            => 'decimal:2',
            'target'            => 'integer',
            'supervisor_share'  => 'decimal:2',
            'last_login_at'     => 'datetime',
            'last_seen_at'      => 'datetime',
            'is_active'         => 'boolean',
        ];
    }

    public function tasks(): HasMany { return $this->hasMany(Task::class); }
    public function leaves(): HasMany { return $this->hasMany(Leave::class); }
    public function maintenanceItems(): HasMany { return $this->hasMany(MaintenanceItem::class); }
    public function assignedContentPlans(): HasMany { return $this->hasMany(ContentPlan::class, 'assigned_to'); }

    public function dailyWage(): float { return (float) $this->salary / 30; }
    public function primaryRole(): ?string { return $this->roles->pluck('name')->first(); }

    public function loginLogs() { return $this->hasMany(LoginLog::class)->latest('logged_in_at'); }

    /** متصل الآن إذا شوهد نشاطه خلال آخر 5 دقائق. */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    /** هل يملك صلاحيات إشرافية واسعة (يرى بيانات الجميع)؟ */
    public function isManager(): bool
    {
        return $this->hasAnyRole(['admin', 'supervisor', 'manager']);
    }
}
