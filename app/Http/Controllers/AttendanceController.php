<?php

namespace App\Http\Controllers;

use App\Helpers\LocationHelper;
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
            'lat'        => 'required|numeric|between:-90,90',
            'lng'        => 'required|numeric|between:-180,180',
        ]);

        // Location check — backend security gate
        $lat = (float) $request->lat;
        $lng = (float) $request->lng;

        if (!LocationHelper::isWithinRadius($lat, $lng)) {
            $distance = LocationHelper::distanceMeters(
                $lat, $lng,
                (float) env('CHURCH_LAT'),
                (float) env('CHURCH_LNG')
            );

            return response()->json([
                'message'         => 'You are not within the church premises.',
                'distance_meters' => round($distance),
            ], 403);
        }

        $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
        $result = $cloudinary->uploadApi()->upload(
            $request->file('live_image')->getRealPath(),
            [
                'folder'         => 'attendance',
                'transformation' => [
                    'width'        => 800,
                    'height'       => 800,
                    'crop'         => 'limit',
                    'quality'      => 'auto',
                    'fetch_format' => 'auto',
                ],
            ]
        );
        $path = $result['secure_url'];

        // Your existing late logic (unchanged)
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

        // Calculate exact distance for the record
        $distance = LocationHelper::distanceMeters(
            $lat, $lng,
            (float) env('CHURCH_LAT'),
            (float) env('CHURCH_LNG')
        );

        $attendance = Attendance::create([
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'department'      => $request->department,
            'picture_path'    => $path,
            'lat'             => $lat,
            'lng'             => $lng,
            'distance_meters' => round($distance, 2),
            'status'          => $isLate ? 'LATE' : 'ON-TIME',
            'time'            => $now->format('H:i:s'),
            'date'            => $now->toDateString(),
        ]);

        return response()->json($attendance, 201);
    }
}