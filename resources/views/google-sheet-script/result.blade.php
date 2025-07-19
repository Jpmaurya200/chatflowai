@extends('layouts.app', ['title' => __tr('Google Sheets App Script')])
@section('content')
<div class="container mt-8">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Google Sheets Integration Script</span>
                        <a href="{{ route('google-sheet-script.index') }}" class="btn btn-sm btn-secondary">Generate Another</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <h5>Script Configuration:</h5>
                        <ul>
                            <li><strong>Template Name:</strong> {{ $templateName }}</li>
                            <li><strong>Template Language:</strong> {{ $templateLanguage }}</li>
                            <li><strong>Phone Column Index:</strong> {{ $phoneColumnIndex }} (Column {{ chr(65 + $phoneColumnIndex) }})</li>
                            @if(count($customFields) > 0)
                            <li>
                                <strong>Custom Fields:</strong>
                                <ul>
                                    @foreach($customFields as $index => $columnIndex)
                                        <li>field_{{ $index + 1 }}: Column {{ chr(65 + $columnIndex) }} (Index: {{ $columnIndex }})</li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h5>Instructions:</h5>
                        <ol>
                            <li>Open your Google Sheet</li>
                            <li>Click on Extensions > Apps Script</li>
                            <li>Replace all content in the script editor with the code below</li>
                            <li>Save the project (give it a name like "WhatsApp Integration")</li>
                            <li>Return to your spreadsheet and refresh the page</li>
                            <li>You'll see a new menu item "API Integration"</li>
                            <li>Click "Start Monitoring" to begin tracking new rows</li>
                        </ol>
                    </div>

                    <div class="form-group">
                        <label for="script">Google Apps Script:</label>
                        <div class="position-relative">
                            <textarea class="form-control" id="script" rows="20" readonly style="font-family: monospace;">{{ $script }}</textarea>
                            <button class="btn btn-sm btn-primary position-absolute top-0 end-0 m-1" 
                                onclick="copyScript()">Copy</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyScript() {
    var scriptText = document.getElementById('script');
    scriptText.select();
    document.execCommand('copy');
    
    // Create and show a temporary alert
    var alert = document.createElement('div');
    alert.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
    alert.innerHTML = 'Script copied to clipboard!';
    document.body.appendChild(alert);
    
    setTimeout(function() {
        alert.style.opacity = '0';
        setTimeout(function() {
            document.body.removeChild(alert);
        }, 500);
    }, 2000);
}
</script>

<style>
.alert {
    z-index: 1050;
    transition: opacity 0.5s;
}
</style>
@endsection