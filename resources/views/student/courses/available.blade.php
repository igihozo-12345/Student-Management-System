@extends('layouts.app')

@section('title', 'Available Courses')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Available Courses</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="availableCoursesTable">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Department</th>
                                    <th>Credits</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courses as $course)
                                <tr>
                                    <td>{{ $course->code }}</td>
                                    <td>{{ $course->name }}</td>
                                    <td>{{ $course->department->name }}</td>
                                    <td>{{ $course->credits }}</td>
                                    <td>{{ Str::limit($course->description, 100) }}</td>
                                    <td>
                                        <form action="{{ route('courses.enroll', $course->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus-circle"></i> Enroll
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#availableCoursesTable').DataTable({
        "pageLength": 10,
        "order": [[1, "asc"]],
        "language": {
            "search": "Search courses:",
            "lengthMenu": "Show _MENU_ courses per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ available courses",
            "emptyTable": "No courses available for enrollment"
        }
    });
});
</script>
@endpush
@endsection 