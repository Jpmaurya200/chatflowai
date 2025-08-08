@php
/**
* Component : User
* Controller : UserController
* File : User.list.blade.php
* ----------------------------------------------------------------------------- */
@endphp
@extends('layouts.app', ['title' => __tr('Agents')])
@section('content')
@include('users.partials.header', [
'title' => __tr(''),
'description' => '',
'class' => 'col-lg-7'
])
<div class="container-fluid mt-lg--6">
    <div class="row mt-3">
        <!-- button -->
        <div class="col-xl-12 mb-3 mt-5 d-flex justify-content-between align-items-center">
            <h1 class="mb-0" style="color:#0B7753;"><i class="fas fa-user-friends me-2"></i> {{ __tr('Agents') }}</h1>
            <button type="button" class="lw-btn btn btn-neo btn-neo-gradient-green" data-toggle="modal"
                data-target="#lwAddNewUser"><i class="fas fa-plus"></i> {{ __tr('Add New Agent') }}</button>
        </div>
        <!--/ button -->
        <!-- Add New User Modal -->
        <x-lw.modal id="lwAddNewUser" :header="__tr('Add New Agent')" :hasForm="true">
            <!--  Add New User Form -->
            <x-lw.form id="lwAddNewUserForm" :action="route('vendor.user.write.create')"
                :data-callback-params="['modalId' => '#lwAddNewUser', 'datatableId' => '#lwUserList']"
                data-callback="appFuncs.modelSuccessCallback">
                <!-- form body -->
                <div class="lw-form-modal-body">
                    <!-- form fields form fields -->
                    <!-- First_Name -->
                    <x-lw.input-field type="text" id="lwFirstNameField" data-form-group-class=""
                        :label="__tr('First Name')" name="first_name" required="true" />
                    <!-- /First_Name -->
                    <!-- Last_Name -->
                    <x-lw.input-field type="text" id="lwLastNameField" data-form-group-class=""
                        :label="__tr('Last Name')" name="last_name" required="true" />
                    <!-- /Last_Name -->
                    <x-lw.input-field type="number" id="lwMobileNumberField" data-form-group-class=""
                        :label="__tr('Mobile Number')" name="mobile_number" required="true" minlength="9" />
                        <h5><span class="text-muted">{{__tr("Mobile number should be with country code without 0 or +")}}</span></h5>

                    <!-- Username -->
                    <x-lw.input-field type="text" id="lwUsernameField" data-form-group-class=""
                        :label="__tr('Username')" name="username" required="true" minlength="3" />
                    <!-- /Username -->
                    <x-lw.input-field type="text" id="lwEmailField" data-form-group-class="" :label="__tr('Email')"
                        name="email" required="true" minlength="3" />
                    <!-- Password -->
                    <x-lw.input-field type="password" id="lwPasswordField" data-form-group-class=""
                        :label="__tr('Password')" name="password" required="true" minlength="6" />
                    <!-- /Password -->
                    <fieldset>
                        <legend>{{ __tr('Permissions') }}</legend>
                        @foreach (getListOfPermissions() as $permissionKey => $permission)
                        <div class="d-block my-3">
                            <x-lw.checkbox id="lw{{ $permissionKey }}Item" name="permissions[{{ $permissionKey }}]"
                                data-lw-plugin="lwSwitchery" :label="$permission['title']" />
                            @if (isset($permission['description']) and $permission['description'])
                            <p class="text-muted mt-1 fs-1">{{ $permission['description'] }}</p>
                            <hr class="my-1">
                            @endif
                        </div>
                        @endforeach
                    </fieldset>
                 
                </div>
                <!-- form footer -->
                <div class="modal-footer">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
                </div>
            </x-lw.form>
            <!--/  Add New User Form -->
        </x-lw.modal>
        <!--/ Add New User Modal -->
        <!--/ Edit User Modal -->

        <!-- Edit User Modal -->
        <x-lw.modal id="lwEditUser" :header="__tr('Edit Agent & Permissions')" :hasForm="true">
            <!--  Edit User Form -->
            <x-lw.form id="lwEditUserForm" :action="route('vendor.user.write.update')"
                :data-callback-params="['modalId' => '#lwEditUser', 'datatableId' => '#lwUserList']"
                data-callback="appFuncs.modelSuccessCallback">
                <!-- form body -->
                <div id="lwEditUserBody" class="lw-form-modal-body"></div>
                <script type="text/template" id="lwEditUserBody-template">

                    <input type="hidden" name="userIdOrUid" value="<%- __tData._uid %>" />
                        <!-- form fields -->
                        <!-- First_Name -->
           <x-lw.input-field type="text" id="lwFirstNameEditField" data-form-group-class="" :label="__tr('First Name')" value="<%- __tData.first_name %>" name="first_name"  required="true"                 />
                <!-- /First_Name -->
                <!-- Last_Name -->
           <x-lw.input-field type="text" id="lwLastNameEditField" data-form-group-class="" :label="__tr('Last Name')" value="<%- __tData.last_name %>" name="last_name"  required="true"                 />
           <x-lw.input-field type="text" id="lwMobileNumberEditField" data-form-group-class="" :label="__tr('Mobile Number')" value="<%- __tData.mobile_number %>" name="mobile_number"  />
            <h5><span class="text-muted">{{__tr("Mobile number should be with country code without 0 or +")}}</span></h5>

           <x-lw.input-field type="text" id="lwEmailEditField" data-form-group-class="" :label="__tr('Email')" value="<%- __tData.email %>" name="email"  />
           <x-lw.input-field type="password" id="lwPasswordEditField" data-form-group-class="" :label="__tr('Password')"  name="password"  />
                <!-- /Last_Name -->
                <!-- STATUS -->
                <div class="form-group pt-3">
                    <label for="lwIsMemberActiveEditField">{{  __tr('Status') }}</label>
                    <input type="checkbox" id="lwIsMemberActiveEditField" <%- __tData.status == 1 ? 'checked' : '' %> data-lw-plugin="lwSwitchery" name="status">
                </div>
                <!-- /STATUS -->
                <fieldset>
                    <legend>{{  __tr('Permissions') }}</legend>
                    @foreach(getListOfPermissions() as $permissionKey => $permission)
                            <span class="d-block my-3">
                                <label for="lwEdit{{ $permissionKey }}Permission" class="flex items-center">
                                    <input id="lwEdit{{ $permissionKey }}Permission" type="checkbox" <%- (__tData.vendor_user_details?.__data?.permissions?.{{ $permissionKey }} == 'allow') ? 'checked' : '' %> name="permissions[{{ $permissionKey }}]" class="form-checkbox" data-lw-plugin="lwSwitchery">
                                    <span class="ml-2 text-gray-600">{{ $permission['title'] }}</span>
                                </label>
                                @if (isset($permission['description']) and $permission['description'])
                                <p class="text-muted mt-1 fs-1">{{ $permission['description'] }}</p>
                                <hr class="my-1">
                                @endif
                            </span>
                    @endforeach
                </fieldset>
                  
                     </script>
                <!-- form footer -->
                <div class="modal-footer">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
                </div>
            </x-lw.form>
            <!--/  Edit User Form -->
        </x-lw.modal>
        <!--/ Edit User Modal -->
        <div class="col-xl-12">
            <div class="modern-table-container">
                <div class="table-header-controls">
                    <div class="entries-control">
                        <label for="ul-entries-per-page" class="mb-0">{{ __tr('Show') }}</label>
                        <select id="ul-entries-per-page" class="entries-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100" selected>100</option>
                            <option value="200">200</option>
                        </select>
                        <span class="entries-text">{{ __tr('entries') }}</span>
                    </div>
                    <div class="search-control">
                        <input type="text" id="ul-table-search" class="search-input" placeholder="{{ __tr('Search...') }}">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
                <div class="table-responsive">
                    <x-lw.datatable
                        id="lwUserList"
                        class="modern-datatable table-hover align-middle"
                        data-page-length="100"
                        :url="route('vendor.user.read.list')">
                        <th data-orderable="true" data-name="first_name">{{ __tr('First Name') }}</th>
                        <th data-orderable="true" data-name="last_name">{{ __tr('Last Name') }}</th>
                        <th data-orderable="true" data-name="username">{{ __tr('Username') }}</th>
                        <th data-orderable="true" data-name="email">{{ __tr('Email') }}</th>
                        <th data-orderable="true" data-name="mobile_number">{{ __tr('Mobile Number') }}</th>
                        <th data-orderable="true" data-name="created_at">{{ __tr('Created At') }}</th>
                        <th data-template="#userStatusColumnTemplate" name="null" class="text-center">{{ __tr('Status') }}</th>
                        <th data-template="#userActionColumnTemplate" name="null" class="text-right">{{ __tr('Action') }}</th>
                    </x-lw.datatable>
                </div>
            </div>
        </div>
        <!-- status template -->
        <script type="text/template" id="userStatusColumnTemplate">
            <% if(__tData.status == 'Active') { %>
                <span class="badge badge-pill badge-success"><i class="fa fa-check-circle mr-1"></i> {{  __tr('Active') }}</span>
            <% } else { %>
                <span class="badge badge-pill badge-danger"><i class="fa fa-times-circle mr-1"></i> {{  __tr('Inactive') }}</span>
            <% } %>
        </script>
        <!-- action template -->
        <script type="text/template" id="userActionColumnTemplate">
            <div class="dropdown d-inline-block action-dropdown">
                <button class="btn btn-sm btn-light action-kebab-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __tr('Actions') }}">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-sm">
                    <a class="dropdown-item lw-ajax-link-action" data-pre-callback="appFuncs.clearContainer" data-response-template="#lwEditUserBody" href="<%= __Utils.apiURL("{{ route('vendor.user.read.update.data', [ 'userIdOrUid']) }}", {'userIdOrUid': __tData._uid}) %>" data-toggle="modal" data-target="#lwEditUser">
                        <i class="fa fa-edit mr-2 text-primary"></i> {!! __tr('Edit User & Permissions') !!}
                    </a>
                    <% if(__tData.status=='Active' && __tData._uid != '{{ getUserUid() }}') { %>
                    <a class="dropdown-item lw-ajax-link-action" data-method="post" data-confirm="#lwLoginAs-template" href="<%= __Utils.apiURL("{{ route('vendor.user.write.login_as', [ 'userIdOrUid']) }}", {'userIdOrUid': __tData._uid}) %>">
                        <i class="fa fa-sign-in-alt mr-2 text-success"></i> {{  __tr('Login as') }}
                    </a>
                    <% } %>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger lw-ajax-link-action" data-method="post" data-confirm="#lwDeleteUser-template" data-callback-params="{{ json_encode(['datatableId' => '#lwUserList']) }}" data-callback="appFuncs.modelSuccessCallback" href="<%= __Utils.apiURL("{{ route('vendor.user.write.delete', [ 'userIdOrUid']) }}", {'userIdOrUid': __tData._uid}) %>">
                        <i class="fa fa-trash mr-2"></i> {{  __tr('Delete') }}
                    </a>
                </div>
            </div>
        </script>
        <!-- /action template -->

        <!-- User delete template -->
        <script type="text/template" id="lwDeleteUser-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to delete this Agent?') }}</p>
    </script>
        <!-- /User delete template -->
        <script type="text/template" id="lwLoginAs-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
        <p>{{ __tr('You want login to this user account?') }}</p>
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function initUserListControls() {
            var table = $('#lwUserList').DataTable();
            if (!table) { setTimeout(initUserListControls, 150); return; }

            $('#ul-entries-per-page').val(table.page.len());

            $('#ul-entries-per-page').off('change.users').on('change.users', function() {
                var len = parseInt(this.value, 10) || 10;
                table.page.len(len).draw();
            });

            $('#ul-table-search').off('keyup.users').on('keyup.users', function() {
                table.search(this.value).draw();
            });

            $('#lwUserList').on('draw.dt', function(){
                $('#ul-entries-per-page').val(table.page.len());
            });
        }

        setTimeout(initUserListControls, 500);
    });
