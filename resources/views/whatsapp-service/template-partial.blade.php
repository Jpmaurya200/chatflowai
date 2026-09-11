@php
$iterationIndex = 0;
@endphp

<script>
// Global variable storage
window.variableValues = window.variableValues || {};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing variable preview functionality...');
    initializeVariablePreview();
});

// Also initialize after a delay to catch late-loading selectize instances
setTimeout(function() {
    console.log('🔄 Re-initializing variable preview (delayed)...');
    initializeVariablePreview();
}, 2000);

function initializeVariablePreview() {
    console.log('🔍 Looking for variable input fields...');
    
    // Find all variable input fields
    const variableInputs = document.querySelectorAll('[data-variable-parameter]');
    console.log('📝 Found', variableInputs.length, 'variable input fields');
    
    variableInputs.forEach(function(input, index) {
        console.log('🔧 Setting up input', index + 1, ':', input.id);
        
        // Try multiple approaches to get the selectize instance
        let selectizeInstance = null;
        
        // Method 1: Direct access
        if (input.selectize) {
            selectizeInstance = input.selectize;
        }
        // Method 2: jQuery data
        else if (window.$ && $(input).data('selectize')) {
            selectizeInstance = $(input).data('selectize');
        }
        // Method 3: Wait a bit and try again
        else {
            setTimeout(function() {
                if (input.selectize) {
                    selectizeInstance = input.selectize;
                    setupSelectizeListeners(input, selectizeInstance);
                } else if (window.$ && $(input).data('selectize')) {
                    selectizeInstance = $(input).data('selectize');
                    setupSelectizeListeners(input, selectizeInstance);
                }
            }, 1000);
        }
        
        if (selectizeInstance) {
            setupSelectizeListeners(input, selectizeInstance);
        }
    });
}

function setupSelectizeListeners(input, selectizeInstance) {
    console.log('🎧 Setting up listeners for:', input.id);
    
    // Listen for changes in the selectize input
    selectizeInstance.on('change', function(value) {
        console.log('📝 Change event:', input.id, '=', value);
        const parameter = input.getAttribute('data-variable-parameter');
        const subjectType = input.getAttribute('data-subject-type');
        updateVariablePreview(parameter, value, subjectType);
    });
    
    // Listen for manual typing
    selectizeInstance.on('type', function(value) {
        console.log('⌨️ Type event:', input.id, '=', value);
        const parameter = input.getAttribute('data-variable-parameter');
        const subjectType = input.getAttribute('data-subject-type');
        updateVariablePreview(parameter, value, subjectType);
    });
    
    // Listen for item selection from dropdown
    selectizeInstance.on('item_add', function(value, $item) {
        console.log('📋 Item added event:', input.id, '=', value);
        const parameter = input.getAttribute('data-variable-parameter');
        const subjectType = input.getAttribute('data-subject-type');
        updateVariablePreview(parameter, value, subjectType);
    });
    
    // Listen for option selection
    selectizeInstance.on('option_add', function(value, data) {
        console.log('➕ Option added event:', input.id, '=', value);
        const parameter = input.getAttribute('data-variable-parameter');
        const subjectType = input.getAttribute('data-subject-type');
        updateVariablePreview(parameter, value, subjectType);
    });
    
    // Listen for blur events (when user clicks away)
    selectizeInstance.on('blur', function() {
        const value = selectizeInstance.getValue();
        console.log('👁️ Blur event:', input.id, '=', value);
        const parameter = input.getAttribute('data-variable-parameter');
        const subjectType = input.getAttribute('data-subject-type');
        updateVariablePreview(parameter, value, subjectType);
    });
    
    // Also listen for input events on the actual input field
    const actualInput = selectizeInstance.$input[0];
    if (actualInput) {
        actualInput.addEventListener('input', function() {
            console.log('⌨️ Input event:', input.id, '=', this.value);
            const parameter = input.getAttribute('data-variable-parameter');
            const subjectType = input.getAttribute('data-subject-type');
            updateVariablePreview(parameter, this.value, subjectType);
        });
        
        // Also listen for change events on the actual input
        actualInput.addEventListener('change', function() {
            console.log('🔄 Change event:', input.id, '=', this.value);
            const parameter = input.getAttribute('data-variable-parameter');
            const subjectType = input.getAttribute('data-subject-type');
            updateVariablePreview(parameter, this.value, subjectType);
        });
    }
    
    // Additional fallback: Monitor the selectize control input
    const controlInput = selectizeInstance.$control_input[0];
    if (controlInput) {
        controlInput.addEventListener('input', function() {
            console.log('🎛️ Control input event:', input.id, '=', this.value);
            const parameter = input.getAttribute('data-variable-parameter');
            const subjectType = input.getAttribute('data-subject-type');
            updateVariablePreview(parameter, this.value, subjectType);
        });
        
        controlInput.addEventListener('change', function() {
            console.log('🎛️ Control change event:', input.id, '=', this.value);
            const parameter = input.getAttribute('data-variable-parameter');
            const subjectType = input.getAttribute('data-subject-type');
            updateVariablePreview(parameter, this.value, subjectType);
        });
    }
}

