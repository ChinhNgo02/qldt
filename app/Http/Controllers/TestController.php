<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Subjects;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        $query = Subjects::query()
            ->addSelect('subjects.*')
            ->addSelect([
                'class_count' => Classes::selectRaw('count(*)')
                    ->WhereColumn('classes.subject_id', 'subjects.id')
            ])
            ->leftJoin('classes', 'subjects.id', 'classes.subject_id')
            ->groupBy('subjects.id');
        dd($query);
    }
}