</script>
<style>
    /* Header Controls */
    .modern-table-container { background: #ffffff; border-radius: 12px; border: 1px solid #e9ecef; box-shadow: 0 4px 18px rgba(2,6,23,0.06); overflow: hidden; }
    .table-header-controls { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; background: linear-gradient(135deg,#f9fafb,#f3f4f6); border-bottom: 1px solid #e9ecef; }
    .entries-control { display: flex; align-items: center; gap: .5rem; color: #6b7280; }
    .entries-select { padding: .35rem .6rem; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; color: #111827; min-width: 64px; }
    .entries-text { font-size: .9rem; color: #6b7280; }
    .search-control { position: relative; }
    .search-input { padding: .5rem .9rem .5rem 2.2rem; border: 1px solid #cbd5e1; border-radius: 10px; min-width: 220px; }
    .search-input:focus { outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,.12); border-color: #93c5fd; }
    .search-icon { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); color: #9ca3af; }

    /* Hide default DataTables length & search since we use custom controls */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { display: none !important; }

    /* Modern DataTable Styles (scoped to this page) */
    .modern-datatable { width: 100% !important; border-collapse: collapse !important; }
    .modern-datatable thead th { background: #f8f9fa !important; border: none !important; padding: 1rem 1.25rem !important; font-weight: 700 !important; font-size: 0.85rem !important; color: #495057 !important; text-transform: uppercase !important; letter-spacing: 0.4px !important; }
    .modern-datatable tbody td { padding: 0.9rem 1.25rem !important; vertical-align: middle !important; font-size: 0.92rem !important; color: #334155 !important; border-top: 1px solid #eef2f7 !important; }
    .modern-datatable tbody tr:hover { background: #fbfbfd !important; }

    /* Align actions and status nicely */
    .modern-datatable thead th.text-right,
    .modern-datatable tbody td:last-child { text-align: right !important; white-space: nowrap; }
    .modern-datatable thead th.text-center { text-align: center !important; }
    .modern-datatable tbody td:nth-child(7) { text-align: center !important; }

    /* Badge polish */
    .badge { font-weight: 600; letter-spacing: 0.2px; }
    .badge-success { background-color: #22a06b; }
    .badge-danger { background-color: #e35d6a; }

    /* Actions dropdown (three-dot) */
    .action-kebab-btn { border: 1px solid #e5e7eb; }
    .action-kebab-btn:hover { background: #f3f4f6; }
    .action-dropdown .dropdown-menu { min-width: 220px; border-radius: 10px; }

    /* Modern gradient buttons */
    .btn-neo { border-radius: 12px; font-weight: 600; padding: 0.65rem 1.1rem; transition: transform 200ms ease, box-shadow 200ms ease, background-position 350ms ease, color 180ms ease; line-height: 1.25rem; }
    .btn-neo i { margin-right: .4rem; }
    .btn-neo-gradient-green { color: #ffffff !important; border: 0; background-image: linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%); background-size: 200% 200%; box-shadow: 0 2px 8px rgba(11, 119, 83, 0.18); }
    .btn-neo-gradient-green:hover, .btn-neo-gradient-green:focus { background-position: right center; transform: translateY(-1px); box-shadow: 0 10px 24px rgba(11, 119, 83, 0.28); }
    .btn-neo-gradient-green:active { transform: translateY(0); box-shadow: 0 6px 14px rgba(11, 119, 83, 0.22); }
</style>
    </div>
</div>
@endsection()