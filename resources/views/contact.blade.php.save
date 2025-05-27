@extends('layouts.app')

@push('styles')
<style>
    .section-title {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    .contact-form {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .contact-info-item {
        padding: 1rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    .contact-info-item:last-child {
        border-bottom: none;
    }
    .contact-info-icon {
        width: 50px;
        height: 50px;
        background: #e3f2fd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .form-select {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-select:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #495057;
    }
</style>
@endpush

@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="contact-us container">
        <div class="mw-930">
            <h2 class="page-title">Contact Us</h2>
        </div>
    </section>

    <hr class="mt-2 text-secondary" />
    <div class="mb-4 pb-4"></div>

    <section class="contact-us container">
        <div class="row">
            <div class="col-lg-6">
                <h2 class="section-title">Get In Touch</h2>
                <p class="fs-6 text-secondary">
                    Have questions about our products or need assistance with your project?
                    We're here to help! Fill out the form below and we'll get back to you as soon as possible.
                </p>

                <!-- Contact Form -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST" class="contact-form">
                    @csrf

                    <!-- Anti-spam honeypot field (hidden) -->
                    <input type="text" name="honeypot" style="display: none;" tabindex="-1" autocomplete="off">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" placeholder="Your Name" value="{{ old('name') }}" required>
                                <label for="name">Your Name *</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" placeholder="Your Email" value="{{ old('email') }}" required>
                                <label for="email">Your Email *</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" placeholder="Your Phone" value="{{ old('phone') }}">
                                <label for="phone">Phone Number</label>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <select class="form-select @error('subject') is-invalid @enderror"
                                        id="subject" name="subject" required>
                                    <option value="">Choose a subject</option>
                                    @if(isset($subjectOptions))
                                        @foreach($subjectOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('subject') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="General Inquiry" {{ old('subject') == 'General Inquiry' ? 'selected' : '' }}>General Inquiry</option>
                                        <option value="Product Information" {{ old('subject') == 'Product Information' ? 'selected' : '' }}>Product Information</option>
                                        <option value="Quote Request" {{ old('subject') == 'Quote Request' ? 'selected' : '' }}>Quote Request</option>
                                        <option value="Technical Support" {{ old('subject') == 'Technical Support' ? 'selected' : '' }}>Technical Support</option>
                                        <option value="Order Inquiry" {{ old('subject') == 'Order Inquiry' ? 'selected' : '' }}>Order Inquiry</option>
                                        <option value="Installation Support" {{ old('subject') == 'Installation Support' ? 'selected' : '' }}>Installation Support</option>
                                        <option value="Warranty Claim" {{ old('subject') == 'Warranty Claim' ? 'selected' : '' }}>Warranty Claim</option>
                                        <option value="Complaint" {{ old('subject') == 'Complaint' ? 'selected' : '' }}>Complaint</option>
                                        <option value="Partnership Inquiry" {{ old('subject') == 'Partnership Inquiry' ? 'selected' : '' }}>Partnership Inquiry</option>
                                        <option value="Other" {{ old('subject') == 'Other' ? 'selected' : '' }}>Other</option>
                                    @endif
                                </select>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea class="form-control @error('message') is-invalid @enderror"
                                  id="message" name="message" placeholder="Your Message"
                                  style="height: 120px" required>{{ old('message') }}</textarea>
                        <label for="message">Your Message *</label>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                </form>
            </div>

            <div class="col-lg-6">
                <h2 class="section-title">Contact Information</h2>

                <div class="contact-info">
                    <div class="contact-info-item d-flex mb-4">
                        <div class="contact-info-icon me-3">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h5>Address</h5>
                            <p class="text-secondary mb-0">
                                Blk1 Lot 1 Ph6 Glocal St.<br>
                                Sterling Industrial Park<br>
                                Libtong, Meycauayan Bulacan<br>
                                Philippines 3020
                            </p>
                        </div>
                    </div>

                    <div class="contact-info-item d-flex mb-4">
                        <div class="contact-info-icon me-3">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h5>Phone</h5>
                            <p class="text-secondary mb-0">
                                <a href="tel:+63448167442" class="text-decoration-none">(044) 816 7442</a>
                            </p>
                        </div>
                    </div>

                    <div class="contact-info-item d-flex mb-4">
                        <div class="contact-info-icon me-3">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h5>Email</h5>
                            <p class="text-secondary mb-0">
                                <a href="mailto:hommss@gmail.com" class="text-decoration-none">hommss@gmail.com</a>
                            </p>
                        </div>
                    </div>

                    <div class="contact-info-item d-flex mb-4">
                        <div class="contact-info-icon me-3">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h5>Business Hours</h5>
                            <p class="text-secondary mb-0">
                                Monday - Friday: 8:00 AM - 6:00 PM<br>
                                Saturday: 8:00 AM - 5:00 PM<br>
                                Sunday: Closed
                            </p>
                        </div>
                    </div>

                    <div class="contact-info-item d-flex">
                        <div class="contact-info-icon me-3">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h5>Follow Us</h5>
                            <p class="text-secondary mb-0">
                                <a href="https://www.facebook.com/hommss.tiles" target="_blank" class="text-decoration-none">
                                    Facebook: @hommss.tiles
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mb-5 pb-4"></div>
</main>

@push('styles')
<style>
/* Ensure all contact form elements are rectangular */
.contact-form .form-control,
.contact-form .form-select,
.contact-form input,
.contact-form textarea,
.contact-form select,
.contact-form button {
    border-radius: 0 !important;
}

/* Override any Bootstrap or theme rounded corners */
.form-floating > .form-control,
.form-floating > .form-select {
    border-radius: 0 !important;
}

/* Ensure buttons are also rectangular */
.contact-form .btn,
.contact-form button[type="submit"] {
    border-radius: 0 !important;
}

/* Override any global select styling */
select,
.form-select {
    border-radius: 0 !important;
}

/* Override any input styling */
input[type="text"],
input[type="email"],
input[type="tel"],
textarea {
    border-radius: 0 !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced form validation and UX
    const contactForm = document.querySelector('.contact-form');
    const subjectSelect = document.getElementById('subject');
    const messageTextarea = document.getElementById('message');

    // Auto-resize message textarea
    if (messageTextarea) {
        messageTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        });
    }

    // Subject-specific message placeholders
    if (subjectSelect && messageTextarea) {
        const messagePlaceholders = {
            'General Inquiry': 'Please describe your inquiry in detail...',
            'Product Information': 'Which products are you interested in? Please provide specific details about your requirements...',
            'Quote Request': 'Please provide details about the products you need, quantities, and project timeline...',
            'Technical Support': 'Please describe the technical issue you\'re experiencing in detail...',
            'Order Inquiry': 'Please provide your order number or details about your order inquiry...',
            'Installation Support': 'Please describe your installation requirements and any specific challenges...',
            'Warranty Claim': 'Please provide your purchase details and describe the issue with your product...',
            'Complaint': 'Please describe your concern in detail so we can address it promptly...',
            'Partnership Inquiry': 'Please describe your business and the type of partnership you\'re interested in...',
            'Other': 'Please provide details about your inquiry...'
        };

        subjectSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            if (selectedValue && messagePlaceholders[selectedValue]) {
                messageTextarea.placeholder = messagePlaceholders[selectedValue];
            } else {
                messageTextarea.placeholder = 'Your Message';
            }
        });
    }

    // Form submission enhancement
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';

                // Re-enable button after 10 seconds as fallback
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Send Message';
                }, 10000);
            }
        });
    }
});
</script>
@endpush

@endsection