function updateVariablePreview(parameter, value, subjectType) {
    if (!value || value.trim() === '') {
        console.log('⚠️ Empty value, skipping update for:', parameter);
        return;
    }
    
    console.log('💾 Storing variable:', parameter, '=', value);
    
    // Store the variable value for preview
    window.variableValues[parameter] = value;
    
    // Update the preview
    updatePreviewWithVariables();
}

function updatePreviewWithVariables() {
    console.log('🔄 Updating preview with variables:', window.variableValues);
    
    if (!window.variableValues || Object.keys(window.variableValues).length === 0) {
        console.log('⚠️ No variables to update');
        return;
    }
    
    // Find all variable placeholders in the preview
    const variablePlaceholders = document.querySelectorAll('.variable-placeholder');
    console.log('🎯 Found', variablePlaceholders.length, 'variable placeholders');
    
    variablePlaceholders.forEach(function(placeholder, index) {
        const variableIndex = placeholder.getAttribute('data-variable-index');
        console.log('🔍 Checking placeholder', index + 1, 'with index:', variableIndex);
        
        // Find the corresponding variable value
        Object.keys(window.variableValues).forEach(function(param) {
            const paramIndex = param.replace(/[^\d]/g, '');
            console.log('🔢 Comparing param index:', paramIndex, 'with variable index:', variableIndex);
            
            if (paramIndex === variableIndex) {
                console.log('✅ Updating placeholder with value:', window.variableValues[param]);
                // Update the placeholder with the actual value
                placeholder.textContent = window.variableValues[param];
                placeholder.classList.add('updated');
                
                // Add a tooltip to show it's been updated
                placeholder.title = 'Variable updated: ' + window.variableValues[param];
            }
        });
    });
    
    // Also update location variables if they exist
    const locationElements = document.querySelectorAll('.lw-whatsapp-location-meta');
    locationElements.forEach(function(element) {
        let text = element.innerHTML;
        Object.keys(window.variableValues).forEach(function(param) {
            if (param.includes('location_')) {
                const locationType = param.replace('location_', '');
                const placeholder = '@{{' + locationType + '}}';
                text = text.replace(new RegExp(placeholder, 'g'), window.variableValues[param]);
            }
        });
        element.innerHTML = text;
    });
}

// Fallback event handlers for direct DOM events
window.handleVariableChange = function(element) {
    console.log('🔄 Direct change event:', element.id, '=', element.value);
    const parameter = element.getAttribute('data-variable-parameter');
    const subjectType = element.getAttribute('data-subject-type');
    updateVariablePreview(parameter, element.value, subjectType);
};

window.handleVariableInput = function(element) {
    console.log('⌨️ Direct input event:', element.id, '=', element.value);
    const parameter = element.getAttribute('data-variable-parameter');
    const subjectType = element.getAttribute('data-subject-type');
    updateVariablePreview(parameter, element.value, subjectType);
};

// Debug function to manually test
window.testVariablePreview = function(parameter, value) {
    console.log('🧪 Testing variable preview:', parameter, '=', value);
    updateVariablePreview(parameter, value, 'test');
};

// Function to show debug panel (call this in browser console if needed)
window.showDebugPanel = function() {
    const debugPanel = document.getElementById('debugPanel');
    if (debugPanel) {
        debugPanel.style.display = 'block';
    }
};

// Function to manually sync all variable values (call this if needed)
window.syncAllVariables = function() {
    console.log('🔄 Manually syncing all variable values...');
    const variableInputs = document.querySelectorAll('[data-variable-parameter]');
    
    variableInputs.forEach(function(input) {
        const parameter = input.getAttribute('data-variable-parameter');
        const subjectType = input.getAttribute('data-subject-type');
        let currentValue = '';
        
        // Try to get value from selectize
        if (input.selectize) {
            currentValue = input.selectize.getValue() || '';
        } else if (window.$ && $(input).data('selectize')) {
            currentValue = $(input).data('selectize').getValue() || '';
        } else {
            currentValue = input.value || '';
        }
        
        if (currentValue) {
            console.log('🔄 Syncing variable:', parameter, '=', currentValue);
            updateVariablePreview(parameter, currentValue, subjectType);
        }
    });
};

// Additional fallback: Use MutationObserver to catch selectize initialization
if (window.MutationObserver) {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('selectize-input')) {
                        console.log('🔍 Selectize input detected, re-initializing...');
                        setTimeout(initializeVariablePreview, 100);
                    }
                });
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

