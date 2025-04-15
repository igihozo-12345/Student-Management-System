<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'date_of_birth',
        'gender',
        'phone',
        'address',
        'photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'password' => 'hashed',
    ];

    protected $appends = ['upcoming_assignments'];

    /**
     * Get the enrollments for the student.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the courses the student is enrolled in.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot('status', 'enrollment_date', 'completion_date', 'grade')
            ->withTimestamps();
    }

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the student's upcoming assignments.
     */
    public function getUpcomingAssignmentsAttribute()
    {
        return $this->courses()
            ->with(['assignments' => function($query) {
                $query->where('due_date', '>', now())
                    ->orderBy('due_date');
            }])
            ->get()
            ->pluck('assignments')
            ->flatten();
    }

    /**
     * Calculate the student's GPA.
     */
    public function calculateGPA()
    {
        $completedCourses = $this->courses()
            ->wherePivot('status', 'completed')
            ->whereNotNull('enrollments.grade')
            ->get();

        if ($completedCourses->isEmpty()) {
            return 0;
        }

        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($completedCourses as $course) {
            $grade = $course->pivot->grade;
            $credits = $course->credits;

            $points = $this->gradeToPoints($grade);
            $totalPoints += $points * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? $totalPoints / $totalCredits : 0;
    }

    /**
     * Convert letter grade to points.
     */
    private function gradeToPoints($grade)
    {
        $gradePoints = [
            'A+' => 4.0,
            'A' => 4.0,
            'A-' => 3.7,
            'B+' => 3.3,
            'B' => 3.0,
            'B-' => 2.7,
            'C+' => 2.3,
            'C' => 2.0,
            'C-' => 1.7,
            'D+' => 1.3,
            'D' => 1.0,
            'F' => 0.0,
        ];

        return $gradePoints[$grade] ?? 0;
    }
} 