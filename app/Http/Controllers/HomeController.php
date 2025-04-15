<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Show the landing page.
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Show the dashboard.
     */
    public function dashboard()
    {
        $student = Auth::user();
        
        // Get available courses (courses not enrolled by the student)
        $availableCourses = Course::whereDoesntHave('students', function($query) use ($student) {
            $query->where('enrollments.student_id', $student->id);
        })->with('department')->get();

        // Get upcoming assignments through enrolled courses
        $upcomingAssignments = collect(); // Initialize an empty collection
        
        if ($student->courses()->exists()) {
            $upcomingAssignments = $student->courses()
                ->with(['assignments' => function($query) {
                    $query->where('due_date', '>', now())
                        ->orderBy('due_date');
                }])
                ->get()
                ->pluck('assignments')
                ->flatten();
        }

        return view('dashboard', compact('availableCourses', 'upcomingAssignments'));
    }
} 