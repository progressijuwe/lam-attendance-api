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
        'lat',
        'lng',
        'distance_meters',
        'status',
        'time',
        'date',
    ];

    public function getDepartmentLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->department));
    }
    
    protected $appends = ['department_label'];
}