<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subjects\StoreRequest;
use App\Http\Requests\Subjects\UpdateRequest;
use App\Models\Classes;
use App\Models\Subjects;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as FacadesRoute;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class SubjectsController extends Controller
{
    public function __construct()
    {
        $routeName = FacadesRoute::currentRouteName();
        $arr = explode('.', $routeName);
        $arr = array_map('ucfirst', $arr);
        $title = implode(' / ', $arr);
        View::share('title', $title);
    }

    public function index()
    {
        return view('subjects.index');
    }

    public function api()
    {
        $query = Subjects::query()
            ->addSelect('subjects.*')
            ->addSelect([
                'class_count' => Classes::selectRaw('count(*)')
                    ->WhereColumn('classes.subject_id', 'subjects.id')
            ])
            ->leftJoin('classes', 'subjects.id', 'classes.subject_id')
            ->groupBy('subjects.id');
        return DataTables::of($query)
            ->addColumn('edit', function ($object) {
                return route('subject.edit', $object);
            })
            ->addColumn('destroy', function ($object) {
                return route('subject.destroy', $object);
            })
            ->make(true);
    }


    public function update(UpdateRequest $request, Subjects $subject)
    {
        $subject->fill($request->validated());
        $subject->update();

        return redirect()->route('subject.index')->with('message', 'Success!!!');
    }

    public function edit(Subjects $subject)
    {
        return view('subjects.edit')->with('subject', $subject);
    }

    public function destroy(Subjects $subject)
    {
        if (auth()->user()->level > 2) {
            if (Classes::where('subject_id', $subject->id)->count() > 0) {
                return redirect()->route('subject.index')->with('message', 'This subject has classes !!!');
            } else {
                $subject->delete();
                return redirect()->route('subject.index')->with('message', 'Success delete ' . $subject->name . ' !!!');
                // return "deleted"; 
            }
        }
    }

    public function store(StoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $object = new Subjects();
        $object->fill($request->validated());
        $object->save();

        return redirect()->route('subject.index')->with('message', 'Success');
    }
}