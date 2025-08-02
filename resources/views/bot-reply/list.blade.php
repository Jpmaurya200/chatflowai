@php
/**
* Component : BotReply
* Controller : BotReplyController
* File : BotReply.list.blade.php
* ----------------------------------------------------------------------------- */
@endphp
@extends('layouts.app', ['title' => __tr('Chatbot')])
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endpush
@section('content')
@include('users.partials.header', [
'title' => __tr(''),
'description' => '',
'class' => 'col-lg-7'
])
<div class="container-fluid mt-lg--6">
    <div class="row mt-3" x-data="{isAdvanceBot:'interactive',botFlowUid:null}">
        <!-- Header Section -->
        <div class="col-xl-12 mb-3">
            <div class="mt-5 d-flex justify-content-between align-items-center">
                <h1 class="page-title mb-0" style="color: #22A755;">
                    <i class="fas fa-robot me-2"></i>{{ __tr(' Chatbot') }}
                </h1>
                <div class="d-flex">
                    <!-- Create New Bot Dropdown -->
                    <div class="btn-group me-2">
                        <button type="button"
                            class="lw-btn btn btn-modern btn-modern-primary btn-rounded animate__animated animate__fadeIn dropdown-toggle"
                            data-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-plus-circle me-2"></i>{{ __tr(' Create New Bot') }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <button type="button"
                                @click="isAdvanceBot = 'simple'"
                                class="dropdown-item"
                                data-toggle="modal"
                                data-target="#lwAddNewAdvanceBotReply">
                                <i class="fas fa-comment me-2"></i>{{ __tr('Simple Chatbot') }}
                            </button>
                            <button type="button"
                                @click="isAdvanceBot = 'media'"
                                class="dropdown-item"
                                data-toggle="modal"
                                data-target="#lwAddNewAdvanceBotReply">
                                <i class="fas fa-photo-video me-2"></i>{{ __tr('Media Chatbot') }}
                            </button>
                            <button type="button"
                                @click="isAdvanceBot = 'interactive'"
                                class="dropdown-item"
                                data-toggle="modal"
                                data-target="#lwAddNewAdvanceBotReply">
                                <i class="fas fa-cogs me-2"></i>{{ __tr('Advance Interactive Chatbot') }}
                            </button>
                        </div>
                    </div>

                    <!-- Help Modal Button -->
                    <x-lw.help-modal :subject="__tr('What are the Chatbots and How to use it?')"
                        class="lw-btn btn btn-modern btn-rounded btn-modern-green animate__animated animate__fadeIn">

                        <template #trigger>
                            <i class="fas fa-info-circle me-2"></i>{{ __tr(' Help') }}
                        </template>

                        <!-- Modal content with green outline -->
                        <div class="p-4 bg-light rounded border border-success">
                            <h3 class="mb-3" style="color: #1c5c3a;">{{ __tr('What are Bots') }}</h3>
                            <p class="text-dark">
                                {{ __tr('Bots are instructions given to the system so when you get a message, a reply is triggered automatically.') }}
                            </p>
                        </div>
                    </x-lw.help-modal>
                </div>
            </div>
        </div>
        <!-- Modern Table Container -->
        <div class="col-xl-12">
            <div class="modern-table-container">
                <!-- Table Header with Controls -->
                <div class="table-header-controls">
                    <div class="table-title-section">
                        <h2 class="table-title">{{ __tr('Chatbot List') }}</h2>
                    </div>
                    <div class="table-controls">
                        <div class="entries-control">
                            <label for="entries-per-page">{{ __tr('Show') }}</label>
                            <select id="entries-per-page" class="entries-select">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200" selected>200</option>
                            </select>
                            <span class="entries-text">{{ __tr('entries per page') }}</span>
                        </div>
                        <div class="search-control">
                            <input type="text" id="table-search" class="search-input" placeholder="{{ __tr('Search...') }}">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- Modern DataTable -->
                <div class="table-responsive">
                    <x-lw.datatable data-page-length="200" id="lwBotReplyList" class="modern-datatable" :url="route('vendor.bot_reply.read.list')">
                        <th data-orderable="true" data-name="name">{{ __tr('Name') }}</th>
                        <th data-name="bot_type">{{ __tr('Bot Type') }}</th>
                        <th data-orderable="true" data-name="trigger_type">{{ __tr('Trigger Type') }}</th>
                        <th data-orderable="true" data-name="reply_trigger">{{ __tr('Trigger Subject') }}</th>
                        <th data-template="#botReplyStatusColumnTemplate" name="null" class="status-column">{{ __tr('Status') }}</th>
                        <th data-orderable="true" data-name="created_at">{{ __tr('Created At') }}</th>
                        <th data-template="#botReplyActionColumnTemplate" name="null">{{ __tr('Action') }}</th>
                    </x-lw.datatable>
                </div>

                <!-- Table Footer with Info -->
                <div class="table-footer">
                    <div class="table-info">
                        <span id="table-info-text">{{ __tr('Showing 0 to 0 of 0 entries') }}</span>
                    </div>
                </div>
            </div>
            @include('bot-reply.bot-forms-partial')
        </div>

        <!-- Status Column Template -->
        <script type="text/template" id="botReplyStatusColumnTemplate">
            <% if(__tData.status == 1) { %>
                <div class="status-badge status-approved">
                    <i class="fa fa-check-circle status-icon"></i>
                    <span class="status-text">{{ __tr('ACTIVE') }}</span>
                </div>
            <% } else { %>
                <div class="status-badge status-rejected">
                    <i class="fa fa-times-circle status-icon"></i>
                    <span class="status-text">{{ __tr('INACTIVE') }}</span>
                </div>
            <% } %>
        </script>

        <!-- Action Column Template -->
        <script type="text/template" id="botReplyActionColumnTemplate">
            <div class="action-buttons-container">
                <!-- Edit Button -->
                <a data-pre-callback="appFuncs.clearContainer"
                   title="{{ __tr('Edit') }}"
                   class="action-btn edit-btn lw-ajax-link-action"
                   data-response-template="#lwEditBotReplyBody"
                   href="<%= __Utils.apiURL('{{ route('vendor.bot_reply.read.update.data', ['botReplyIdOrUid']) }}', {'botReplyIdOrUid': __tData._uid}) %>"
                   data-toggle="modal"
                   data-target="#lwEditBotReply">
                    <i class="fa fa-edit"></i>
                </a>

                <!-- Delete Button -->
                <a data-method="post"
                   href="<%= __Utils.apiURL('{{ route('vendor.bot_reply.write.delete', [ 'botReplyIdOrUid']) }}', {'botReplyIdOrUid': __tData._uid}) %>"
                   class="action-btn delete-btn lw-ajax-link-action-via-confirm"
                   data-confirm="#lwDeleteBotReply-template"
                   title="{{ __tr('Delete') }}"
                   data-callback-params="{{ json_encode(['datatableId' => '#lwBotReplyList']) }}"
                   data-callback="appFuncs.modelSuccessCallback">
                    <i class="fa fa-trash"></i>
                </a>

                <!-- Clone Button -->
                <a data-method="post"
                   href="<%= __Utils.apiURL('{{ route('vendor.bot_reply.write.duplicate', [ 'botReplyIdOrUid']) }}', {'botReplyIdOrUid': __tData._uid}) %>"
                   class="action-btn clone-btn lw-ajax-link-action-via-confirm"
                   data-confirm="#lwDuplicateBotReply-template"
                   title="{{ __tr('Duplicate') }}"
                   data-callback-params="{{ json_encode(['datatableId' => '#lwBotReplyList']) }}"
                   data-callback="appFuncs.modelSuccessCallback">
                    <i class="fa fa-copy"></i>
                </a>
            </div>
        </script>

        <!-- /action template -->

        <!-- Bot Reply delete template -->
        <script type="text/template" id="lwDeleteBotReply-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to delete this Chatbot?') }}</p>
    </script>
        <!-- /Bot Reply delete template -->
        <!-- Bot Reply duplicate template -->
        <script type="text/template" id="lwDuplicateBotReply-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to duplicate this Chatbot?') }}</p>
    </script>
        <!-- /Bot Reply duplicate template -->
    </div>
