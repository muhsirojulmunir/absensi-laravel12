<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'last_login_at',
        'role_id',
        'employee_id',
        'division_id',
        'position',
        'phone',
        'address',
        'emergency_phone',
        'emergency_name',
        'emergency_relation',
        'avatar',
        'birth_place',
        'birth_date',
        'otp_code',
        'otp_expires_at',
        'fcm_token',
        'is_active',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeWithRole($query, $roleSlug)
    {
        return $query->whereHas('role', function ($query) use ($roleSlug) {
            $query->where('slug', $roleSlug);
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'otp_expires_at' => 'datetime',
        ];
    }

    public static function generateNextEmployeeId($roleId)
    {
        $role = Role::find($roleId);
        if (!$role) return '';

        $prefix = match ($role->slug) {
            'karyawan' => 'KRY',
            'pic' => 'PIC',
            'hrd' => 'HRD',
            'super-admin' => 'ADM',
            default => 'EMP',
        };

        $lastUser = self::where('employee_id', 'LIKE', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING(employee_id, ' . (strlen($prefix) + 2) . ') AS UNSIGNED) DESC')
            ->first();

        if (!$lastUser) {
            return $prefix . '-001';
        }

        $lastId = $lastUser->employee_id;
        $lastNumber = (int) substr($lastId, strpos($lastId, '-') + 1);
        $nextNumber = $lastNumber + 1;

        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
