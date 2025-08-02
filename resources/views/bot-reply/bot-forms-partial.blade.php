<!-- Modern Bot Forms Styling -->
<style>
/* Modern Bot Forms - Override app.blade.php styling */
.bot-forms-modern {
    --primary-color: #10b981;
    --primary-hover:rgb(0, 163, 108);
    --secondary-color: #64748b;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #3b82f6;
    --light-bg: #f8fafc;
    --border-color: #e2e8f0;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
}

/* Modal Overrides */
.bot-forms-modern .modal-content {
    border: none;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    background: #ffffff;
}

.bot-forms-modern .modal-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
    color: white;
    border-bottom: none;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    padding: 1.5rem;
}

.bot-forms-modern .modal-header .modal-title {
    font-weight: 600;
    font-size: 1.25rem;
}

.bot-forms-modern .modal-header .close {
    color: white;
    opacity: 0.8;
    transition: opacity 0.2s;
}

.bot-forms-modern .modal-header .close:hover {
    opacity: 1;
}

.bot-forms-modern .modal-body {
    padding: 2rem;
    background: var(--light-bg);
}

.bot-forms-modern .modal-footer {
    background: white;
    border-top: 1px solid var(--border-color);
    padding: 1.5rem 2rem;
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}

/* Form Elements */
.bot-forms-modern .form-group {
    margin-bottom: 1.5rem;
}

.bot-forms-modern .form-label {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.bot-forms-modern .form-control {
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    background: white;
}

.bot-forms-modern .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgb(99 102 241 / 0.1);
    outline: none;
}

.bot-forms-modern textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

/* Fieldset and Legend */
.bot-forms-modern fieldset {
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    background: white;
    position: relative;
}

.bot-forms-modern legend {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    position: absolute;
    top: -0.75rem;
    left: 1rem;
    margin: 0;
}

/* Alerts */
.bot-forms-modern .alert {
    border: none;
    border-radius: var(--radius-md);
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    position: relative;
    overflow: hidden;
}

.bot-forms-modern .alert::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
}

.bot-forms-modern .alert-info {
    background: rgb(59 130 246 / 0.1);
    color: var(--info-color);
    border-left: 4px solid var(--info-color);
}

.bot-forms-modern .alert-warning {
    background: rgb(245 158 11 / 0.1);
    color: var(--warning-color);
    border-left: 4px solid var(--warning-color);
}

.bot-forms-modern .alert-success {
    background: rgb(16 185 129 / 0.1);
    color: var(--success-color);
    border-left: 4px solid var(--success-color);
}

.bot-forms-modern .alert-danger {
    background: rgb(239 68 68 / 0.1);
    color: var(--danger-color);
    border-left: 4px solid var(--danger-color);
}

/* Buttons */
.bot-forms-modern .btn {
    border-radius: var(--radius-md);
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.bot-forms-modern .btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
    color: white;
    box-shadow: var(--shadow-sm);
}

.bot-forms-modern .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.bot-forms-modern .btn-secondary {
    background: var(--secondary-color);
    color: white;
}

.bot-forms-modern .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
}

/* Help Text */
.bot-forms-modern .help-text {
    background: var(--light-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 1rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-top: 0.75rem;
}

.bot-forms-modern .help-text code {
    background: rgb(99 102 241 / 0.1);
    color: var(--primary-color);
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 500;
}

/* Radio and Checkbox */
.bot-forms-modern input[type="radio"],
.bot-forms-modern input[type="checkbox"] {
    accent-color: var(--primary-color);
    transform: scale(1.1);
}

.bot-forms-modern .form-check-label {
    font-weight: 500;
    color: var(--text-primary);
    margin-left: 0.5rem;
}

/* Selectize Overrides */
.bot-forms-modern .selectize-control {
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
}

.bot-forms-modern .selectize-control.focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgb(99 102 68 / 0.1);
}

/* File Upload */
.bot-forms-modern .filepond {
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-md);
    background: var(--light-bg);
    transition: all 0.2s ease;
}

.bot-forms-modern .filepond:hover {
    border-color: var(--primary-color);
    background: rgb(99 102 241 / 0.05);
}

/* Responsive Design */
@media (max-width: 768px) {
    .bot-forms-modern .modal-body {
        padding: 1rem;
    }
    
    .bot-forms-modern .modal-footer {
        padding: 1rem;
    }
    
    .bot-forms-modern fieldset {
        padding: 1rem;
    }
}

/* Animation */
.bot-forms-modern .form-control,
.bot-forms-modern .btn,
.bot-forms-modern .alert {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom Scrollbar */
.bot-forms-modern ::-webkit-scrollbar {
    width: 8px;
}

.bot-forms-modern ::-webkit-scrollbar-track {
    background: var(--light-bg);
    border-radius: var(--radius-sm);
}

.bot-forms-modern ::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: var(--radius-sm);
}

.bot-forms-modern ::-webkit-scrollbar-thumb:hover {
    background: var(--secondary-color);
}

/* Additional Modern Enhancements */
.bot-forms-modern .form-control::placeholder {
    color: var(--text-secondary);
    opacity: 0.7;
}

.bot-forms-modern .btn:active {
    transform: translateY(0);
}

.bot-forms-modern .modal-dialog {
    max-width: 800px;
}

.bot-forms-modern .modal-content {
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

/* Focus states */
.bot-forms-modern .form-control:focus,
.bot-forms-modern .btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgb(99 102 241 / 0.1);
}

/* Hover effects */
.bot-forms-modern .btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

