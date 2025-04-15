@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    @if(auth()->user()->photo_path)
                        <img src="{{ Storage::url(auth()->user()->photo_path) }}" alt="Profile Photo" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-secondary mb-3 mx-auto" style="width: 150px; height: 150px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user fa-4x text-white"></i>
                        </div>
                    @endif
                    <h3>{{ auth()->user()->full_name }}</h3>
                    <p class="text-muted">{{ auth()->user()->student_id }}</p>
                    <p class="text-muted">{{ auth()->user()->email }}</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">Update Profile</a>
                        <a href="{{ route('profile.photo') }}" class="btn btn-outline-primary">Update Photo</a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Enrolled Courses</span>
                        <span class="badge bg-primary">{{ auth()->user()->courses->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Completed Courses</span>
                        <span class="badge bg-success">{{ auth()->user()->courses()->wherePivot('status', 'completed')->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>In Progress</span>
                        <span class="badge bg-warning">{{ auth()->user()->courses()->wherePivot('status', 'enrolled')->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>GPA</span>
                        <span class="badge bg-info">{{ number_format(auth()->user()->calculateGPA(), 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Upcoming Deadlines -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upcoming Deadlines</h5>
                </div>
                <div class="card-body">
                    @if(auth()->user()->upcomingAssignments->isEmpty())
                        <div class="alert alert-info">
                            No upcoming deadlines.
                        </div>
                    @else
                        <div class="list-group">
                            @foreach(auth()->user()->upcomingAssignments as $assignment)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $assignment->title }}</h6>
                                        <small class="text-danger">{{ $assignment->due_date->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">{{ $assignment->course->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-8">
            <!-- Course Enrollment -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Available Courses</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollModal">
                        Enroll in Course
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Credits</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($availableCourses as $course)
                                    <tr>
                                        <td>{{ $course->code }}</td>
                                        <td>{{ $course->name }}</td>
                                        <td>{{ $course->department->name }}</td>
                                        <td>{{ $course->credits }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="enrollCourse({{ $course->id }})">
                                                Enroll
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- My Courses -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">My Courses</h5>
                </div>
                <div class="card-body">
                    @if(auth()->user()->courses->isEmpty())
                        <div class="alert alert-info">
                            You are not enrolled in any courses yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Status</th>
                                        <th>Enrollment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(auth()->user()->courses as $course)
                                        <tr>
                                            <td>{{ $course->code }}</td>
                                            <td>{{ $course->name }}</td>
                                            <td>
                                                @if($course->pivot->status == 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($course->pivot->status == 'enrolled')
                                                    <span class="badge bg-warning">In Progress</span>
                                                @else
                                                    <span class="badge bg-danger">Dropped</span>
                                                @endif
                                            </td>
                                            <td>{{ $course->pivot->enrollment_date->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-info">View</a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="dropCourse({{ $course->id }})">
                                                        Drop
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Course Enrollment Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enroll in Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="enrollForm">
                    @csrf
                    <div class="mb-3">
                        <label for="course_id" class="form-label">Select Course</label>
                        <select class="form-select" id="course_id" name="course_id" required>
                            <option value="">Choose a course...</option>
                            @foreach($availableCourses as $course)
                                <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitEnrollment()">Enroll</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function enrollCourse(courseId) {
    if (confirm('Are you sure you want to enroll in this course?')) {
        fetch(`/enrollments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ course_id: courseId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to enroll in course');
            }
        });
    }
}

function dropCourse(courseId) {
    if (confirm('Are you sure you want to drop this course?')) {
        fetch(`/enrollments/${courseId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to drop course');
            }
        });
    }
}
</script>
@endpush
@endsection 