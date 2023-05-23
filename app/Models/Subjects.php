<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subjects extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'start_date',
        'class_sessions',
    ];

    public function classes()
    {
        return $this->hasMany('App\Models\Classes', 'subject_id');
    }
}