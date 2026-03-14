<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Cloudinary\Cloudinary;

class AttendanceController extends Controller
{
    public function index()
    {
        $records = Attendance::orderBy('created_at', 'desc')->get();

        return response()->json($records->map(function ($record) {
            return [
                ...$record->toArray(),
                'time' => Carbon::parse($record->time)->format('h:i a'),
            ];
        }));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'department' => 'required|string',
            'live_image' => 'required|image',
        ]);

        $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
        $result = $cloudinary->uploadApi()->upload(
            $request->file('live_image')->getRealPath()
        );
        $path = $result['secure_url'];

        $now    = Carbon::now();
        $hour   = $now->hour;
        $minute = $now->minute;
        $day    = $now->dayOfWeek;

        $isLate = false;
        if ($day === 0) {
            $isLate = $hour > 7 || ($hour === 7 && $minute >= 15);
        } elseif (in_array($day, [3, 4, 5, 6])) {
            $isLate = $hour > 17 || ($hour === 17 && $minute >= 30);
        }

        $attendance = Attendance::create([
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'department'   => $request->department,
            'picture_path' => $path,
            'status'       => $isLate ? 'LATE' : 'ON-TIME',
            'time'         => $now->format('H:i:s'),
            'date'         => $now->toDateString(),
        ]);

        return response()->json($attendance, 201);
    }
}