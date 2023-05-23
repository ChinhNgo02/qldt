<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\ClassStudent;
use Illuminate\Http\Request;

class ClassStudentController extends Controller
{
    public function destroy($id, $class_id)
    {
        $isAccepted = Classes::find($class_id)->status;
        if ($isAccepted == 2) {
            return redirect()->back()->with('message', 'Không thể xóa học viên khi lớp đã được duyệt');
        } else {
            ClassStudent::where('user_id', $id)->delete();
            return redirect()->back()->with('message', 'Xóa thành công !');
            // return "deleted";
        }
    }

    public function store(Request $request, $id)
    {
        try {
            $users = $request->students;
            $numberOfStudent = ClassStudent::Where('class_id', $id)->count();
            // dd(1);

            $teacher = ClassStudent::query()
                ->Where('class_id', $id)
                ->leftJoin('users', 'users.id', 'class_students.user_id')
                ->Where('users.level', 2)
                ->first();

            if (!empty($teacher)) {
                $count = $numberOfStudent - 1;
            } else {
                $count = $numberOfStudent;
            }

            $message = '';
            $numberAdded = 0;
            // dd(2);
            foreach ($users as $user) {
                if ($count < 15) {
                    ClassStudent::updateOrCreate(
                        ['user_id' => $user['id'], 'class_id' => $id],
                        ['user_id' => $user['id'], 'class_id' => $id]
                    );
                    // dd(2);
                    $count++;
                    $numberAdded++;
                } else {
                    $message = 'Số lượng học viên trong lớp đã đạt đủ 15 học viên';
                    break;
                }
            }

            if (!empty($message)) {
                return [
                    'status' => 'success',
                    'message' => 'Thêm thành công ' . $numberAdded . ' sinh viên ! ' . $message,
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Thêm thành công ' . $numberAdded . ' sinh viên !',
            ];
        } catch (\Throwable $th) {
            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }
    }
}