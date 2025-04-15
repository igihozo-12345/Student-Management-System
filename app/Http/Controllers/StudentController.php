<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     */
    public function index()
    {
        $students = Student::with(['courses', 'enrollments'])->get();
        return response()->json($students);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|string|max:20|unique:students',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students',
            'password' => 'required|string|min:8',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('photo');
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('student-photos', 'public');
            $data['photo_path'] = $path;
        }

        $student = Student::create($data);
        return response()->json($student, 201);
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        return response()->json($student->load(['courses', 'enrollments']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        $student = Auth::user();
        return view('student.profile.edit', compact('student'));
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request)
    {
        $student = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students,email,' . $student->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $student->update($request->only(['first_name', 'last_name', 'email', 'phone', 'address']));
        
        return redirect()->route('dashboard')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the student's profile photo.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $student = Auth::user();
        
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }
            
            $path = $request->file('photo')->store('student-photos', 'public');
            $student->update(['photo_path' => $path]);
        }
        
        return redirect()->route('dashboard')
            ->with('success', 'Profile photo updated successfully.');
    }

    /**
     * Remove the specified student.
     */
    public function destroy(Student $student)
    {
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }
        $student->delete();
        return response()->json(null, 204);
    }

    /**
     * Get student's enrolled courses.
     */
    public function enrolledCourses()
    {
        $student = Auth::user();
        $courses = $student->courses()
            ->with(['department', 'assignments'])
            ->withPivot(['enrollment_date', 'status', 'completion_date'])
            ->get();
            
        return view('student.courses', compact('courses'));
    }

    /**
     * Display available courses for enrollment.
     */
    public function availableCourses()
    {
        $student = Auth::user();
        
        // Get IDs of courses the student is already enrolled in
        $enrolledCourseIds = $student->enrollments()->pluck('course_id');
        
        // Get all available courses (not enrolled in)
        $availableCourses = Course::with('department')
            ->whereNotIn('id', $enrolledCourseIds)
            ->orderBy('code')
            ->get();
            
        return view('student.courses.available', compact('availableCourses'));
    }

    public function enroll(Course $course)
    {
        $student = Auth::user();
        
        // Check if student is already enrolled
        if ($student->enrollments()->where('course_id', $course->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course.'
            ], 400);
        }
        
        // Enroll the student
        $student->enrollments()->create([
            'course_id' => $course->id,
            'enrollment_date' => now(),
            'status' => 'in_progress'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Successfully enrolled in ' . $course->name
        ]);
    }
    
    public function drop(Course $course)
    {
        $student = Auth::user();
        
        // Check if student is enrolled
        if (!$student->courses()->where('course_id', $course->id)->exists()) {
            return redirect()->back()->with('error', 'You are not enrolled in this course.');
        }
        
        // Drop the course
        $student->courses()->detach($course->id);
        
        return redirect()->back()->with('success', 'Successfully dropped the course.');
    }

    public function dashboard()
    {
        $student = Auth::user();
        
        // Get enrollment statistics
        $enrollments = $student->enrollments()->with('course')->get();
        
        $stats = [
            'enrolled_courses' => $enrollments->count(),
            'completed_courses' => $enrollments->where('status', 'completed')->count(),
            'in_progress' => $enrollments->where('status', 'in_progress')->count(),
            'gpa' => $student->calculateGPA(),
        ];

        // Get enrolled courses with their departments
        $enrolledCourses = $student->enrollments()
            ->with(['course.department'])
            ->orderBy('enrollment_date', 'desc')
            ->get();

        // Get upcoming deadlines (assignments) for in-progress courses
        $upcomingDeadlines = \App\Models\Assignment::whereIn('course_id', $enrollments->where('status', 'in_progress')->pluck('course_id'))
            ->where('due_date', '>=', now())
            ->with('course')
            ->orderBy('due_date')
            ->get();

        // Get available courses for enrollment (courses student is not enrolled in)
        $enrolledCourseIds = $enrollments->pluck('course_id');
        $availableCourses = Course::whereNotIn('id', $enrolledCourseIds)
            ->orderBy('code')
            ->get();

        return view('student.dashboard', compact('stats', 'enrolledCourses', 'upcomingDeadlines', 'availableCourses'));
    }
}
