<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'occasion',
        'points',
        'remarks',
        'date',
    ];

    public function child()
    {
        return $this->belongsTo(Students::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'child_id')->withTrashed();
    }

}
