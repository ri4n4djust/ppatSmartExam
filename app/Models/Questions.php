<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
    protected $fillable = [
        'category_id',
        'text',
        'options',
        'correct_answer',
        'score_value',
        'score_ifwrong',
        'difficulty',
        'type',
    ];


}
