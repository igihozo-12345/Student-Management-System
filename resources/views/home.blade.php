@extends('layouts.app')

@section('title', 'Welcome to Student Management System')

@section('content')
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold">Students Management System</h1>
                    <p class="lead">Streamline your academic journey with our comprehensive student management platform.</p>
                    <div class="mt-4">
                        <a href="{{ route('register') }}" class="btn btn-light btn-lg me-3">Get Started</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">Login</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <img src="https://via.placeholder.com/600x400" alt="Student Management" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Features</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-user-graduate fa-3x mb-3 text-primary"></i>
                            <h3 class="card-title">Student Profiles</h3>
                            <p class="card-text">Manage student information, track academic progress, and maintain comprehensive records.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                            <h3 class="card-title">Course Management</h3>
                            <p class="card-text">Organize courses, manage enrollments, and track student performance in each course.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x mb-3 text-primary"></i>
                            <h3 class="card-title">Analytics</h3>
                            <p class="card-text">Gain insights into student performance and course effectiveness through detailed analytics.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>Why Choose Our System?</h2>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check text-success me-2"></i> User-friendly interface</li>
                        <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Secure data management</li>
                        <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Real-time updates</li>
                        <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Comprehensive reporting</li>
                        <li class="mb-3"><i class="fas fa-check text-success me-2"></i> 24/7 support</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <img src="https://via.placeholder.com/600x400" alt="System Benefits" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </section>
@endsection 