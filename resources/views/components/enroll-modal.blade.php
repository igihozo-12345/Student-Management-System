<!-- Enroll Course Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1" role="dialog" aria-labelledby="enrollModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enrollModalLabel">Enroll in Course</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="enrollForm" onsubmit="return enrollCourse(event)">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="course_id">Select Course</label>
                        <select class="form-control" id="course_id" name="course_id" required>
                            <option value="">Choose a course...</option>
                            @foreach($availableCourses as $course)
                                <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Enroll</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function enrollCourse(event) {
    event.preventDefault();
    
    const courseId = document.getElementById('course_id').value;
    if (!courseId) {
        alert('Please select a course');
        return false;
    }

    const form = document.getElementById('enrollForm');
    const formData = new FormData(form);

    // Submit the form using fetch
    fetch(`/courses/${courseId}/enroll`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        return response.json().then(json => Promise.reject(json));
    })
    .then(data => {
        // Show success message
        alert(data.message || 'Successfully enrolled in course');
        
        // Close the modal
        $('#enrollModal').modal('hide');
        
        // Reload the page to show updated enrollments
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Failed to enroll in the course. Please try again.');
    });

    return false;
}
</script>
@endpush 