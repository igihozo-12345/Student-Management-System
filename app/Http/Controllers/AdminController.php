<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle admin login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->filled('remember'))) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    /**
     * Show the admin registration form.
     */
    public function showRegistrationForm()
    {
        return view('admin.auth.register');
    }

    /**
     * Handle admin registration.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.login')
            ->with('success', 'Registration successful! Please login.');
    }

    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'students' => Student::count(),
            'courses' => Course::count(),
            'departments' => Department::count(),
            'enrollments' => \App\Models\Enrollment::count(),
        ];

        $recentStudents = Student::with('courses')->latest()->take(5)->get();
        $recentCourses = Course::with(['department', 'enrollments'])->latest()->take(5)->get();
        
        // Get department statistics with course and student counts
        $departmentStats = Department::withCount('courses')
            ->with(['courses' => function($query) {
                $query->withCount('enrollments');
            }])
            ->get()
            ->map(function($department) {
                $department->total_students = $department->courses->sum('enrollments_count');
                return $department;
            });

        return view('admin.dashboard', compact('stats', 'recentStudents', 'recentCourses', 'departmentStats'));
    }

    /**
     * Show the list of students.
     */
    public function students()
    {
        $students = Student::with('courses')->paginate(10);
        return view('admin.students.index', compact('students'));
    }

    /**
     * Show the list of courses.
     */
    public function courses()
    {
        $courses = Course::with('department')->paginate(10);
        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Show the list of departments.
     */
    public function departments()
    {
        $departments = Department::withCount('courses')->paginate(10);
        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Handle admin logout.
     */
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    /**
     * Show the form for creating a new student.
     */
    public function createStudent()
    {
        return view('admin.students.create');
    }

    /**
     * Store a newly created student in storage.
     */
    public function storeStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|string|unique:students',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $student = new Student();
        $student->student_id = $request->student_id;
        $student->first_name = $request->first_name;
        $student->last_name = $request->last_name;
        $student->email = $request->email;
        $student->password = Hash::make($request->password);
        $student->date_of_birth = $request->date_of_birth;
        $student->phone = $request->phone;
        $student->address = $request->address;

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('public/student_photos', $filename);
            $student->photo = $filename;
        }

        $student->save();

        return redirect()->route('admin.students')
            ->with('success', 'Student created successfully.');
    }

    /**
     * Show the form for creating a new course.
     */
    public function createCourse()
    {
        $departments = Department::all();
        return view('admin.courses.create', compact('departments'));
    }

    /**
     * Store a newly created course in storage.
     */
    public function storeCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:courses',
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'credits' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $course = new Course();
        $course->code = $request->code;
        $course->name = $request->name;
        $course->department_id = $request->department_id;
        $course->credits = $request->credits;
        $course->description = $request->description;
        $course->save();

        return redirect()->route('admin.courses')
            ->with('success', 'Course created successfully.');
    }

    /**
     * Show the form for creating a new department.
     */
    public function createDepartment()
    {
        return view('admin.departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function storeDepartment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:departments',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $department = new Department();
        $department->code = $request->code;
        $department->name = $request->name;
        $department->description = $request->description;
        $department->save();

        return redirect()->route('admin.departments')
            ->with('success', 'Department created successfully.');
    }

    public function editDepartment(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:departments,code,' . $department->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments')
            ->with('success', 'Department updated successfully.');
    }

    public function deleteDepartment(Department $department)
    {
        // Check if department has any courses
        if ($department->courses()->exists()) {
            return redirect()->route('admin.departments')
                ->with('error', 'Cannot delete department because it has associated courses.');
        }

        $department->delete();

        return redirect()->route('admin.departments')
            ->with('success', 'Department deleted successfully.');
    }

    public function editCourse(Course $course)
    {
        $departments = Department::all();
        return view('admin.courses.edit', compact('course', 'departments'));
    }

    public function updateCourse(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:courses,code,' . $course->id,
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'credits' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $course->update($validated);

        return redirect()->route('admin.courses')
            ->with('success', 'Course updated successfully.');
    }

    public function deleteCourse(Course $course)
    {
        // Check if course has any enrollments
        if ($course->enrollments()->exists()) {
            return redirect()->route('admin.courses')
                ->with('error', 'Cannot delete course because it has associated enrollments.');
        }

        $course->delete();

        return redirect()->route('admin.courses')
            ->with('success', 'Course deleted successfully.');
    }

    public function editStudent(Student $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    public function updateStudent(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|max:20|unique:students,student_id,' . $student->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students,email,' . $student->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo) {
                Storage::delete('public/student_photos/' . $student->photo);
            }
            
            // Store new photo
            $photo = $request->file('photo');
            $filename = time() . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('public/student_photos', $filename);
            $validated['photo'] = $filename;
        }

        $student->update($validated);

        return redirect()->route('admin.students')
            ->with('success', 'Student updated successfully.');
    }

    public function deleteStudent(Student $student)
    {
        // Check if student has any enrollments
        if ($student->enrollments()->exists()) {
            return redirect()->route('admin.students')
                ->with('error', 'Cannot delete student because they have course enrollments.');
        }

        // Delete student's photo if exists
        if ($student->photo) {
            Storage::delete('public/student_photos/' . $student->photo);
        }

        $student->delete();

        return redirect()->route('admin.students')
            ->with('success', 'Student deleted successfully.');
    }
}
