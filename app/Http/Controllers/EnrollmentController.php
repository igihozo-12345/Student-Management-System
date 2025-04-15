<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the enrollments.
     */
    public function index()
    {
        $enrollments = Enrollment::with(['student', 'course'])->get();
        return response()->json($enrollments);
    }

    /**
     * Store a newly created enrollment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'status' => 'required|in:enrolled,completed,dropped',
            'enrollment_date' => 'required|date',
            'completion_date' => 'nullable|date|after:enrollment_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if enrollment already exists
        $existingEnrollment = Enrollment::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($existingEnrollment) {
            return response()->json(['message' => 'Student is already enrolled in this course'], 409);
        }

        $enrollment = Enrollment::create($request->all());
        return response()->json($enrollment, 201);
    }

    /**
     * Display the specified enrollment.
     */
    public function show(Enrollment $enrollment)
    {
        return response()->json($enrollment->load(['student', 'course']));
    }

    /**
     * Update the specified enrollment.
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:enrolled,completed,dropped',
            'completion_date' => 'nullable|date|after:enrollment_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $enrollment->update($request->all());
        return response()->json($enrollment);
    }

    /**
     * Remove the specified enrollment.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return response()->json(null, 204);
    }

    /**
     * Get enrollments by student.
     */
    public function byStudent(Student $student)
    {
        $enrollments = $student->enrollments()->with('course')->get();
        return response()->json($enrollments);
    }

    /**
     * Get enrollments by course.
     */
    public function byCourse(Course $course)
    {
        $enrollments = $course->enrollments()->with('student')->get();
        return response()->json($enrollments);
    }
}
