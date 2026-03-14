<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = Attendance::orderBy('created_at', 'desc')->get();

        return response()->json($records->map(function ($record) {
            return [
                ...$record->toArray(),
                'time' => \Carbon\Carbon::parse($record->time)->format('h:i a'),
            ];
        }));
    }

    // POST /api/attendance
    public function store(Request $request)
    {
        $request->validate([
            'first_name'  => 'required|string',
            'last_name'   => 'required|string',
            'department'  => 'required|string',
            'live_image'  => 'required|image',
        ]);

        // store the image
        $path = cloudinary()->upload($request->file('live_image')->getRealPath())->getSecurePath();

        // determine status based on day + time
        $now     = Carbon::now();
        $hour    = $now->hour;
        $minute  = $now->minute;
        $day     = $now->dayOfWeek; // 0=Sun, 3=Wed, 4=Thu, 5=Fri, 6=Sat

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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