// Final fallback: Poll for selectize instances
let pollCount = 0;
const maxPolls = 20;
const pollInterval = setInterval(function() {
    pollCount++;
    console.log('🔄 Polling for selectize instances...', pollCount);
    
    const variableInputs = document.querySelectorAll('[data-variable-parameter]');
    let foundSelectize = false;
    
    variableInputs.forEach(function(input) {
        if (input.selectize || (window.$ && $(input).data('selectize'))) {
            foundSelectize = true;
            console.log('✅ Found selectize instance for:', input.id);
        }
    });
    
    if (foundSelectize || pollCount >= maxPolls) {
        clearInterval(pollInterval);
        if (foundSelectize) {
            initializeVariablePreview();
        }
    }
}, 500);

// Additional monitoring: Check for value changes periodically
let lastValues = {};
setInterval(function() {
    const variableInputs = document.querySelectorAll('[data-variable-parameter]');
    
    variableInputs.forEach(function(input) {
        const parameter = input.getAttribute('data-variable-parameter');
        let currentValue = '';
        
        // Try to get value from selectize
        if (input.selectize) {
            currentValue = input.selectize.getValue() || '';
        } else if (window.$ && $(input).data('selectize')) {
            currentValue = $(input).data('selectize').getValue() || '';
        } else {
            currentValue = input.value || '';
        }
        
        // Check if value has changed
        if (lastValues[parameter] !== currentValue && currentValue !== '') {
            console.log('🔄 Value change detected via polling:', parameter, '=', currentValue);
            const subjectType = input.getAttribute('data-subject-type');
            updateVariablePreview(parameter, currentValue, subjectType);
            lastValues[parameter] = currentValue;
        }
    });
}, 1000); // Check every second
</script>

<!-- Debug Panel (remove in production) -->
<div class="alert alert-info mb-3" style="display: none;" id="debugPanel">
    <h6>🔧 Debug Panel</h6>
    <button class="btn btn-sm btn-primary" onclick="testVariablePreview('field_1', 'Test Value')">Test Variable 1</button>
    <button class="btn btn-sm btn-primary" onclick="testVariablePreview('field_2', 'Another Test')">Test Variable 2</button>
    <button class="btn btn-sm btn-warning" onclick="syncAllVariables()">Sync All Variables</button>
    <button class="btn btn-sm btn-secondary" onclick="console.log('Variable Values:', window.variableValues)">Log Variables</button>
    <button class="btn btn-sm btn-secondary" onclick="console.log('Placeholders:', document.querySelectorAll('.variable-placeholder'))">Log Placeholders</button>
</div>

<div class="row">
    @foreach ($parameters as $parameter)
@php
$parameterIndex = strtr($parameter, [
        'field_' => '',
        'button_' => '',
]);
@endphp
<div class="col-md-12 col-lg-6 card border-0">
    @if ($subjectType == 'button')
        @isset($buttonItems[$iterationIndex])
            @if($buttonItems[$iterationIndex]['type'] == 'URL')
            {{ $buttonItems[$iterationIndex]['text'] }}  - {{ $buttonItems[$iterationIndex]['url'] }}
            @elseif($buttonItems[$iterationIndex]['type'] == 'COPY_CODE')
            {{ $buttonItems[$iterationIndex]['text'] }}
            @endif
        @endisset
    @endif
    <x-lw.input-field  placeholder="{{  __tr('Choose or Write your own') }}" type="selectize" data-lw-plugin="lwSelectize" id="lwField_{{ $parameter }}"
        name="{{ $parameter }}" data-form-group-class="" data-selected=" " :label="is_numeric( $parameterIndex) ? __tr('Assign content for @{{__messageParameter__}} variable', [
                '__messageParameter__' => '<strong>'. ($subjectType == 'button' ? '1' : Str::of(Str::title($parameterIndex))->replace(
    '_', ' '
)) .'</strong>'
            ]) : __tr('Assign content for __messageParameter__ variable', [
                '__messageParameter__' => '<strong>'. ($subjectType == 'button' ? '1' : Str::of(Str::title($parameterIndex))->replace(
    '_', ' '
)) .'</strong>'
            ])"  data-create="true" data-variable-parameter="{{ $parameter }}" data-subject-type="{{ $subjectType }}" onchange="handleVariableChange(this)" oninput="handleVariableInput(this)">
        <x-slot name="selectOptions">
            <option value="">{{ __tr('Select or Type your own') }}</option>
            <optgroup label="{{ __tr('Assign from User Contact Details') }}">
                @foreach ($contactDataMaps as $contactDataMapKey => $contactDataMapValue)
                    <option value="{{ $contactDataMapKey }}">{{ $contactDataMapValue }}</option>
                @endforeach
            </optgroup>
            <optgroup label="{{ __tr('or custom values') }}">
                <option disabled="">{{  __tr('type to use custom value') }}</option>
            </optgroup>
        </x-slot>
    </x-lw.input-field>
</div>
@endforeach
</div>