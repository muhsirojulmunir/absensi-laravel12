<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentContract extends Model
{
    protected $fillable = [
        'contract_number',
        'user_id',
        'employee_name',
        'employee_nik',
        'employee_birth_place',
        'employee_birth_date',
        'employee_address',
        'employee_phone',
        'employee_position',
        'contract_start',
        'contract_end',
        'basic_salary',
        'meal_allowance',
        'transport_allowance',
        'other_allowance',
        'other_allowance_note',
        'company_representative',
        'company_representative_position',
        'signed_city',
        'signed_date',
    ];

    protected $casts = [
        'employee_birth_date' => 'date',
        'contract_start'      => 'date',
        'contract_end'        => 'date',
        'signed_date'         => 'date',
        'basic_salary'        => 'integer',
        'meal_allowance'      => 'integer',
        'transport_allowance' => 'integer',
        'other_allowance'     => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalSalaryAttribute(): int
    {
        return $this->basic_salary + $this->meal_allowance + $this->transport_allowance + $this->other_allowance;
    }
}