/* Loading states */
.bot-forms-modern .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Success animation */
.bot-forms-modern .form-control.is-valid {
    border-color: var(--success-color);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2310b981' d='m2.3 6.73.94-.94 2.89 2.89 2.89-2.89.94.94L4.82 9.56z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

/* Error animation */
.bot-forms-modern .form-control.is-invalid {
    border-color: var(--danger-color);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23ef4444'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 2.4 2.4m0-2.4L5.8 7'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
</style>

<!-- Add New Advance Bot Reply Modal -->
<x-lw.modal id="lwAddNewAdvanceBotReply" modal-dialog-class="modal-lg bot-forms-modern" :header="__tr('Add New Bot Reply')" :hasForm="true">
    <!--  Add New Bot Reply Form -->
    <x-lw.form x-data="{triggerType:'',headerType:'',interactiveButtonType:'button'}" id="lwAddNewAdvanceBotReplyForm"
        :action="route('vendor.bot_reply.write.create')"
        :data-callback-params="['modalId' => '#lwAddNewAdvanceBotReply', 'datatableId' => '#lwBotReplyList']"
        data-callback="appFuncs.modelSuccessCallback">
        <!-- form body -->
        <div class="lw-form-modal-body">
            <!-- form fields form fields -->
            <input type="hidden" name="message_type" :value="isAdvanceBot">
            <template x-if="botFlowUid">
                <input type="hidden" name="bot_flow_uid" :value="botFlowUid">
            </template>
            <!-- Name -->
            <x-lw.input-field type="text" id="lwAdvanceBotNameField" data-form-group-class="form-group" :label="__tr('Name')"
                name="name" required="true" />
            <!-- /Name -->
            <fieldset>
           
                <!-- Reply_Text -->
                <div class="form-group" x-show="isAdvanceBot == 'simple' || isAdvanceBot == 'interactive' || isAdvanceBot == 'question'">
                    <label for="lwReplyTextField">
                        <span x-show="isAdvanceBot == 'question'">{{ __tr('Question Text') }}</span>
                        <span x-show="isAdvanceBot != 'question'">{{ __tr('Reply Text') }}</span>
                    </label>
                    <textarea cols="10" rows="3" id="lwAdvanceBotReplyTextField" class="lw-form-field form-control"
                        x-bind:placeholder="isAdvanceBot == 'question' ? '{{ __tr('What is your address?') }}' : '{{ __tr('Add your main message body text here') }}'"
                        name="reply_text" x-bind:required="isAdvanceBot == 'simple' || isAdvanceBot == 'interactive' || isAdvanceBot == 'question'"></textarea>
                        <div class="help-text">{{  __tr('You are free to use following dynamic variables for reply text, which will get replaced with contact\'s concerned field value.') }} <div><code>{{ implode(' ', $dynamicFields) }}</code></div></div>
                </div>
                <!-- /Reply_Text -->

                <!-- Ask Question Fields -->
                <div x-show="isAdvanceBot == 'question'">
                    <fieldset>
                        <legend>{{ __tr('Question Settings') }}</legend>

                     

                        <x-lw.input-field type="selectize" id="lwQuestionStoreField" data-form-group-class="form-group"
                            :label="__tr('Map to Contact Custom Field')" name="question_store_field" required="true">
                            <x-slot name="selectOptions">
                                <option value="">{{ __tr('Select a custom field...') }}</option>
                                @if(isset($contactCustomFields))
                                    @foreach($contactCustomFields as $customField)
                                        <option value="{{ $customField->input_name }}" data-field-type="{{ ucfirst($customField->input_type) }}">{{ $customField->input_name }} ({{ ucfirst($customField->input_type) }})</option>
                                    @endforeach
                                @endif
                            </x-slot>
                        </x-lw.input-field>
                        <div id="lwQuestionFieldTypeInfo" class="help-text mb-2" style="display:none;"></div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var select = document.getElementById('lwQuestionStoreField');
                                var infoDiv = document.getElementById('lwQuestionFieldTypeInfo');
                                if (select) {
                                    function updateFieldTypeInfo() {
                                        var type = select.options[select.selectedIndex]?.getAttribute('data-field-type');
                                        if (select.value && type) {
                                            infoDiv.textContent = 'Selected field type: ' + type;
                                            infoDiv.style.display = '';
                                        } else {
                                            infoDiv.textContent = '';
                                            infoDiv.style.display = 'none';
                                        }
                                    }
                                    select.addEventListener('change', updateFieldTypeInfo);
                                    updateFieldTypeInfo();
                                }
                            });
                        </script>

                        
                    </fieldset>
                </div>
                <!-- /Ask Question Fields -->

                <!-- Goto Node Fields -->
                <div x-show="isAdvanceBot == 'goto'">
                    <fieldset>
                        <legend>{{ __tr('Goto Node Settings') }}</legend>


                        <x-lw.input-field type="selectize" id="lwGotoTargetNode" data-form-group-class="form-group"
                            :label="__tr('Target Node')" name="goto_target_node" required="true"
                            data-callback="onGotoTargetNodeSelected">
                            <x-slot name="selectOptions">
                                <option value="">{{ __tr('Select a node to redirect to...') }}</option>
                                @if(isset($flowBots))
                                    @foreach($flowBots as $flowBot)
                                        <option value="{{ $flowBot->_uid }}">{{ $flowBot->name }}</option>
                                    @endforeach
                                @endif
                            </x-slot>
                        </x-lw.input-field>

                      
                    </fieldset>
                </div>
                <!-- /Goto Node Fields -->

                <!-- Wait Node Fields -->
                <div x-show="isAdvanceBot == 'wait'">
                    <fieldset>
                        <legend>{{ __tr('Wait Node Settings') }}</legend>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-2"></i>
                            {{ __tr('A wait node pauses the conversation flow for a specified amount of time before continuing to the next node.') }}
                        </div>

                        <x-lw.input-field 
                            type="number" 
                            id="lwWaitTime" 
                            data-form-group-class="form-group" 
                            :label="__tr('Wait Time (seconds)')" 
                            name="wait_delay_seconds" 
                            min="1" 
                            max="3600" 
                            step="1"
                            value="5"
                            required="true"
                            placeholder="Enter wait time in seconds"
                        />

                        <div class="form-group">
                            <label for="lwWaitMessage">{{ __tr('Optional Message (displayed while waiting)') }}</label>
                            <textarea 
                                id="lwWaitMessage" 
                                class="form-control" 
                                name="wait_message" 
                                rows="3"
                                placeholder="{{ __tr('Please wait while we process your request...') }}"
                            ></textarea>
                            <div class="help-text">
                                <strong>{{ __tr('Note:') }}</strong> {{ __tr('This message will be shown to the user while waiting. You can use dynamic variables like:') }} 
                                <code>{{ implode(' ', $dynamicFields) }}</code>
                            </div>

                            <!-- Hidden reply_text field with default value for Wait Node -->
                            <!-- The backend uses wait_message field, not wait_reply_text -->

                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle me-2"></i>
                                <strong>{{ __tr('Important:') }}</strong> 
                                {{ __tr('The actual wait time may vary slightly based on system load and other factors.') }}
                            </div>
                        </div>
                    </fieldset>
                </div>
                <!-- /Wait Node Fields -->

                <!-- Team Assignment Node Fields -->
                <div x-show="isAdvanceBot == 'team_assignment'">
                    <fieldset>
                        <legend>{{ __tr('Team Assignment Settings') }}</legend>

                       

                        <x-lw.input-field type="selectize" id="lwTeamMemberSelect" data-form-group-class="form-group"
                            :label="__tr('Assign to Team Member')" name="assigned_team_member" required="true">
                            <x-slot name="selectOptions">
                                <option value="">{{ __tr('Select a team member...') }}</option>
                                @if(isset($vendorTeamMembers))
                                    @foreach($vendorTeamMembers as $teamMember)
                                        <option value="{{ $teamMember->_uid }}">{{ $teamMember->full_name }} ({{ $teamMember->email }})</option>
                                    @endforeach
                                @endif
                            </x-slot>
                        </x-lw.input-field>

                        <div class="form-group">
                            <label for="lwTeamAssignmentMessage">{{ __tr('Assignment Message (optional)') }}</label>
                            <textarea
                                id="lwTeamAssignmentMessage"
                                class="form-control"
                                name="assignment_message"
                                rows="3"
                                placeholder="{{ __tr('This conversation has been assigned to a team member...') }}"
                            ></textarea>
                            <div class="help-text">
                                <strong>{{ __tr('Note:') }}</strong> {{ __tr('This message will be shown to the user when the assignment happens. You can use dynamic variables like:') }}
                                <code>{{ implode(' ', $dynamicFields) }}</code>
                            </div>

