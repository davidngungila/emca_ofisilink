<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBenefit extends Model
{
    protected $fillable = [
        'employee_id',
        'benefit_type',
        'benefit_name',
        'amount',
        'percentage',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
