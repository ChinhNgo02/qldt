<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Classes;
use App\Models\ClassStudent;
use App\Models\Schedules;
use App\Models\Subjects;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use PharIo\Manifest\Author;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function customLogin(Request $request)
    {
        try {
            // dd(User::query()->get());

            $user = User::query()
                ->where('email', $request->get('email'))
                ->firstOrFail();

            if (Hash::check($request->get('password'), $user->password)) {
                Auth::login($user);
                return redirect()->intended('dashboard')
                    ->withSuccess('Signed in');
            }
        } catch (\Throwable $e) {
            return redirect()->route('login')->withSuccess('Login details are not valid');
        }
    }


    public function findValueById($object, $id, $id_field, $field)
    {
        if ($object[$id_field] == $id) {
            return $object[$field];
        }
    }

    public function findElementById($object, $id, $id_field, $field)
    {
        foreach ($object as $key => $value) {
            if ($value->$id_field == $id) {
                return $value->$field;
            }
        }
    }

    public function dashboard()
    {
        if (Auth::check()) {
            if (auth()->user()->level == 1) {
                $id = auth()->user()->id;
                // get number of class
                $classes_id = ClassStudent::query()
                    ->Where('user_id', $id)
                    ->get('class_id');
                // dd($classes_id);
                $classes = [];
                $teachers = [];
                $sessions = [];
                $scheduleOfWeek = [];
                $now = Carbon::now();
                $weekStartDate = $now->startOfWeek()->format('Y-m-d');
                $weekEndDate = $now->endOfWeek()->format('Y-m-d');
                // dd(1);
                foreach ($classes_id as $class_id) {
                    // get class
                    // dd($class_id['class_id']);
                    $class = Classes::query()
                        ->addSelect('classes.*')
                        ->addSelect('subjects.name as subject_name')
                        ->leftJoin('subjects', 'classes.subject_id', 'subjects.id')
                        ->Where('classes.id', $class_id['class_id'])
                        ->first();
                    // dd($class['id']);

                    // }
                    $classes[] = $class;
                    // get teacher
                    $user_id = ClassStudent::query()
                        ->Where('class_id', $class_id['class_id'])->first()->get('user_id');
                    // dd($user_id->toArray());
                    $teacher = User::query()
                        ->addSelect('users.name', 'users.email')
                        ->WhereIn('id', $user_id)
                        ->Where('level', 2)
                        ->first();
                    // dd($teacher);
                    $teacher['subject'] = $this->findValueById($class, $class_id['class_id'], 'id', 'subject_name');
                    $teachers[] = $teacher;

                    $schedule = Schedules::query()->Where('class_id', $class_id['class_id']);
                    $number_sessions = $schedule->get('id'); // get all id of sessions in schedule
                    $date = $schedule->get(['date', 'id']); // get all date and id of sessions in schedule to search
                    // dd($date->toArray());
                    if (!empty($number_sessions)) {
                        $attendances = Attendance::WhereIn('schedule_id', $number_sessions)->Where('user_id', $id)->get();
                        foreach ($attendances as $attendance) {
                            $attendance['subject'] = $this->findValueById($class, $class_id['class_id'], 'id', 'subject_name');
                            $attendance['date'] = $this->findElementById($date, $attendance['schedule_id'], 'id', 'date');
                            $attendance['class'] = $this->findValueById($class, $class_id['class_id'], 'id', 'name');
                            $sessions[] = $attendance;
                        }
                    }
                    // dd(var_dump($sessions));

                    $weekSchedule = $schedule->WhereBetween('date', [$weekStartDate, $weekEndDate])->get();
                    foreach ($weekSchedule as $schedule) {
                        $schedule['class'] = $this->findValueById($class, $class_id['class_id'], 'id', 'name');
                        $schedule['shift'] = $this->findValueById($class, $class_id['class_id'], 'id', 'shift');
                        $scheduleOfWeek[] = $schedule;
                    }
                    // dd($scheduleOfWeek);
                }


                return view('home')->with([
                    'classes' => $classes,
                    'sessions' => $sessions,
                    'teachers' => $teachers,
                    'scheduleOfWeek' => $scheduleOfWeek,
                ]);
            } else if (auth()->user()->level  == 2) {
                $id = auth()->user()->id;
                //get number of class
                $classes_id = ClassStudent::where('user_id', $id)->get('class_id');
                $classes = [];
                $scheduleOfWeek = [];
                $now = Carbon::now();
                $weekStartDate = $now->startOfWeek()->format('Y-m-d');
                $weekEndDate = $now->endOfWeek()->format('Y-m-d');

                foreach ($classes_id as $class_id) {
                    // get class
                    $class = Classes::query()
                        ->addSelect('classes.*')
                        ->addSelect('subjects.name as subject_name')
                        ->leftJoin('subjects', 'classes.subject_id', 'subjects.id')
                        ->where('classes.id', $class_id['class_id'])
                        ->first();
                    $classes[] = $class;

                    $schedule = Schedules::query()->where('class_id', $class_id['class_id']);
                    $number_sessions = $schedule->get('id'); // get all id of sessions in schedule

                    $weekSchedule = $schedule->whereBetween('date', [$weekStartDate, $weekEndDate])->get();
                    foreach ($weekSchedule as $schedule) {
                        $schedule['class'] = $this->findValueById($class, $class_id['class_id'], 'id', 'name');
                        $schedule['shift'] = $this->findValueById($class, $class_id['class_id'], 'id', 'shift');
                        $scheduleOfWeek[] = $schedule;
                    }
                }


                $admins = User::where('level', 3)->inRandomOrder()->limit(3)->get(['name', 'email']);

                return view('home')->with([
                    'classes' => $classes,
                    'admins' => $admins,
                    'scheduleOfWeek' => $scheduleOfWeek,
                ]);
            }

            if (auth()->user()->level == 3 || auth()->user()->level == 4) {
                $number_student = User::where('level', 1)->count();
                $number_teacher = User::where('level', 2)->count();
                $number_class = Classes::count();
                $number_subject = Subjects::count();

                return view('home')->with([
                    'number_student' => $number_student,
                    'number_teacher' => $number_teacher,
                    'number_class' => $number_class,
                    'number_subject' => $number_subject,
                ]);
            }
        }


        return redirect("login")->withSuccess('You are not allowed to access');
    }

    public function signOut()
    {
        Session::flush();
        Auth::logout();

        return Redirect('login');
    }
}