<!-- Hidden reply_text field with default value for Team Assignment Node -->
<!-- The backend uses assignment_message field, not team_assignment_reply_text -->

                        </div>
                    </fieldset>
                </div>
                <!-- /Team Assignment Node Fields -->

                <!-- Webhook Node Fields -->
                <div x-show="isAdvanceBot == 'webhook'">
                    <fieldset>
                        <legend>{{ __tr('Webhook Settings') }}</legend>

                      

                        <!-- Webhook URL -->
                        <x-lw.input-field type="url" id="lwWebhookUrl" data-form-group-class=""
                            :label="__tr('Webhook URL')" name="webhook_url" required="true"
                            placeholder="https://api.example.com/webhook" />

                        <!-- HTTP Method -->
                        <x-lw.input-field type="selectize" id="lwWebhookMethod" data-form-group-class=""
                            :label="__tr('HTTP Method')" name="http_method" required="true">
                            <x-slot name="selectOptions">
                                <option value="POST" selected>POST</option>
                                <option value="GET">GET</option>
                                <option value="PUT">PUT</option>
                                <option value="PATCH">PATCH</option>
                                <option value="DELETE">DELETE</option>
                            </x-slot>
                        </x-lw.input-field>

                        <!-- Request Body Template -->
                        <div class="form-group">
                            <label for="lwWebhookRequestBody">{{ __tr('Request Body (JSON)') }}</label>
                            <textarea id="lwWebhookRequestBody" class="form-control" name="request_body" rows="6"
                                placeholder='{"message": "{full_name} sent: {user_input}", "phone": "{phone_number}", "email": "{email}"}'></textarea>
                            <div class="help-text my-3 border p-3">
                                <strong>{{ __tr('Note:') }}</strong> {{ __tr('You can use dynamic variables in the request body:') }}
                                <code>{{ implode(' ', $dynamicFields) }}</code>
                                <br><strong>{{ __tr('Special variables:') }}</strong>
                                <code>{user_input}</code> {{ __tr('(user\'s last input)') }}
                            </div>
                        </div>

                        <!-- Timeout -->
                        <x-lw.input-field type="number" id="lwWebhookTimeout" data-form-group-class=""
                            :label="__tr('Timeout (seconds)')" name="timeout" value="30" min="5" max="120" />

                        <!-- Success Message -->
                        <div class="form-group">
                            <label for="lwWebhookSuccessMessage">{{ __tr('Success Message') }}</label>
                            <textarea id="lwWebhookSuccessMessage" class="form-control" name="success_message" rows="3"
                                placeholder="{{ __tr('Request processed successfully') }}"></textarea>
                            <div class="help-text my-3 border p-3">
                                <strong>{{ __tr('Note:') }}</strong> {{ __tr('This message will be shown when the webhook succeeds. You can use dynamic variables and response data.') }}
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div class="form-group">
                            <label for="lwWebhookErrorMessage">{{ __tr('Error Message') }}</label>
                            <textarea id="lwWebhookErrorMessage" class="form-control" name="error_message" rows="3"
                                placeholder="{{ __tr('Request failed. Please try again later.') }}"></textarea>
                        </div>

                        <!-- Response Mapping -->
                        <div class="form-group" x-data="{responseMappings: []}">
                            <label>{{ __tr('Response Variable Mapping (Optional)') }}</label>
                            <div class="help-text mb-3 border p-3">
                                <strong>{{ __tr('Map webhook response data to variables:') }}</strong><br>
                                {{ __tr('Use dot notation for nested values (e.g., "data.user.name" to access response.data.user.name)') }}
                            </div>

                            <template x-for="(mapping, index) in responseMappings" :key="index">
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" :name="'response_mapping[' + index + '][source_path]'"
                                            placeholder="data.result" x-model="mapping.source_path">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" :name="'response_mapping[' + index + '][target_variable]'"
                                            placeholder="webhook_result" x-model="mapping.target_variable">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger" @click="responseMappings.splice(index, 1)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <button type="button" class="btn btn-sm btn-secondary"
                                @click="responseMappings.push({source_path: '', target_variable: ''})">
                                <i class="fa fa-plus"></i> {{ __tr('Add Response Mapping') }}
                            </button>
                        </div>

                        <!-- Hidden reply_text field with default value for Webhook Node -->
                        <!-- The backend uses success_message field, not webhook_reply_text -->
                    </fieldset>
                </div>
                <!-- /Webhook Node Fields -->

                <!-- Custom Field Node Fields -->
                <div x-show="isAdvanceBot == 'custom_field'">
                    <fieldset>
                        <legend>{{ __tr('Custom Field Settings') }}</legend>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            {{ __tr('A custom field node asks users for specific information and stores their response in a selected custom field.') }}
                        </div>

                        <x-lw.input-field type="selectize" id="lwCustomFieldSelect" data-form-group-class=""
                            :label="__tr('Select Custom Field')" name="custom_field_id" required="true">
                            <x-slot name="selectOptions">
                                <option value="">{{ __tr('Select a custom field...') }}</option>
                                @if(isset($contactCustomFields))
                                    @foreach($contactCustomFields as $customField)
                                        <option value="{{ $customField->_id }}" data-field-name="{{ $customField->input_name }}">{{ $customField->input_name }} ({{ ucfirst($customField->input_type) }})</option>
                                    @endforeach
                                @endif
                            </x-slot>
                        </x-lw.input-field>

                        <div class="form-group">
                            <label for="lwCustomFieldQuestion">{{ __tr('Question Text') }}</label>
                            <textarea
                                id="lwCustomFieldQuestion"
                                class="form-control"
                                name="question_text"
                                rows="3"
                                placeholder="{{ __tr('Please provide your information...') }}"
                                required
                            ></textarea>
                            <div class="help-text my-3 border p-3">
                                <strong>{{ __tr('Note:') }}</strong> {{ __tr('This question will be shown to the user. You can use dynamic variables like:') }}
                                <code>{{ implode(' ', $dynamicFields) }}</code>
                            </div>

                            <!-- Hidden reply_text field with default value for Custom Field Node -->
                            <!-- The backend uses question_text field, not custom_field_reply_text -->

                            <!-- Hidden field to store custom field name -->
                            <input type="hidden" name="custom_field_name" id="lwCustomFieldNameHidden" value="" />

                            <script>
                                // Handle custom field selection to populate field name
                                $(document).on('change', '#lwCustomFieldSelect', function() {
                                    var selectedOption = $(this).find('option:selected');
                                    var fieldName = selectedOption.data('field-name') || '';
                                    $('#lwCustomFieldNameHidden').val(fieldName);
                                });
                            </script>

                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i>
                                <strong>{{ __tr('How it works:') }}</strong>
                                {{ __tr('The user\'s response will be automatically saved to the selected custom field and can be used in subsequent messages using the field name as a variable.') }}
                            </div>
                        </div>
                    </fieldset>
                </div>
                <!-- /Custom Field Node Fields -->

                <!-- Stay in Session Node Fields -->
                <div x-show="isAdvanceBot == 'stay_in_session'">
                    <fieldset>
                        <legend>{{ __tr('Stay in Session Settings') }}</legend>

                       

                        <div class="form-group">
                            <label for="lwStayInSessionMessage">{{ __tr('Session Message (Optional)') }}</label>
                            <textarea
                                id="lwStayInSessionMessage"
                                class="form-control"
                                name="session_message"
                                rows="3"
                                placeholder="{{ __tr('Session will remain active. You can continue interacting...') }}"
                            ></textarea>
                            <div class="help-text my-3 border p-3">
                                <strong>{{ __tr('Note:') }}</strong> {{ __tr('This message will be shown to indicate the session is staying active. You can use dynamic variables like:') }}
                                <code>{{ implode(' ', $dynamicFields) }}</code>
                            </div>
                        </div>

                        <!-- Hidden reply_text field with empty value for Stay in Session Node -->
                        <!-- Only include this field when actually creating a stay_in_session node -->
                        <template x-if="isAdvanceBot == 'stay_in_session'">
                            <input type="hidden" name="reply_text" value="" />
                        </template>

                      

                       
                    </fieldset>
                </div>
                <!-- /Stay in Session Node Fields -->

                <!-- WhatsApp Template Node Fields -->
                <div x-show="isAdvanceBot == 'whatsapp_template'">
                    <fieldset>
                        <legend>{{ __tr('WhatsApp Template Settings') }}</legend>

                        <div class="form-group">
                            <label for="lwWhatsAppTemplateSelect">{{ __tr('Select Template') }}</label>
                            <select id="lwWhatsAppTemplateSelect" name="whatsapp_template_id" class="form-control" required>
                                <option value="">{{ __tr('Select a WhatsApp template...') }}</option>
                                @if(isset($whatsAppTemplates))
                                    @foreach($whatsAppTemplates as $template)
                                        <option value="{{ $template->_id }}">{{ $template->template_name }} ({{ $template->language }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Hidden reply_text field with default value for WhatsApp Template Node -->
                        <!-- The backend sets a default reply_text automatically, no hidden field needed -->

                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i>
                            <strong>{{ __tr('How it works:') }}</strong>
                            {{ __tr('The node will automatically fetch and display all approved WhatsApp templates. Users can select a template by number or name, and the selection will be stored for further processing.') }}
                        </div>

                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>{{ __tr('Important:') }}</strong>
                            {{ __tr('Only approved WhatsApp templates will be displayed. Make sure you have approved templates in your WhatsApp Business Account.') }}
                        </div>
                    </fieldset>
                </div>
                <!-- /WhatsApp Template Node Fields -->

                <div x-show="isAdvanceBot == 'interactive' || isAdvanceBot == 'media'">
                        {{-- select type --}}
                    <div x-show="isAdvanceBot == 'interactive'">
                    <x-lw.input-field x-init="$watch('headerType', function() {
                        if((interactiveButtonType == 'list') && !_.includes(['','text'],headerType)) {
                            interactiveButtonType = 'button';
                        }
                    })" x-model="headerType" type="selectize" id="lwAdvanceBotHeaderTypeField"  data-form-group-class="" data-selected=" " :label="__tr('Header Type (optional)')" name="header_type" >
                        <x-slot name="selectOptions">
                            <option value="">{{  __tr('None') }}</option>
                            <option value="text">{{  __tr('Text') }}</option>
                            <option value="image">{{  __tr('Image') }}</option>
                            <option value="video">{{  __tr('Video') }}</option>
                            <option value="document">{{  __tr('Document') }}</option>
                        </x-slot>
                    </x-lw.input-field>
                    </div>
                    <div x-show="isAdvanceBot == 'media'">
                        <x-lw.input-field x-model="headerType" type="selectize" id="lwMediaHeaderType"
                        data-form-group-class="" data-selected=" " :label="__tr('Header Type')" name="media_header_type" >
                            <x-slot name="selectOptions">
                                <option value="">{{  __tr('None') }}</option>
                                <option value="image">{{  __tr('Image') }}</option>
                                <option value="video">{{  __tr('Video') }}</option>
                                <option value="document">{{  __tr('Document') }}</option>
                                <option value="audio">{{  __tr('Audio') }}</option>
                            </x-slot>
                        </x-lw.input-field>
                    </div>
                    <div class="my-3">
                    {{-- document --}}
                    <div x-show="headerType == 'document'" class="form-group col-sm-12">
                    <input id="lwDocumentMediaFilepond" type="file" data-allow-revert="true"
                        data-label-idle="{{ __tr('Select Document') }}" class="lw-file-uploader" data-instant-upload="true"
                        data-action="<?= route('media.upload_temp_media', 'whatsapp_document') ?>" id="lwDocumentField" data-file-input-element="#lwMediaFileName" data-allowed-media='<?= getMediaRestriction('whatsapp_document') ?>' />
                    </div>
                    {{-- image --}}
                    <div x-show="headerType == 'image'" class="form-group col-sm-12">
                    <input id="lwImageMediaFilepond" type="file" data-allow-revert="true"
                        data-label-idle="{{ __tr('Select Image') }}" class="lw-file-uploader" data-instant-upload="true"
                        data-action="<?= route('media.upload_temp_media', 'whatsapp_image') ?>" id="lwImageField" data-file-input-element="#lwMediaFileName" data-allowed-media='<?= getMediaRestriction('whatsapp_image') ?>' />
                </div>
                {{-- video --}}
                <div x-show="headerType == 'video'" class="form-group col-sm-12">
                    <input id="lwVideoMediaFilepond" type="file" data-allow-revert="true"
                        data-label-idle="{{ __tr('Select Video') }}" class="lw-file-uploader" data-instant-upload="true"
                        data-action="<?= route('media.upload_temp_media', 'whatsapp_video') ?>" id="lwVideoField" data-file-input-element="#lwMediaFileName" data-allowed-media='<?= getMediaRestriction('whatsapp_video') ?>' />
                    </div>
                {{-- audio --}}
                    <div x-show="headerType == 'audio'" class="form-group col-sm-12">
                    <input id="lwAudioMediaFilepond" type="file" data-allow-revert="true"
                        data-label-idle="{{ __tr('Select Audio') }}" class="lw-file-uploader" data-instant-upload="true"
                        data-action="<?= route('media.upload_temp_media', 'whatsapp_audio') ?>" id="lwAudioField" data-file-input-element="#lwMediaFileName" data-allowed-media='<?= getMediaRestriction('whatsapp_audio') ?>' />
                    </div>
                </div>
                        <input id="lwMediaFileName" type="hidden" value="" name="uploaded_media_file_name" />
                        <div x-show="(isAdvanceBot == 'media') && headerType && (headerType != 'audio')">
                        <label for="lwMediaCaptionText">{{  __tr('Caption/Text') }}</label>
                        <textarea name="caption" id="lwCaptionField" class="form-control" rows="2"></textarea>
                        <div class="help-text my-3 border p-3">{{  __tr('You are free to use following dynamic variables for caption, which will get replaced with contact\'s concerned field value.') }} <div><code>{{ implode(' ', $dynamicFields) }}</code></div></div>
                    </div>
                        <div x-show="headerType == 'text'">
                        <x-lw.input-field type="text" id="lwAdvanceHeaderText" data-form-group-class=""
                            :label="__tr('Header Text')" name="header_text" required="true" />
                </div>
                    <div class="mt-4" x-show="isAdvanceBot == 'interactive'">
                       <strong> <input type="radio" name="interactive_type" x-model="interactiveButtonType" value="button" id="lwNewAdvanceBotReplyBtnType" checked> <label  class="mr-2" for="lwNewAdvanceBotReplyBtnType">{{  __tr('Reply Buttons') }}</label>
                        <input type="radio" name="interactive_type" x-model="interactiveButtonType" value="cta_url" id="lwNewAdvanceBotCtaUrlBtnType"> <label class="mr-2" for="lwNewAdvanceBotCtaUrlBtnType">{{  __tr('CTA URL Button') }}</label>
                        <input type="radio" x-bind:disabled="!_.includes(['','text'],headerType)" name="interactive_type" x-model="interactiveButtonType" value="list" id="lwNewAdvanceBotListMessageType"> <label x-bind:class="!_.includes(['','text'],headerType) ? 'text-muted' : ''" class="mr-2" for="lwNewAdvanceBotListMessageType">{{  __tr('List Message') }} <abbr x-show="!_.includes(['','text'],headerType)" title="{{  __tr('Header is optional, Only text type for header is supported for the list message') }}">?</abbr></label></strong>
                        <hr class="mt-1 mb-2">
                    <template x-if="interactiveButtonType == 'button'">
                        <div>
                            {{-- <h2>{{  __tr('Reply Buttons') }}</h2> --}}
                            <x-lw.input-field type="text" id="lwAdvanceButton1" data-form-group-class="" :label="__tr('Button 1 Label')" name="buttons[1]" required="true" />
                            <x-lw.input-field type="text" id="lwAdvanceButton2" data-form-group-class="" :label="__tr('Button 2 Label (optional)')" name="buttons[2]" />
                            <x-lw.input-field type="text" id="lwAdvanceButton3" data-form-group-class="" :label="__tr('Button 3 Label (optional)')" name="buttons[3]" />
                        </div>
                    </template>
                    <template x-if="interactiveButtonType == 'cta_url'">
                        <div>
                            {{-- <h2>{{  __tr('CTA URL Button') }}</h2> --}}
                            <x-lw.input-field type="text" id="lwCtaUrlButtonDisplayText" data-form-group-class="" :label="__tr('CTA Button Display Text')" name="button_display_text" required="true"/>
                            <x-lw.input-field type="text" id="lwCtaButtonUrl" data-form-group-class="" :label="__tr('CTA Button URL')" name="button_url" required="true" />
                        </div>
                    </template>
                    <template x-if="interactiveButtonType == 'list'">
                        <div x-data="{botListMessageSections:{}, 
                            get totalRows() {
                                let count = 0;
                                for (const sectionId in this.botListMessageSections) {
                                    if (this.botListMessageSections.hasOwnProperty(sectionId)) {
                                        count += Object.keys(this.botListMessageSections[sectionId].rows || {}).length;
                                    }
                                }
                                return count;
                            },
                            addListSection : function() {
                                let uniqueSectionId = __Utils.generateUniqueId('section_');
                                this.botListMessageSections[uniqueSectionId] = {
                                    index: _.size(this.botListMessageSections) + 1,
                                    id:uniqueSectionId,
                                    title:'',
                                    rows:{}
                                };
                            },
                            addListSectionRow : function(sectionId) {
                                if (this.totalRows >= 10) {
                                    alert('You can add a maximum of 10 options across all sections.');
                                    return;
                                }
                                let uniqueRowId = __Utils.generateUniqueId('row_');
                                this.botListMessageSections[sectionId]['rows'][uniqueRowId] = {
                                    index: _.size(this.botListMessageSections[sectionId]['rows']) + 1,
                                    id: uniqueRowId,
                                    row_id:'',title:'',
                                    description:''
                                };
                            },
                            deleteSection(sectionId){
                                delete this.botListMessageSections[sectionId];
                            },
                            deleteRow(sectionId, rowId){
                                delete this.botListMessageSections[sectionId]['rows'][rowId];
                            }
                        }" >
                            {{-- <h2>{{  __tr('List Message') }}</h2> --}}
                            <x-lw.input-field type="text" id="lwListButtonText" data-form-group-class="" :label="__tr('Button Label')" name="list_button_text" required="true" />
                            <template x-for="(botListMessageSection, index) in botListMessageSections">
                                <fieldset>
                                    <legend  class="py-1 px-2 mb-1"><small>{{  __tr('Section') }}</small></legend>
                                    <button @click.prevent="deleteSection(botListMessageSection.id)" class="btn btn-link float-right p-1 mt--4" type="button"><i class="fa fa-times text-danger"></i></button>
                                    <x-lw.input-field type="text" id="lwListMessageSectionTitle" data-form-group-class="" :label="__tr('Section Title')" x-bind:name="'sections[' + botListMessageSection.id+'][title]'" required="true" />
                                    <input type="hidden" x-bind:name="'sections[' + botListMessageSection.id+'][id]'" x-bind:value="botListMessageSection.id" required="true">
                                    <template x-for="botListMessageRow in botListMessageSection.rows">
                                    <fieldset>
                                        <legend  class="py-1 px-2 mb-1"><small>{{  __tr('Row') }}</small></legend>
                                        {{-- delete row btn --}}
                                        <button @click.prevent="deleteRow(botListMessageSection.id, botListMessageRow.id)" class="btn btn-link float-right p-1 mt--4" type="button"><i class="fa fa-times text-danger"></i></button>
                                        <input type="hidden" x-bind:name="'sections[' + botListMessageSection.id+'][rows]['+botListMessageRow.id+'][id]'" x-bind:value="botListMessageRow.id" required="true">
                                        {{-- row id --}}
                                        <x-lw.input-field type="text" x-bind:id="'lwListMessageRowId'+botListMessageSection.id+botListMessageRow.id" data-form-group-class="" :label="__tr('Row ID')" x-bind:name="'sections[' + botListMessageSection.id+'][rows]['+botListMessageRow.id+'][row_id]'" required="true" />
                                        {{-- row title --}}
                                        <x-lw.input-field type="text" x-bind:id="'lwListMessageRowTitle'+botListMessageSection.id+botListMessageRow.id" data-form-group-class="" :label="__tr('Row Title')" x-bind:name="'sections[' + botListMessageSection.id+'][rows]['+botListMessageRow.id+'][title]'" required="true" />
                                        {{-- row description --}}
                                        <div class="form-group">
                                            <label for="">{{ __tr('Row Description (optional)') }}</label>
                                            <textarea class="form-control" rows="2" x-bind:id="'lwListMessageRowDescription'+botListMessageSection.id+botListMessageRow.id" x-bind:name="'sections[' + botListMessageSection.id+'][rows]['+botListMessageRow.id+'][description]'"></textarea>
                                        </div>
                                    </fieldset>
                                </template>
                                <button type="button" class="btn btn-sm btn-light my-2" @click="addListSectionRow(botListMessageSection.id)" :disabled="totalRows >= 10">{{  __tr('Add Row') }}</button>
                                <div x-show="totalRows >= 10" class="text-danger small mt-1">{{ __tr('Maximum 10 options allowed across all sections.') }}</div>
                                </fieldset>
                            </template>
                            <button type="button" class="btn btn-sm btn-dark my-2" @click="addListSection()">{{  __tr('Add Section') }}</button>
                        </div>
                        </template>
                    </div>
                    {{-- footer text --}}
                    <div x-show="isAdvanceBot == 'interactive'">
                        <x-lw.input-field  type="text" id="lwAdvanceFooterText" data-form-group-class=""
                        :label="__tr('Footer Text (optional)')" name="footer_text" />
                    </div>
                </div>
                {{-- /reply --}}
            </fieldset>
            <template x-if="!botFlowUid">
                <div>
                    <!-- Trigger_Type -->
                <x-lw.input-field x-model="triggerType" type="selectize" id="lwAdvanceBotTriggerTypeField"
                data-form-group-class="" data-selected=" " :label="__tr('Trigger Type')" name="trigger_type"
                required="true">
                <x-slot name="selectOptions">
                    <option value="">{{ __tr('How do you want to trigger this message?') }}</option>
                    @foreach (configItem('bot_reply_trigger_types') as $replyBotTypeKey => $replyBotType)
                    <option value="{{ $replyBotTypeKey }}">{{ $replyBotType['title'] }} </option>
                    @endforeach
                </x-slot>
            </x-lw.input-field>
            <!-- /Trigger_Type -->
            @foreach (configItem('bot_reply_trigger_types') as $replyBotTypeKey => $replyBotType)
            <div x-show="triggerType == '{{ $replyBotTypeKey }}'" class="alert alert-dark">{{
                $replyBotType['description'] }}</div>
            @endforeach
            <!-- Reply_Trigger -->
            <div x-show="triggerType != 'welcome'">
                <x-lw.input-field placeholder="{{ __tr('What\'s that magic words that will trigger this reply?') }}" type="text" id="lwAdvanceBotReplyTriggerField" data-form-group-class=""
                    :label="__tr('Reply Trigger Subject')" name="reply_trigger" required="true" />
                    <div><small class="text-muted">{{  __tr('You can have comma separated multiple triggers.') }}</small></div>
            </div>
            <!-- /Reply_Trigger -->
            <div class="form-group pt-3">
                <x-lw.checkbox id="lwAddBotStatus" :offValue="0" value="1" :checked="true" name="status" data-lw-plugin="lwSwitchery" :label="__tr('Status')" />
             </div>
                </div>
            </template>
            <template x-if="botFlowUid">
                <input type="hidden" name="trigger_type" value="is">
            </template>
            <div class="my-4">
                <x-lw.checkbox id="lwValidateBotReply" :offValue="0" name="validate_bot_reply" value="1" data-lw-plugin="lwSwitchery" :label="__tr('Validate Bot Reply by Sending Test Message')" />
            </div>
        </div>
        <!-- form footer -->
        <div class="modal-footer">
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save me-2"></i>{{ __('Submit') }}
            </button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                <i class="fa fa-times me-2"></i>{{ __tr('Close') }}
            </button>
        </div>
    </x-lw.form>
    <!--/  Add New Bot Reply Form -->
