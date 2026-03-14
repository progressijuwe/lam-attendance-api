<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    
    protected $fillable = [
        'first_name',
        'last_name', 
        'department',
        'picture_path',
        'status',
        'time',
        'date',
    ];
}