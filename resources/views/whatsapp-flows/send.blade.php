@extends('layouts.app')

@section('styles')
<style>
/* Custom Toast Styling */
.custom-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    min-width: 300px;
    z-index: 9999;
    padding: 15px 20px;
    border-radius: 5px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    color: white;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: space-between;
    animation: slideIn 0.3s ease-out forwards;
    opacity: 0;
}

.custom-toast-success {
    background: linear-gradient(to right, #28a745, #218838);
    border-left: 5px solid #1e7e34;
}

.custom-toast-error {
    background: linear-gradient(to right, #dc3545, #c82333);
    border-left: 5px solid #bd2130;
}

.custom-toast-icon {
    margin-right: 12px;
    font-size: 20px;
}

.custom-toast-close {
    cursor: pointer;
    margin-left: 15px;
    opacity: 0.7;
    transition: opacity 0.2s;
    font-size: 18px;
}

.custom-toast-close:hover {
    opacity: 1;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
</style>
@endsection

@section('content')
<div class="container py-4 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Send Flow</h2>
                    <a href="{{ route('whatsapp-flows.index') }}" class="btn btn-outline-primary">
                        <i class="fa fa-arrow-left"></i> Back to Flows
                    </a>
                </div>

                <div class="card-body">
                    <h4 class="mb-3">Flow: {{ $flow['name'] }}</h4>
                    
                    <!-- Flow Status Badge -->
                    <div class="mb-3">
                        <span class="badge {{ $flow['status'] === 'DRAFT' ? 'bg-warning' : 'bg-success' }}">
                            {{ $flow['status'] }}
                        </span>
                    </div>

                    <!-- Validation Errors -->
                    @if(!empty($flow['validation_errors']))
                        <div class="alert alert-warning mb-3">
                            <h5 class="alert-heading">Validation Warnings</h5>
                            <ul class="mb-0">
                                @foreach($flow['validation_errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form id="sendFlowForm" method="POST" action="{{ route('whatsapp-flows.send', ['id' => $flow['id']]) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number(s)</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number"
                                  placeholder="e.g., 91882228282 or multiple numbers: 91882228282,91993337777" required
                                  title="Please enter numbers only, separate multiple numbers with commas">
                            <div class="form-text">Include country code without '+' or '00'. For multiple numbers, separate with commas (no spaces).</div>
                        </div>

                        @if($flow['status'] !== 'DRAFT')
                        <!-- Message Template Fields - Only shown for published flows -->
                        <div class="mb-3">
                            <label for="header_text" class="form-label">Header Text</label>
                            <input type="text" class="form-control" id="header_text" name="header_text" 
                                  placeholder="Header text for your message">
                        </div>
                        
                        <div class="mb-3">
                            <label for="body_text" class="form-label">Body Text</label>
                            <textarea class="form-control" id="body_text" name="body_text" 
                                     rows="3" placeholder="Main body of your message"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="footer_text" class="form-label">Footer Text</label>
                            <input type="text" class="form-control" id="footer_text" name="footer_text" 
                                  placeholder="Footer text for your message">
                        </div>
                        @endif

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary" id="sendButton">
                                <i class="fa fa-paper-plane"></i> Send Flow
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing form handler');
    
    // Custom toast notification function
    function showCustomToast(message, type = 'success', duration = 5000) {
        console.log('Showing custom toast:', message, type);
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `custom-toast custom-toast-${type}`;
        
        // Create icon based on type
        const icon = document.createElement('span');
        icon.className = 'custom-toast-icon';
        icon.innerHTML = type === 'success' ? '<i class="fa fa-check-circle"></i>' : '<i class="fa fa-exclamation-circle"></i>';
        
        // Create message container
        const messageEl = document.createElement('span');
        messageEl.textContent = message;
        
        // Create close button
        const closeBtn = document.createElement('span');
        closeBtn.className = 'custom-toast-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.onclick = function() {
            document.body.removeChild(toast);
        };
        
        // Assemble toast
        toast.appendChild(icon);
        toast.appendChild(messageEl);
        toast.appendChild(closeBtn);
        
        // Add to document
        document.body.appendChild(toast);
        
        // Automatically remove after duration
        setTimeout(function() {
            toast.style.animation = 'fadeOut 0.5s forwards';
            setTimeout(function() {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 500);
        }, duration);
    }
    
    const form = document.getElementById('sendFlowForm');
    const sendButton = document.getElementById('sendButton');
    const phoneNumberInput = document.getElementById('phone_number');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (!form || !sendButton || !phoneNumberInput) {
        console.error('Required elements not found:', {
            form: !!form,
            sendButton: !!sendButton,
            phoneNumberInput: !!phoneNumberInput
        });
        return;
    }

    console.log('Form elements found, setting up event listeners');

    // Allow only numbers and commas in the phone number field
    phoneNumberInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9,]/g, '');
    });

    form.addEventListener('submit', function(e) {
        console.log('Form submitted');
        e.preventDefault();
        
        const phoneNumberValue = phoneNumberInput.value.trim();
        const phoneNumbers = phoneNumberValue.split(',').filter(num => num.trim() !== '');
        
        console.log('Phone numbers:', phoneNumbers);

        // Validate there's at least one phone number
        if (phoneNumbers.length === 0) {
            console.error('No valid phone numbers provided');
            showCustomToast('Please enter at least one valid phone number', 'error');
            return;
        }

        // Validate each phone number format (numbers only)
        for (const number of phoneNumbers) {
            if (!/^[0-9]+$/.test(number)) {
                console.error('Invalid phone number format:', number);
                showCustomToast('Please enter valid phone numbers (numbers only)', 'error');
                return;
            }
        }

        // Disable the button and show loading state
        sendButton.disabled = true;
        sendButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

        // Create FormData
        const formData = new FormData();
        formData.append('phone_number', phoneNumberValue);
        formData.append('_token', csrfToken);
        
        // Add template fields if they exist
        const headerText = document.getElementById('header_text');
        const bodyText = document.getElementById('body_text');
        const footerText = document.getElementById('footer_text');
        
        if (headerText) formData.append('header_text', headerText.value);
        if (bodyText) formData.append('body_text', bodyText.value);
        if (footerText) formData.append('footer_text', footerText.value);

        // Log the request data
        console.log('Sending request with data:', {
            phone_number: phoneNumberValue,
            phone_numbers: phoneNumbers,
            flow_id: '{{ $flow["id"] }}',
            csrf_token: csrfToken,
            header_text: headerText ? headerText.value : null,
            body_text: bodyText ? bodyText.value : null,
            footer_text: footerText ? footerText.value : null
        });

        // Make the API request
        fetch('{{ route("whatsapp-flows.send", ["id" => $flow["id"]]) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(formData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                sendButton.classList.remove('btn-primary');
                sendButton.classList.add('btn-success');
                sendButton.innerHTML = '<i class="fa fa-check"></i> Flow Sent Successfully';
                
                // Show detailed success notification
                let recipientText = phoneNumbers.length > 1 ? 
                    `${phoneNumbers.length} recipients` : 
                    phoneNumberValue;
                const successMessage = `Flow sent successfully to ${recipientText}`;
                console.log('Showing success notification:', successMessage);
                
                // Try multiple notification methods to ensure it works
                if (window.__Utils && window.__Utils.notification) {
                    // Primary method - app's notification system
                    window.__Utils.notification(successMessage, 'success', 5000); // 5 second duration
                } else if (typeof toastr !== 'undefined') {
                    // Secondary method - toastr if available
                    toastr.success(successMessage, 'Success', {timeOut: 5000});
                } else if (typeof Swal !== 'undefined') {
                    // Tertiary method - SweetAlert if available
                    Swal.fire({
                        title: 'Success!',
                        text: successMessage,
                        icon: 'success',
                        timer: 3000
                    });
                } else {
                    // Use our custom toast implementation
                    showCustomToast(successMessage, 'success');
                }
                
                // Clear the phone number input for next send
                phoneNumberInput.value = '';
            } else {
                sendButton.classList.remove('btn-primary');
                sendButton.classList.add('btn-danger');
                sendButton.innerHTML = '<i class="fa fa-exclamation-circle"></i> Failed to Send Flow';
                
                // Show detailed error notification
                const errorMessage = data.error || 'Failed to send flow';
                console.log('Showing error notification:', errorMessage);
                
                // Try multiple notification methods to ensure it works
                if (window.__Utils && window.__Utils.notification) {
                    // Primary method - app's notification system
                    window.__Utils.notification(errorMessage, 'error', 8000); // Longer duration for errors
                } else if (typeof toastr !== 'undefined') {
                    // Secondary method - toastr if available
                    toastr.error(errorMessage, 'Error', {timeOut: 8000});
                } else if (typeof Swal !== 'undefined') {
                    // Tertiary method - SweetAlert if available
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error'
                    });
                } else {
                    // Use our custom toast implementation
                    showCustomToast(errorMessage, 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            sendButton.classList.remove('btn-primary');
            sendButton.classList.add('btn-danger');
            sendButton.innerHTML = '<i class="fa fa-exclamation-circle"></i> Error';
            
            // Show error notification
            const errorMessage = error.message || 'An unexpected error occurred';
            console.log('Showing error notification:', errorMessage);
            
            // Try multiple notification methods
            if (window.__Utils && window.__Utils.notification) {
                window.__Utils.notification(errorMessage, 'error', 8000);
            } else if (typeof toastr !== 'undefined') {
                toastr.error(errorMessage, 'Error', {timeOut: 8000});
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error'
                });
            } else {
                // Use our custom toast implementation
                showCustomToast(errorMessage, 'error');
            }
        })
        .finally(() => {
            // Re-enable the button after 3 seconds to allow for another attempt
            setTimeout(() => {
                sendButton.disabled = false;
                sendButton.classList.remove('btn-success', 'btn-danger');
                sendButton.classList.add('btn-primary');
                sendButton.innerHTML = '<i class="fa fa-paper-plane"></i> Send Flow';
            }, 3000);
        });
    });

    console.log('Form handler initialization complete');
});
</script>
@endsection