</x-lw.modal>
<!--/ Add New Advance Bot Reply Modal -->
<!-- Edit Bot Reply Modal -->
<x-lw.modal id="lwEditBotReply" modal-dialog-class="modal-lg bot-forms-modern" :header="__tr('Edit Bot Reply')" :hasForm="true">
    <!--  Edit Bot Reply Form -->
    <x-lw.form id="lwEditBotReplyForm" :action="route('vendor.bot_reply.write.update')"
        :data-callback-params="['modalId' => '#lwEditBotReply', 'datatableId' => '#lwBotReplyList']"
        data-callback="appFuncs.modelSuccessCallback" x-data="{headerType:'', triggerType:'', interactiveButtonType:'button'}">
        <!-- form body -->
        <div id="lwEditBotReplyBody" class="lw-form-modal-body"></div>
        <script type="text/template" id="lwEditBotReplyBody-template">
            <div x-init="
                <% if(__tData.__data?.interaction_message?.header_type) { %>
                    headerType = '<%- __tData.__data?.interaction_message?.header_type %>';
                <% } else if(__tData.__data?.media_message?.header_type) { %>
                    headerType = '<%- __tData.__data?.media_message?.header_type %>';
                <% } else { %>
                    headerType = '';
                <% } %>

                <% if(__tData.__data?.interaction_message?.interactive_type) { %>
                    interactiveButtonType = '<%- __tData.__data?.interaction_message?.interactive_type %>';
                <% } else { %>
                    interactiveButtonType = 'button';
                <% } %>

                <% if(__tData.trigger_type) { %>
                    triggerType = '<%- __tData.trigger_type %>';
                <% } %>
            ">
            <input type="hidden" name="botReplyIdOrUid" value="<%- __tData._uid %>" />

            <!-- Determine message type based on data structure -->
            <% if(__tData.__data?.question_message) { %>
                <input type="hidden" name="message_type" value="question">
            <% } else if(__tData.__data?.goto_message) { %>
                <input type="hidden" name="message_type" value="goto">
            <% } else if(__tData.__data?.wait_message) { %>
                <input type="hidden" name="message_type" value="wait">
            <% } else if(__tData.__data?.media_message) { %>
                <input type="hidden" name="message_type" value="media">
            <% } else if(__tData.__data?.interaction_message) { %>
                <input type="hidden" name="message_type" value="interactive">
            <% } else if(__tData.__data?.team_assignment_message) { %>
                <input type="hidden" name="message_type" value="team_assignment">
            <% } else if(__tData.__data?.webhook_message) { %>
                <input type="hidden" name="message_type" value="webhook">
            <% } else if(__tData.__data?.custom_field_message) { %>
                <input type="hidden" name="message_type" value="custom_field">
            <% } else if(__tData.__data?.stay_in_session_message) { %>
                <input type="hidden" name="message_type" value="stay_in_session">
            <% } else if(__tData.__data?.whatsapp_template_message) { %>
                <input type="hidden" name="message_type" value="whatsapp_template">
            <% } else { %>
                <input type="hidden" name="message_type" value="simple">
            <% } %>

            <template x-if="botFlowUid">
                <input type="hidden" name="bot_flow_uid" :value="botFlowUid">
            </template>

            <!-- Name -->
            <x-lw.input-field type="text" id="lwNameEditField" data-form-group-class="" :label="__tr('Name')" value="<%- __tData.name %>" name="name" required="true" />
            <!-- /Name -->

            <fieldset>
                <legend>{{ __tr('Reply Message') }}</legend>

                <!-- Simple Text Message - default case when no specific message type is detected -->
                <% if(!__tData.__data?.question_message &&
                      !__tData.__data?.goto_message &&
                      !__tData.__data?.wait_message &&
                      !__tData.__data?.media_message &&
                      !__tData.__data?.interaction_message &&
                      !__tData.__data?.team_assignment_message &&
                      !__tData.__data?.webhook_message &&
                      !__tData.__data?.custom_field_message &&
                      !__tData.__data?.stay_in_session_message &&
                      !__tData.__data?.whatsapp_template_message) { %>
                    <div class="form-group">
                        <label for="lwReplyTextEditField">{{ __tr('Reply Text') }}</label>
                        <textarea cols="10" rows="3" id="lwReplyTextEditField" class="lw-form-field form-control"
                            placeholder="{{ __tr('Reply Text') }}" name="reply_text" required="true"><%- __tData.reply_text %></textarea>
                        <div class="help-text my-3 border p-3">{{ __tr('You are free to use following dynamic variables for reply text, which will get replaced with contact\'s concerned field value.') }} <div><code>{{ implode(' ', $dynamicFields) }}</code></div></div>
                    </div>
                <% } %>

                <!-- Question Message -->
                <% if(__tData.__data?.question_message) { %>
                   

                    <div class="form-group">
                        <label for="lwEditQuestionText">{{ __tr('Question Text') }}</label>
                        <textarea id="lwEditQuestionText" class="form-control" name="reply_text" rows="3"
                            placeholder="{{ __tr('What is your name?') }}" required><%- __tData.__data?.question_message?.question_text || __tData.reply_text %></textarea>
                        <div class="help-text my-3 border p-3">
                            {{ __tr('You are free to use following dynamic variables for question text, which will get replaced with contact\'s concerned field value.') }}
                            <div><code>{{ implode(' ', $dynamicFields) }}</code></div>
                        </div>
                    </div>

                    <x-lw.input-field type="selectize" id="lwEditQuestionStoreField" data-form-group-class=""
                        :label="__tr('Store Response In')" name="question_store_field" required="true"
                        data-selected="<%- __tData.__data?.question_message?.store_in_field %>">
                        <x-slot name="selectOptions">
                            <option value="">{{ __tr('Select Custom Field') }}</option>
                            @if(isset($contactCustomFields))
                                @foreach($contactCustomFields as $customField)
                                    <option value="{{ $customField->input_name }}">{{ $customField->input_name }} ({{ ucfirst($customField->input_type) }})</option>
                                @endforeach
                            @endif
                        </x-slot>
                    </x-lw.input-field>
                <% } %>

                <!-- Interactive Message -->
                <% if(__tData.__data?.interaction_message) { %>
                    <div class="form-group">
                        <label for="lwReplyTextEditField">{{ __tr('Reply Text') }}</label>
                        <textarea cols="10" rows="3" id="lwReplyTextEditField" class="lw-form-field form-control"
                            placeholder="{{ __tr('Reply Text') }}" name="reply_text" required="true"><%- __tData.reply_text %></textarea>
                        <div class="help-text my-3 border p-3">{{ __tr('You are free to use following dynamic variables for reply text, which will get replaced with contact\'s concerned field value.') }} <div><code>{{ implode(' ', $dynamicFields) }}</code></div></div>
                    </div>

                    <!-- Interactive Type Selection -->
                    <div class="mt-4">
                        <strong>
                            <input type="radio" name="interactive_type" x-model="interactiveButtonType" value="button" id="lwEditAdvanceBotReplyBtnType" x-bind:checked="interactiveButtonType === 'button'">
                            <label class="mr-2" for="lwEditAdvanceBotReplyBtnType">{{ __tr('Reply Buttons') }}</label>
                            <input type="radio" name="interactive_type" x-model="interactiveButtonType" value="cta_url" id="lwEditAdvanceBotCtaUrlBtnType" x-bind:checked="interactiveButtonType === 'cta_url'">
                            <label class="mr-2" for="lwEditAdvanceBotCtaUrlBtnType">{{ __tr('CTA URL Button') }}</label>
                            <input type="radio" name="interactive_type" x-model="interactiveButtonType" value="list" id="lwEditAdvanceBotListMessageType" x-bind:checked="interactiveButtonType === 'list'">
                            <label class="mr-2" for="lwEditAdvanceBotListMessageType">{{ __tr('List Message') }}</label>
                        </strong>
                        <hr class="mt-1 mb-2">
                    </div>

                    <!-- Reply Buttons -->
                    <template x-if="interactiveButtonType == 'button'">
                        <div>
                            <x-lw.input-field type="text" id="lwAdvanceEditButton1" data-form-group-class="" :label="__tr('Button 1 Label')"
                                name="buttons[1]" value="<%- __tData.__data?.interaction_message?.buttons?.[1] %>" required="true" />
                            <x-lw.input-field type="text" id="lwAdvanceEditButton2" data-form-group-class="" :label="__tr('Button 2 Label (optional)')"
                                name="buttons[2]" value="<%- __tData.__data?.interaction_message?.buttons?.[2] %>" />
                            <x-lw.input-field type="text" id="lwAdvanceEditButton3" data-form-group-class="" :label="__tr('Button 3 Label (optional)')"
                                name="buttons[3]" value="<%- __tData.__data?.interaction_message?.buttons?.[3] %>" />
                        </div>
                    </template>

                    <!-- CTA URL Button -->
                    <template x-if="interactiveButtonType == 'cta_url'">
                        <div>
                            <x-lw.input-field type="text" id="lwCtaEditUrlButtonDisplayText" data-form-group-class=""
                                :label="__tr('CTA Button Display Text')" name="button_display_text"
                                value="<%- __tData.__data?.interaction_message?.cta_url?.display_text %>" required="true"/>
                            <x-lw.input-field type="text" id="lwCtaEditButtonUrl" data-form-group-class=""
                                :label="__tr('CTA Button URL')" name="button_url"
                                value="<%- __tData.__data?.interaction_message?.cta_url?.url %>" required="true" />
                        </div>
                    </template>

                    <!-- List Message -->
                    <template x-if="interactiveButtonType == 'list'">
                        <div x-data="{
                            botListMessageSections: <%- JSON.stringify(__tData.__data?.interaction_message?.list_data?.sections || {}) %>,
                            get totalRows() {
                                let count = 0;
                                for (const sectionId in this.botListMessageSections) {
                                    if (this.botListMessageSections.hasOwnProperty(sectionId)) {
                                        count += Object.keys(this.botListMessageSections[sectionId].rows || {}).length;
                                    }
                                }
                                return count;
                            },
                            addListSection : function() {
                                let uniqueSectionId = __Utils.generateUniqueId('section_');
                                this.botListMessageSections[uniqueSectionId] = {
                                    index: _.size(this.botListMessageSections) + 1,
                                    id:uniqueSectionId,
                                    title:'',
                                    rows:{}
                                };
                            },
                            addListSectionRow : function(sectionId) {
                                if (this.totalRows >= 10) {
                                    alert('You can add a maximum of 10 options across all sections.');
                                    return;
                                }
                                let uniqueRowId = __Utils.generateUniqueId('row_');
                                this.botListMessageSections[sectionId]['rows'][uniqueRowId] = {
                                    index: _.size(this.botListMessageSections[sectionId]['rows']) + 1,
                                    id: uniqueRowId,
                                    row_id:'',title:'',
                                    description:''
                                };
                            },
                            deleteSection(sectionId){
                                delete this.botListMessageSections[sectionId];
                            },
                            deleteRow(sectionId, rowId){
                                delete this.botListMessageSections[sectionId]['rows'][rowId];
                            }
                        }">
                            <x-lw.input-field type="text" id="lwListButtonTextEdit" data-form-group-class=""
                                :label="__tr('Button Label')" name="list_button_text"
                                value="<%- __tData.__data?.interaction_message?.list_data?.button_text %>" required="true" />

                            <template x-for="(botListMessageSection, index) in botListMessageSections">
                                <fieldset>
                                    <legend class="py-1 px-2 mb-1"><small>{{ __tr('Section') }}</small></legend>
                                    <button @click.prevent="deleteSection(botListMessageSection.id)" class="btn btn-link float-right p-1 mt--4" type="button">
                                        <i class="fa fa-times text-danger"></i>
                                    </button>
                                    <x-lw.input-field type="text" data-form-group-class="" :label="__tr('Section Title')"
                                        x-bind:name="'sections[' + botListMessageSection.id+'][title]'"
                                        x-bind:value="botListMessageSection.title" required="true" />
                                    <input type="hidden" x-bind:name="'sections[' + botListMessageSection.id+'][id]'"
                                        x-bind:value="botListMessageSection.id" required="true">

                                    <template x-for="botListMessageRow in botListMessageSection.rows">
                                        <fieldset>
                                            <legend class="py-1 px-2 mb-1"><small>{{ __tr('Row') }}</small></legend>
                                            <button @click.prevent="deleteRow(botListMessageSection.id, botListMessageRow.id)"
                                                class="btn btn-link float-right p-1 mt--4" type="button">
                                                <i class="fa fa-times text-danger"></i>
                                            </button>
                                            <input type="hidden" x-bind:name="'sections[' + botListMessageSection.id+'][rows]['+botListMessageRow.id+'][id]'"
                                                x-bind:value="botListMessageRow.id" required="true">

                                            <x-lw.input-field type="text" data-form-group-class="" :label="__tr('Row ID')"
                                                x-bind:name="'sections[' + botListMessageSection.id+'][rows]['+botListMessageRow.id+'][row_id]'"
                                                x-bind:value="botListMessageRow.row_id" required="true" />
                                            <x-lw.input-field type="text" data-form-group-class="" :label="__tr('Row Title')"
                                                x-bind:name="'sections[' + botListMessageSection.id+'][rows]['+botListMessageRow.id+'][title]'"
                                                x-bind:value="botListMessageRow.title" required="true" />

                                            <div class="form-group">
                                                <label>{{ __tr('Row Description (optional)') }}</label>
                                                <textarea class="form-control" rows="2"
                                                    x-bind:name="'sections[' + botListMessageSection.id+'][rows]['+botListMessageRow.id+'][description]'"
                                                    x-bind:value="botListMessageRow.description"></textarea>
                                            </div>
                                        </fieldset>
                                    </template>
                                    <button type="button" class="btn btn-sm btn-light my-2" @click="addListSectionRow(botListMessageSection.id)">
                                        {{ __tr('Add Row') }}
                                    </button>
                                </fieldset>
                            </template>
                            <button type="button" class="btn btn-sm btn-dark my-2" @click="addListSection()">
                                {{ __tr('Add Section') }}
                            </button>
                        </div>
                    </template>
                <% } %>

                <!-- Media Message -->
                <% if(__tData.__data?.media_message) { %>
                    <!-- Header Type Selection -->
                    <x-lw.input-field x-model="headerType" type="selectize" id="lwMediaHeaderTypeEdit"
                        data-form-group-class="" :label="__tr('Media Type')" name="media_header_type" required="true"
                        data-selected="<%- __tData.__data?.media_message?.header_type %>">
                        <x-slot name="selectOptions">
                            <option value="">{{ __tr('Select Media Type') }}</option>
                            <option value="image">{{ __tr('Image') }}</option>
                            <option value="video">{{ __tr('Video') }}</option>
                            <option value="document">{{ __tr('Document') }}</option>
                            <option value="audio">{{ __tr('Audio') }}</option>
                        </x-slot>
                    </x-lw.input-field>

                    <!-- Current Media Display -->
                    <% if(__tData.__data?.media_message?.uploaded_media_file_name) { %>
                        <div class="form-group">
                            <label>{{ __tr('Current Media') }}</label>
                            <div class="border p-3 mb-3">
                                <% if(__tData.__data?.media_message?.header_type === 'image') { %>
                                    <img src="<%- __tData.__data?.media_message?.media_url %>" alt="Current Image" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                                <% } else if(__tData.__data?.media_message?.header_type === 'video') { %>
                                    <video controls style="max-width: 200px; max-height: 200px;">
                                        <source src="<%- __tData.__data?.media_message?.media_url %>" type="video/mp4">
                                        {{ __tr('Your browser does not support the video tag.') }}
                                    </video>
                                <% } else if(__tData.__data?.media_message?.header_type === 'audio') { %>
                                    <audio controls>
                                        <source src="<%- __tData.__data?.media_message?.media_url %>" type="audio/mpeg">
                                        {{ __tr('Your browser does not support the audio element.') }}
                                    </audio>
                                <% } else if(__tData.__data?.media_message?.header_type === 'document') { %>
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-file fa-2x mr-2"></i>
                                        <div>
                                            <strong><%- __tData.__data?.media_message?.uploaded_media_file_name %></strong><br>
                                            <a href="<%- __tData.__data?.media_message?.media_url %>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-download"></i> {{ __tr('Download') }}
                                            </a>
                                        </div>
                                    </div>
                                <% } %>
                                <div class="mt-2">
                                    <small class="text-muted">{{ __tr('File:') }} <%- __tData.__data?.media_message?.uploaded_media_file_name %></small>
                                </div>
                            </div>
                        </div>
                    <% } %>

                    <!-- Media Upload Fields -->
                    <div class="my-3">
                        <!-- Document Upload -->
                        <div x-show="headerType == 'document'" class="form-group">
                            <label>{{ __tr('Upload New Document (optional)') }}</label>
                            <input id="lwDocumentMediaFilepondEdit" type="file" data-allow-revert="true"
                                data-label-idle="{{ __tr('Select Document') }}" class="lw-file-uploader" data-instant-upload="true"
                                data-action="<?= route('media.upload_temp_media', 'whatsapp_document') ?>"
                                data-file-input-element="#lwMediaFileNameEdit"
                                data-allowed-media='<?= getMediaRestriction('whatsapp_document') ?>' />
                        </div>

                        <!-- Image Upload -->
                        <div x-show="headerType == 'image'" class="form-group">
                            <label>{{ __tr('Upload New Image (optional)') }}</label>
                            <input id="lwImageMediaFilepondEdit" type="file" data-allow-revert="true"
                                data-label-idle="{{ __tr('Select Image') }}" class="lw-file-uploader" data-instant-upload="true"
                                data-action="<?= route('media.upload_temp_media', 'whatsapp_image') ?>"
                                data-file-input-element="#lwMediaFileNameEdit"
                                data-allowed-media='<?= getMediaRestriction('whatsapp_image') ?>' />
                        </div>

                        <!-- Video Upload -->
                        <div x-show="headerType == 'video'" class="form-group">
                            <label>{{ __tr('Upload New Video (optional)') }}</label>
                            <input id="lwVideoMediaFilepondEdit" type="file" data-allow-revert="true"
                                data-label-idle="{{ __tr('Select Video') }}" class="lw-file-uploader" data-instant-upload="true"
                                data-action="<?= route('media.upload_temp_media', 'whatsapp_video') ?>"
                                data-file-input-element="#lwMediaFileNameEdit"
                                data-allowed-media='<?= getMediaRestriction('whatsapp_video') ?>' />
                        </div>

                        <!-- Audio Upload -->
                        <div x-show="headerType == 'audio'" class="form-group">
                            <label>{{ __tr('Upload New Audio (optional)') }}</label>
                            <input id="lwAudioMediaFilepondEdit" type="file" data-allow-revert="true"
                                data-label-idle="{{ __tr('Select Audio') }}" class="lw-file-uploader" data-instant-upload="true"
                                data-action="<?= route('media.upload_temp_media', 'whatsapp_audio') ?>"
                                data-file-input-element="#lwMediaFileNameEdit"
                                data-allowed-media='<?= getMediaRestriction('whatsapp_audio') ?>' />
                        </div>
                    </div>

                    <!-- Hidden field for uploaded media file name -->
                    <input id="lwMediaFileNameEdit" type="hidden" value="<%- __tData.__data?.media_message?.uploaded_media_file_name || '' %>" name="uploaded_media_file_name" />

                    <!-- Caption Field (for non-audio media) -->
                    <div x-show="headerType && (headerType != 'audio')" class="form-group">
                        <label for="lwMediaCaptionTextEdit">{{ __tr('Caption/Text') }}</label>
                        <textarea name="caption" id="lwMediaCaptionTextEdit" class="form-control" rows="2"><%- __tData.__data?.media_message?.caption || __tData.reply_text %></textarea>
                        <div class="help-text my-3 border p-3">{{ __tr('You are free to use following dynamic variables for caption, which will get replaced with contact\'s concerned field value.') }} <div><code>{{ implode(' ', $dynamicFields) }}</code></div></div>
                    </div>
                <% } %>

                <!-- Goto Message -->
                <% if(__tData.__data?.goto_message) { %>
                    <fieldset>
                        <legend>{{ __tr('Goto Node Settings') }}</legend>

                     

                        <!-- Current Target Node Display -->
                        <% if(__tData.__data?.goto_message?.target_node_name) { %>
                            <div class="form-group">
                                <label>{{ __tr('Current Target Node') }}</label>
                                <div class="border p-3 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-arrow-right fa-2x mr-2"></i>
                                        <div>
                                            <strong><%- __tData.__data?.goto_message?.target_node_name %></strong>
                                            <div class="text-muted small">{{ __tr('Node ID:') }} <%- __tData.__data?.goto_message?.redirect_to_node %></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <% } %>

                        <!-- Target Node Selection -->
                        <x-lw.input-field type="selectize" id="lwGotoTargetNodeEdit" data-form-group-class=""
                            :label="__tr('Target Node')" name="goto_target_node" required="true"
                            data-selected="<%- __tData.__data?.goto_message?.redirect_to_node %>"
                            data-callback="onGotoTargetNodeSelected">
                            <x-slot name="selectOptions">
                                <option value="">{{ __tr('Select a node to redirect to...') }}</option>
                                <% if(__tData.flowBots && __tData.flowBots.length > 0) { %>
                                    <% _.forEach(__tData.flowBots, function(flowBot) { %>
                                        <% if(flowBot._uid !== __tData._uid) { %>
                                            <option value="<%- flowBot._uid %>"
                                                <%= (__tData.__data?.goto_message?.redirect_to_node === flowBot._uid) ? 'selected' : '' %>>
                                                <%- flowBot.name %>
                                            </option>
                                        <% } %>
                                    <% }); %>
                                <% } %>
                            </x-slot>
                        </x-lw.input-field>

                      
                    </fieldset>
                <% } %>

                <!-- Wait Message -->
                <% if(__tData.__data?.wait_message) { %>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        {{ __tr('Wait nodes pause the flow for a specified duration before automatically proceeding to the next node.') }}
                    </div>
                <% } %>

                <!-- Team Assignment Message -->
                <% if(__tData.__data?.team_assignment_message) { %>
                    <fieldset>
                        <legend>{{ __tr('Team Assignment Settings') }}</legend>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            {{ __tr('A team assignment node assigns the conversation to a specific team member. The assigned team member will only see conversations assigned to them.') }}
                        </div>

                        <!-- Current Assignment Display -->
                        <% if(__tData.__data?.team_assignment_message?.assigned_team_member) { %>
                            <div class="form-group">
                                <label>{{ __tr('Current Assignment') }}</label>
                                <div class="border p-3 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-user fa-2x mr-2"></i>
                                        <div>
                                            <%
                                            var assignedMember = null;
                                            if(__tData.vendorTeamMembers && __tData.vendorTeamMembers.length > 0) {
                                                assignedMember = _.find(__tData.vendorTeamMembers, function(member) {
                                                    return member._uid === __tData.__data?.team_assignment_message?.assigned_team_member;
                                                });
                                            }
                                            %>
                                            <% if(assignedMember) { %>
                                                <strong><%- assignedMember.full_name %></strong>
                                                <div class="text-muted small"><%- assignedMember.email %></div>
                                            <% } else { %>
                                                <strong>{{ __tr('Unknown Team Member') }}</strong>
                                                <div class="text-muted small">{{ __tr('Member ID:') }} <%- __tData.__data?.team_assignment_message?.assigned_team_member %></div>
                                            <% } %>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <% } %>

                        <!-- Team Member Selection -->
                        <div class="form-group">
                            <label for="lwTeamMemberSelectEdit">{{ __tr('Assign to Team Member') }}</label>
                            <select id="lwTeamMemberSelectEdit" name="assigned_team_member" class="form-control" required>
                                <option value="">{{ __tr('Select a team member...') }}</option>
                                <% if(__tData.vendorTeamMembers && __tData.vendorTeamMembers.length > 0) { %>
                                    <% _.forEach(__tData.vendorTeamMembers, function(teamMember) { %>
                                        <option value="<%- teamMember._uid %>"
                                            <%= (__tData.__data?.team_assignment_message?.assigned_team_member === teamMember._uid) ? 'selected' : '' %>>
                                            <%- teamMember.full_name %> (<%- teamMember.email %>)
                                        </option>
                                    <% }); %>
                                <% } else { %>
                                    <option value="" disabled>{{ __tr('No team members available') }}</option>
                                <% } %>
                            </select>
                        </div>

                        <!-- Assignment Message -->
                        <div class="form-group">
                            <label for="lwTeamAssignmentMessageEdit">{{ __tr('Assignment Message (optional)') }}</label>
                            <textarea
                                id="lwTeamAssignmentMessageEdit"
                                class="form-control"
                                name="assignment_message"
                                rows="3"
                                placeholder="{{ __tr('This conversation has been assigned to a team member...') }}"
                            ><%- __tData.__data?.team_assignment_message?.assignment_message || '' %></textarea>
                            <div class="help-text my-3 border p-3">
                                <strong>{{ __tr('Note:') }}</strong> {{ __tr('This message will be shown to the user when the assignment happens. You can use dynamic variables like:') }}
                                <code>{{ implode(' ', $dynamicFields) }}</code>
                            </div>
                        </div>

                        <!-- Hidden reply_text field with default value for Team Assignment Node -->
                        <input type="hidden" name="reply_text" value="{{ __tr('Assigning conversation to team member...') }}" />

                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>{{ __tr('Important:') }}</strong>
                            {{ __tr('Team assignment nodes are terminal nodes - they end the flow after assigning the conversation.') }}
                        </div>
                    </fieldset>
                <% } %>

                <!-- Webhook Message -->
                <% if(__tData.__data?.webhook_message) { %>
                    <fieldset>
                        <legend>{{ __tr('Webhook Settings') }}</legend>

                       

                        <!-- Current Webhook Configuration Display -->
                        <% if(__tData.__data?.webhook_message?.webhook_url) { %>
                            <div class="form-group">
                                <label>{{ __tr('Current Configuration') }}</label>
                                <div class="border p-3 mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fa fa-globe fa-2x mr-2"></i>
                                        <div>
                                            <strong><%- __tData.__data?.webhook_message?.http_method || 'POST' %></strong>
                                            <code><%- __tData.__data?.webhook_message?.webhook_url %></code>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">{{ __tr('Timeout:') }} <%- __tData.__data?.webhook_message?.timeout || 30 %> {{ __tr('seconds') }}</small>
                                        </div>
                                        <div class="col-md-6">
                                            <% if(__tData.__data?.webhook_message?.response_mapping && __tData.__data?.webhook_message?.response_mapping.length > 0) { %>
                                                <small class="text-muted">{{ __tr('Response Mappings:') }} <%- __tData.__data?.webhook_message?.response_mapping.length %></small>
                                            <% } %>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <% } %>

                        <!-- Webhook URL -->
                        <x-lw.input-field type="url" id="lwWebhookUrlEdit" data-form-group-class=""
                            :label="__tr('Webhook URL')" name="webhook_url" required="true"
                            value="<%- __tData.__data?.webhook_message?.webhook_url || '' %>"
                            placeholder="https://api.example.com/webhook" />

                        <!-- HTTP Method -->
                        <x-lw.input-field type="selectize" id="lwWebhookMethodEdit" data-form-group-class=""
                            :label="__tr('HTTP Method')" name="http_method" required="true"
                            data-selected="<%- __tData.__data?.webhook_message?.http_method || 'POST' %>">
                            <x-slot name="selectOptions">
                                <option value="POST">POST</option>
                                <option value="GET">GET</option>
                                <option value="PUT">PUT</option>
                                <option value="PATCH">PATCH</option>
                                <option value="DELETE">DELETE</option>
                            </x-slot>
                        </x-lw.input-field>

                        <!-- Request Body Template -->
                        <div class="form-group">
                            <label for="lwWebhookRequestBodyEdit">{{ __tr('Request Body (JSON)') }}</label>
                            <textarea id="lwWebhookRequestBodyEdit" class="form-control" name="request_body" rows="6"
                                placeholder='{"message": "{full_name} sent: {user_input}", "phone": "{phone_number}", "email": "{email}"}'
                            ><%- __tData.__data?.webhook_message?.request_body || '' %></textarea>
                            <div class="help-text my-3 border p-3">
                                <strong>{{ __tr('Note:') }}</strong> {{ __tr('You can use dynamic variables in the request body:') }}
                                <code>{{ implode(' ', $dynamicFields) }}</code>
                                <br><strong>{{ __tr('Special variables:') }}</strong>
                                <code>{user_input}</code> {{ __tr('(user\'s last input)') }}
                            </div>
                        </div>

                        <!-- Timeout -->
                        <x-lw.input-field type="number" id="lwWebhookTimeoutEdit" data-form-group-class=""
                            :label="__tr('Timeout (seconds)')" name="timeout" min="5" max="120"
                            value="<%- __tData.__data?.webhook_message?.timeout || 30 %>" />

                        <!-- Success Message -->
                        <div class="form-group">
                            <label for="lwWebhookSuccessMessageEdit">{{ __tr('Success Message') }}</label>
                            <textarea id="lwWebhookSuccessMessageEdit" class="form-control" name="success_message" rows="3"
                                placeholder="{{ __tr('Request processed successfully') }}"
                            ><%- __tData.__data?.webhook_message?.success_message || '' %></textarea>
                            <div class="help-text my-3 border p-3">
                                <strong>{{ __tr('Note:') }}</strong> {{ __tr('This message will be shown when the webhook succeeds. You can use dynamic variables and response data.') }}
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div class="form-group">
                            <label for="lwWebhookErrorMessageEdit">{{ __tr('Error Message') }}</label>
                            <textarea id="lwWebhookErrorMessageEdit" class="form-control" name="error_message" rows="3"
                                placeholder="{{ __tr('Request failed. Please try again later.') }}"
                            ><%- __tData.__data?.webhook_message?.error_message || '' %></textarea>
                        </div>

                        <!-- Response Mapping -->
                        <div class="form-group" x-data="{
                            responseMappings: <%- JSON.stringify(__tData.__data?.webhook_message?.response_mapping || []) %>,
                            addMapping: function() {
                                this.responseMappings.push({source_path: '', target_variable: ''});
                            },
                            removeMapping: function(index) {
                                this.responseMappings.splice(index, 1);
                            }
                        }">
                            <label>{{ __tr('Response Variable Mapping (Optional)') }}</label>
                            <div class="help-text mb-3 border p-3">
                                <strong>{{ __tr('Map webhook response data to variables:') }}</strong><br>
                                {{ __tr('Use dot notation for nested values (e.g., "data.user.name" to access response.data.user.name)') }}
                            </div>

                            <!-- Current Mappings Display -->
                            <% if(__tData.__data?.webhook_message?.response_mapping && __tData.__data?.webhook_message?.response_mapping.length > 0) { %>
                                <div class="mb-3">
                                    <small class="text-muted">{{ __tr('Current Mappings:') }}</small>
                                    <% _.forEach(__tData.__data?.webhook_message?.response_mapping, function(mapping, index) { %>
                                        <div class="border-left pl-2 ml-2 mb-1">
                                            <code><%- mapping.source_path %></code> → <code><%- mapping.target_variable %></code>
                                        </div>
                                    <% }); %>
                                </div>
                            <% } %>

                            <template x-for="(mapping, index) in responseMappings" :key="index">
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" :name="'response_mapping[' + index + '][source_path]'"
                                            placeholder="data.result" x-model="mapping.source_path">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" :name="'response_mapping[' + index + '][target_variable]'"
                                            placeholder="webhook_result" x-model="mapping.target_variable">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger" @click="removeMapping(index)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <button type="button" class="btn btn-sm btn-secondary" @click="addMapping()">
                                <i class="fa fa-plus"></i> {{ __tr('Add Response Mapping') }}
                            </button>
                        </div>

                        <!-- Hidden reply_text field with default value for Webhook Node -->
                        <input type="hidden" name="reply_text" value="{{ __tr('Processing webhook...') }}" />

                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i>
                            <strong>{{ __tr('How it works:') }}</strong>
                            {{ __tr('The webhook will be called with the configured parameters. Success/error messages will be shown to the user based on the response.') }}
                        </div>
                    </fieldset>
                <% } %>

                <!-- Stay in Session Message -->
                <% if(__tData.__data?.stay_in_session_message) { %>
                    <fieldset>
                        <legend>{{ __tr('Stay in Session Settings') }}</legend>

                      

                        <!-- Current Configuration Display -->
                        <% if(__tData.__data?.stay_in_session_message?.session_message) { %>
                            <div class="form-group">
                                <label>{{ __tr('Current Configuration') }}</label>
                                <div class="border p-3 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-infinity fa-2x mr-2 text-primary"></i>
                                        <div>
                                            <strong>{{ __tr('Session Message:') }}</strong>
                                            <div class="text-muted"><%- __tData.__data?.stay_in_session_message?.session_message %></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <% } %>

                        <!-- Session Message -->
                        <div class="form-group">
                            <label for="lwStayInSessionMessageEdit">{{ __tr('Session Message (Optional)') }}</label>
                            <textarea
                                id="lwStayInSessionMessageEdit"
                                class="form-control"
                                name="session_message"
                                rows="3"
                                placeholder="{{ __tr('Session will remain active. You can continue interacting...') }}"
                            ><%- __tData.__data?.stay_in_session_message?.session_message || '' %></textarea>
                            <div class="help-text my-3 border p-3">
                                <strong>{{ __tr('Note:') }}</strong> {{ __tr('This message will be shown to indicate the session is staying active. You can use dynamic variables like:') }}
                                <code>{{ implode(' ', $dynamicFields) }}</code>
                            </div>
                        </div>

                        <!-- Hidden reply_text field with empty value for Stay in Session Node -->
                        <input type="hidden" name="reply_text" value="" />

                      

                       
                    </fieldset>
                <% } %>
            </fieldset>

            <!-- Trigger Settings (only for non-flow nodes) -->
            <template x-if="!botFlowUid">
                <div x-data="{botTriggerType:'<%- __tData.trigger_type %>'}">
                    <!-- Trigger_Type -->
                    <x-lw.input-field class="" type="selectize" x-model="botTriggerType" id="lwTriggerTypeEditField" data-form-group-class="" data-selected="<%- __tData.trigger_type %>" :label="__tr('Trigger Type')" name="trigger_type" required="true">
                        <x-slot name="selectOptions">
                            <option value="">{{ __tr('How do you want to trigger this message?') }}</option>
                            @foreach (configItem('bot_reply_trigger_types') as $replyBotTypeKey => $replyBotType)
                                <option value="{{ $replyBotTypeKey }}">{{ $replyBotType['title'] }} </option>
                            @endforeach
                        </x-slot>
                    </x-lw.input-field>

                    <!-- Reply_Trigger -->
                    <div x-show="botTriggerType != 'welcome'">
                        <x-lw.input-field placeholder="{{ __tr('What\'s that magic words that will trigger this reply?') }}" type="text" id="lwAdvanceBotReplyTriggerEditField" data-form-group-class=""
                            :label="__tr('Reply Trigger Subject')" name="reply_trigger" value="<%- __tData.reply_trigger %>" required="true" />
                        <div><small class="text-muted">{{ __tr('You can have comma separated multiple triggers.') }}</small></div>
                    </div>

                    @if(hasVendorAccess('manage.bot_reply'))
                    <div class="form-group pt-3">
                        <input type="checkbox" id="lwEditBotStatus" <%- (!__tData.status || (__tData.status == 2)) ? '' : 'checked' %> data-lw-plugin="lwSwitchery" value="1" name="status">
                        <label for="lwEditBotStatus">{{ __tr('Status') }}</label>
                    </div>
                    @endif

                    <div class="my-4">
                        <x-lw.checkbox id="lwEditValidateBotReply" :offValue="0" name="validate_bot_reply" value="1" data-lw-plugin="lwSwitchery" :label="__tr('Validate Bot Reply by Sending Test Message')" />
                    </div>
                </div>
            </template>
            </div>
        </script>
                <!-- form footer -->
                <div class="modal-footer">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-2"></i>{{ __tr('Submit') }}
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times me-2"></i>{{ __tr('Close') }}
                    </button>
                </div>
    </x-lw.form>
    <!--/  Edit Bot Reply Form -->
</x-lw.modal>
<!--/ Edit Bot Reply Modal -->

<script>
function addResponseMapping(button) {
    const container = $(button).closest('.form-group');
    const mappingCount = container.find('.row.mb-2').length;
    const newMapping = `
        <div class="row mb-2">
            <div class="col-md-5">
                <input type="text" class="form-control" name="response_mapping[${mappingCount}][source_path]"
                    placeholder="data.result">
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control" name="response_mapping[${mappingCount}][target_variable]"
                    placeholder="webhook_result">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.row').remove()">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    $(button).before(newMapping);
}

// Initialize edit form when modal is shown
$('#lwEditBotReply').on('shown.bs.modal', function () {
    // Initialize selectize fields
    setTimeout(function() {
        $('[data-lw-plugin="lwSelectize"]').each(function() {
            if (!$(this).hasClass('selectized')) {
                lwPlugins.lwSelectize($(this));
            }
        });

        // Initialize other plugins if needed
        lwPluginsInit();
    }, 100);
});
</script>