</div>
<style>
    /* Modern Table Container Styles */
    .modern-table-container {
        background: #ffffff !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
        overflow: hidden !important;
        margin-bottom: 2rem !important;
        border: 1px solid #e9ecef !important;
    }

    /* Table Header Controls */
    .table-header-controls {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 1.5rem 2rem !important;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    .table-title-section {
        display: flex !important;
        align-items: center !important;
    }

    .table-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #495057 !important;
        margin: 0 !important;
    }

    .table-controls {
        display: flex !important;
        align-items: center !important;
        gap: 1.5rem !important;
    }

    .entries-control {
        display: flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }

    .entries-control label {
        font-size: 0.875rem !important;
        color: #6c757d !important;
        margin: 0 !important;
    }

    .entries-select {
        padding: 0.375rem 0.75rem !important;
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        background: #ffffff !important;
        font-size: 0.875rem !important;
        color: #495057 !important;
        min-width: 60px !important;
    }

    .entries-text {
        font-size: 0.875rem !important;
        color: #6c757d !important;
    }

    .search-control {
        position: relative !important;
    }

    .search-input {
        padding: 0.5rem 1rem 0.5rem 2.5rem !important;
        border: 1px solid #ced4da !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        font-size: 0.875rem !important;
        color: #495057 !important;
        min-width: 200px !important;
        transition: all 0.3s ease !important;
    }

    .search-input:focus {
        outline: none !important;
        border-color: #007bff !important;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1) !important;
    }

    .search-icon {
        position: absolute !important;
        left: 0.75rem !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: #6c757d !important;
        font-size: 0.875rem !important;
    }

    /* Modern DataTable Styles */
    .modern-datatable {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .modern-datatable thead th {
        background: #f8f9fa !important;
        border: none !important;
        padding: 1rem 1.5rem !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        color: #495057 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        position: relative !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
    }

    .modern-datatable thead th:hover {
        background: #e9ecef !important;
    }

    .modern-datatable tbody tr {
        border-bottom: 1px solid #f1f3f4 !important;
        transition: all 0.3s ease !important;
    }

    .modern-datatable tbody tr:hover {
        background: #f8f9fa !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
    }

    .modern-datatable tbody td {
        padding: 1rem 1.5rem !important;
        font-size: 0.875rem !important;
        color: #495057 !important;
        vertical-align: middle !important;
    }

    /* Status Badge Styles */
    .status-badge {
        display: inline-flex !important;
        align-items: center !important;
        padding: 0.5rem 1rem !important;
        border-radius: 50px !important;
        font-weight: 600 !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.5px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
        transition: all 0.3s ease !important;
        text-transform: uppercase !important;
    }

    .status-badge:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
    }

    .status-icon {
        margin-right: 0.5rem !important;
        font-size: 0.875rem !important;
    }

    .status-approved {
        background: linear-gradient(135deg, #d4edda, #c3e6cb) !important;
        color: #155724 !important;
        border-left: 3px solid #28a745 !important;
    }

    .status-rejected {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb) !important;
        color: #721c24 !important;
        border-left: 3px solid #dc3545 !important;
    }

    /* Action Buttons Container */
    .action-buttons-container {
        display: flex !important;
        align-items: center !important;
        gap: 0.4rem !important;
        justify-content: flex-start !important;
        flex-wrap: nowrap !important;
        white-space: nowrap !important;
        min-width: fit-content !important;
        overflow: visible !important;
    }

    .action-btn {
        width: 34px !important;
        height: 34px !important;
        border: none !important;
        border-radius: 6px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.8rem !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        position: relative !important;
        overflow: hidden !important;
        text-decoration: none !important;
        background: linear-gradient(135deg, #0B7753, #22A755) !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(11, 119, 83, 0.2) !important;
        flex-shrink: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .action-btn:hover,
    .action-btn:focus {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(11, 119, 83, 0.3) !important;
        text-decoration: none !important;
        background: linear-gradient(135deg, #095c41, #1D9248) !important;
        color: white !important;
        outline: none !important;
    }

    /* Action buttons - Green gradient for most buttons */
    .edit-btn,
    .clone-btn {
        background: linear-gradient(135deg, #0B7753, #22A755) !important;
        color: white !important;
    }

    .edit-btn:hover,
    .edit-btn:focus,
    .clone-btn:hover,
    .clone-btn:focus {
        background: linear-gradient(135deg, #095c41, #1D9248) !important;
        color: white !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(11, 119, 83, 0.3) !important;
    }

    /* Delete button - Red gradient */
    .delete-btn {
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2) !important;
    }

    .delete-btn:hover,
    .delete-btn:focus {
        background: linear-gradient(135deg, #c82333, #bd2130) !important;
        color: white !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3) !important;
    }

    /* Table Footer */
    .table-footer {
        padding: 1rem 2rem !important;
        background: #f8f9fa !important;
        border-top: 1px solid #dee2e6 !important;
    }

    .table-info {
        font-size: 0.875rem !important;
        color: #6c757d !important;
    }

    /* Modern Button Styles */
    .btn-modern {
        transition: all 0.3s ease !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08) !important;
        font-weight: 600 !important;
        letter-spacing: 0.025em !important;
        padding: 0.5rem 1rem !important;
        margin: 0 0.2rem !important;
    }

    .btn-modern:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08) !important;
    }

    .btn-modern-primary {
        background: linear-gradient(135deg, #6e8efb, #4a6cf7) !important;
        border: none !important;
        color: white !important;
    }

    .btn-modern-green {
        background: linear-gradient(135deg, #0B7753, #22A755) !important;
        border: none !important;
        color: white !important;
    }

    .btn-modern-green:hover {
        background: linear-gradient(135deg, #22A755, #1D9248) !important;
        color: white !important;
    }

    .btn-rounded {
        border-radius: 12px !important;
    }

    /* Page Title Styling */
    .page-title {
        font-size: 1.75rem !important;
        font-weight: 600 !important;
        color: #333 !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1.2 !important;
        animation: fadeInLeft 0.5s ease-out !important;
    }

    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        display: none !important;
    }

    /* Hide default DataTables elements since we have custom ones */
    .dataTables_wrapper .top {
        display: none !important;
    }

    .dataTables_wrapper .bottom {
        display: none !important;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .table-header-controls {
            flex-direction: column !important;
            gap: 1rem !important;
            align-items: stretch !important;
        }

        .table-controls {
            justify-content: space-between !important;
        }
    }

    @media (max-width: 768px) {
        .table-header-controls {
            padding: 1rem !important;
        }

        .table-controls {
            flex-direction: column !important;
            gap: 1rem !important;
        }

        .entries-control {
            justify-content: center !important;
        }

        .search-control {
            width: 100% !important;
        }

        .search-input {
            width: 100% !important;
            min-width: auto !important;
        }

        .modern-datatable thead th,
        .modern-datatable tbody td {
            padding: 0.75rem 1rem !important;
        }

        .action-buttons-container {
            flex-wrap: nowrap !important;
            gap: 0.3rem !important;
            overflow: visible !important;
            min-width: fit-content !important;
        }

        .action-btn {
            width: 30px !important;
            height: 30px !important;
            font-size: 0.7rem !important;
            flex-shrink: 0 !important;
            border-radius: 5px !important;
        }

        .page-title {
            font-size: 1.5rem !important;
        }
    }

    @media (max-width: 576px) {
        .table-title {
            font-size: 1.125rem !important;
        }

        .modern-datatable thead th,
        .modern-datatable tbody td {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.8rem !important;
        }

        .status-badge {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.7rem !important;
        }

        .action-btn {
            width: 26px !important;
            height: 26px !important;
            font-size: 0.65rem !important;
            flex-shrink: 0 !important;
            border-radius: 4px !important;
        }

        .btn-modern {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
        }
    }
</style>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let table;

        // Function to initialize custom functionality after DataTable is ready
        function initializeCustomFeatures() {
            table = $('#lwBotReplyList').DataTable();

            if (!table) {
                // If DataTable is not ready yet, wait a bit and try again
                setTimeout(initializeCustomFeatures, 100);
                return;
            }

            // Update entries per page dropdown
            $('#entries-per-page').val(table.page.len());

            // Update table info
            updateTableInfo();

            // Add modern styling to table
            addModernTableStyling();

            // Bind custom event handlers
            bindCustomEventHandlers();
        }

        // Entries per page change handler
        $('#entries-per-page').on('change', function() {
            if (table) {
                table.page.len($(this).val()).draw();
            }
        });

        // Search functionality
        $('#table-search').on('keyup', function() {
            if (table) {
                table.search(this.value).draw();
            }
        });

        // Bind custom event handlers
        function bindCustomEventHandlers() {
            // Listen for DataTable draw events
            $('#lwBotReplyList').on('draw.dt', function() {
                updateTableInfo();
                animateTableRows();
                addModernTableStyling();
            });
        }

        // Update table info
        function updateTableInfo() {
            if (!table) return;

            const info = table.page.info();
            const start = info.start + 1;
            const end = info.end;
            const total = info.recordsTotal;
            const filtered = info.recordsDisplay;

            let infoText = `{{ __tr('Showing') }} ${start} {{ __tr('to') }} ${end} {{ __tr('of') }} ${filtered} {{ __tr('entries') }}`;
            if (filtered !== total) {
                infoText += ` ({{ __tr('filtered from') }} ${total} {{ __tr('total entries') }})`;
            }

            $('#table-info-text').text(infoText);
        }

        // Animate table rows
        function animateTableRows() {
            const rows = document.querySelectorAll('#lwBotReplyList tbody tr');
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.classList.add('animate__animated', 'animate__fadeInUp');
                    row.style.opacity = '1';
                }, index * 50);
            });
        }

        // Initialize animations for main buttons
        const buttons = document.querySelectorAll('.btn-modern');
        buttons.forEach((button, index) => {
            setTimeout(() => {
                button.classList.add('animate__animated', 'animate__fadeIn');
                button.style.opacity = '1';
            }, index * 150);
        });

        // Add hover effects to modern buttons
        $(document).on('mouseenter', '.btn-modern', function() {
            $(this).addClass('animate__animated animate__pulse');
        }).on('mouseleave', '.btn-modern', function() {
            $(this).removeClass('animate__animated animate__pulse');
        });

        // Status badge animations
        $('#lwBotReplyList').on('draw.dt', function() {
            const statusBadges = document.querySelectorAll('.status-badge');
            statusBadges.forEach((badge, index) => {
                badge.classList.remove('animate__fadeIn');
                void badge.offsetWidth;

                setTimeout(() => {
                    badge.classList.add('animate__fadeIn');

                    badge.addEventListener('mouseenter', function() {
                        this.classList.add('animate__pulse');
                    });

                    badge.addEventListener('mouseleave', function() {
                        this.classList.remove('animate__pulse');
                    });

                    if (badge.classList.contains('status-approved')) {
                        const icon = badge.querySelector('.status-icon');
                        icon.classList.add('animate__animated', 'animate__bounceIn');
                        setTimeout(() => {
                            icon.classList.remove('animate__bounceIn');
                        }, 1000);
                    }
                }, index * 150);
            });
        });

        // Action button hover effects
        $(document).on('mouseenter', '.action-btn', function() {
            $(this).addClass('action-btn-hover');
        }).on('mouseleave', '.action-btn', function() {
            $(this).removeClass('action-btn-hover');
        });

        // Add modern table styling function
        function addModernTableStyling() {
            // Add hover effects to table rows
            $('#lwBotReplyList tbody tr').hover(
                function() {
                    $(this).addClass('table-row-hover');
                },
                function() {
                    $(this).removeClass('table-row-hover');
                }
            );

            // Add click effects to table headers
            $('#lwBotReplyList thead th').click(function() {
                $('#lwBotReplyList thead th').removeClass('sort-active');
                $(this).addClass('sort-active');
            });
        }

        // Add modern table row hover class
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .table-row-hover {
                    background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
                    transform: translateY(-1px) !important;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
                }
                .sort-active {
                    background: linear-gradient(135deg, #007bff, #0056b3) !important;
                    color: white !important;
                }
                .sort-active .sort-icon {
                    color: white !important;
                }
            `)
            .appendTo('head');

        // Start initialization after a short delay to ensure DataTable is ready
        setTimeout(initializeCustomFeatures, 500);
    });
</script>
@endsection