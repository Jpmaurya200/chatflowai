@extends('layouts.app')

@section('content')
<div class="container py-4 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Edit Flow</h2>
                    <a href="{{ route('whatsapp-flows.index') }}" class="btn btn-outline-primary">
                        <i class="fa fa-arrow-left"></i> Back to Flows
                    </a>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('whatsapp-flows.update', $flow['id']) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Flow Name -->
                            <div class="form-group col-md-6">
                                <label for="name" class="form-label">Flow Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $flow['name']) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Categories - Single select dropdown -->
                            <div class="form-group col-md-6">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control @error('categories') is-invalid @enderror" 
                                        id="category" name="categories[]" required>
                                    <option value="">Select a category</option>
                                    <option value="SIGN_UP" {{ in_array('SIGN_UP', old('categories', $flow['categories'])) ? 'selected' : '' }}>Sign Up</option>
                                    <option value="SIGN_IN" {{ in_array('SIGN_IN', old('categories', $flow['categories'])) ? 'selected' : '' }}>Sign In</option>
                                    <option value="APPOINTMENT_BOOKING" {{ in_array('APPOINTMENT_BOOKING', old('categories', $flow['categories'])) ? 'selected' : '' }}>Appointment Booking</option>
                                    <option value="LEAD_GENERATION" {{ in_array('LEAD_GENERATION', old('categories', $flow['categories'])) ? 'selected' : '' }}>Lead Generation</option>
                                    <option value="CONTACT_US" {{ in_array('CONTACT_US', old('categories', $flow['categories'])) ? 'selected' : '' }}>Contact Us</option>
                                    <option value="CUSTOMER_SUPPORT" {{ in_array('CUSTOMER_SUPPORT', old('categories', $flow['categories'])) ? 'selected' : '' }}>Customer Support</option>
                                    <option value="SURVEY" {{ in_array('SURVEY', old('categories', $flow['categories'])) ? 'selected' : '' }}>Survey</option>
                                    <option value="OTHER" {{ in_array('OTHER', old('categories', $flow['categories'])) ? 'selected' : '' }}>Other</option>
                                </select>
                                <small class="form-text text-muted">Select a category for your flow</small>
                                @error('categories')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update Flow
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
