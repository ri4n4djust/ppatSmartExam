<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exams extends Model
{
    protected $fillable = [
        'title',
        'start_time',
        'end_time',
        'duration',
        'count_qa',
        'status',
    ];
}
