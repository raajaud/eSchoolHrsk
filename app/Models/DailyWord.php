<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyWord extends Model
{
    use HasFactory;

    protected $connection = 'school'; // since you're using Schema::connection('school')
    protected $table = 'daily_words';

    protected $fillable = [
        'english_word',
        'pronunciation',
        'hindi_word',
        'hindi_meaning',
        'publish_date',
    ];
}
