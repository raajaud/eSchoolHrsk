<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCharge extends Model
{
    use HasFactory;

    protected $connection = 'school';

    protected $fillable = [
        'user_id',
        'charge_type',
        'amount',
        'description',
        'charge_date',
        'is_paid',
    ];

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
