@extends('layouts.app', ['title' => __tr('WhatsApp Flows')])

@section('content')
@include('users.partials.header', [
    'title' => __tr(''),
    'description' => '',
    'class' => 'col-lg-7'
])

<div class="container-fluid mt-lg--6">
    <div class="row mt-5">
        <!-- Header / Actions (match templates-list UI) -->
        <div class="col-xl-12 mb-3">
            <div class="d-flex align-items-center justify-content-between flex-nowrap mt-3">
                <h1 class="mb-0"><i class="fas fa-code-branch me-2" style="color: #0B7753;"></i> {{ __tr('WhatsApp Flows') }}</h1>
                <div class="d-flex align-items-center">
                    <a class="lw-btn btn btn-neo btn-neo-gradient-green" href="{{ route('whatsapp-flows.create') }}">
                        <i class="fa fa-plus"></i> {{ __tr('Create New Flow') }}
                    </a>
                    <button id="refresh-btn" class="lw-btn btn btn-neo btn-neo-gradient-ghost ml-2">
                        <i class="fa fa-sync-alt"></i> {{ __tr('Refresh') }}
                    </button>
                    <a class="lw-btn btn btn-neo btn-neo-gradient-green ml-2" target="_blank" href="https://business.facebook.com/wa/manage/flows?waba_id={{ getVendorSettings('whatsapp_business_account_id') }}">
                        {{ __tr('Manage Flows on Meta') }} <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- /Header / Actions -->

        @if (session('status'))
            <div class="col-xl-12">
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="col-xl-12">
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="col-xl-12">
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="col-xl-12">
            <div class="modern-table-container">
                <!-- Header Controls: Show entries & Search -->
                <div class="table-header-controls">
                    <div class="entries-control">
                        <label for="wf-entries-per-page" class="mb-0">{{ __tr('Show') }}</label>
                        <select id="wf-entries-per-page" class="entries-select">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                        </select>
                        <span class="entries-text">{{ __tr('entries') }}</span>
                    </div>
                    <div class="search-control">
                        <input type="text" id="wf-table-search" class="search-input" placeholder="{{ __tr('Search...') }}">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="loading" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">{{ __tr('Fetching flows...') }}</p>
                    </div>

                    <div id="flows-container">
                        @if(empty($flows))
                            <div class="alert alert-info">
                                {{ __tr('No WhatsApp flows found. Try refreshing or check your API connection.') }}
                            </div>
                        @else
                            <div class="table-responsive">
                                <table id="flowsTable" class="table modern-datatable table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ __tr('ID') }}</th>
                                            <th>{{ __tr('Name') }}</th>
                                            <th>{{ __tr('Status') }}</th>
                                            <th>{{ __tr('Categories') }}</th>
                                            <th class="text-right">{{ __tr('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($flows as $flow)
                                            <tr class="{{ $flow['status'] === 'PUBLISHED' ? 'table-success' : '' }}" data-flow-id="{{ $flow['id'] }}">
                                                <td>{{ $flow['id'] }}</td>
                                                <td>{{ $flow['name'] }}</td>
                                                <td>
                                                    @if($flow['status'] === 'PUBLISHED')
                                                        <span class="badge badge-success p-2">
                                                            <i class="fa fa-check-circle"></i> {{ $flow['status'] }}
                                                        </span>
                                                    @elseif($flow['status'] === 'DRAFT')
                                                        <span class="badge badge-warning p-2">
                                                            <i class="fa fa-clock"></i> {{ $flow['status'] }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-info p-2">
                                                            {{ $flow['status'] }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @foreach($flow['categories'] as $category)
                                                        <span class="badge badge-info mr-1">{{ $category }}</span>
                                                    @endforeach
                                                </td>
                                                <td class="text-right">
                                                    <div class="wf-action-buttons">
                                                        @if($flow['status'] !== 'DEPRECATED')
                                                            <a href="{{ route('whatsapp-flows.send', ['id' => $flow['id']]) }}" class="wf-action-btn wf-btn-send" title="{{ __tr('Send') }}">
                                                                <i class="fa fa-paper-plane"></i><span class="d-none d-sm-inline">{{ __tr('Send') }}</span>
                                                            </a>
                                                        @endif

                                                        <a href="{{ route('whatsapp-flows.preview', ['id' => $flow['id']]) }}" class="wf-action-btn wf-btn-preview" title="{{ __tr('Preview') }}">
                                                            <i class="fa fa-eye"></i><span class="d-none d-sm-inline">{{ __tr('Preview') }}</span>
                                                        </a>

                                                        @if($flow['status'] === 'DRAFT')
                                                            <a href="{{ route('whatsapp-flows.edit', ['id' => $flow['id']]) }}" class="wf-action-btn wf-btn-edit" title="{{ __tr('Edit') }}">
                                                                <i class="fa fa-pencil"></i><span class="d-none d-sm-inline">{{ __tr('Edit') }}</span>
                                                            </a>
                                                            <form action="{{ route('whatsapp-flows.delete', ['id' => $flow['id']]) }}" method="POST" class="m-0 d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="wf-action-btn wf-btn-delete delete-flow-btn" data-flow-id="{{ $flow['id'] }}" data-flow-name="{{ $flow['name'] }}" title="{{ __tr('Delete') }}">
                                                                    <i class="fa fa-trash"></i><span class="d-none d-sm-inline">{{ __tr('Delete') }}</span>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Flow Template -->
        <script type="text/template" id="lwDeleteFlow-template">
            <h2>{{ __tr('Are You Sure!') }}</h2>
            <p>{{ __tr('You want to delete this WhatsApp Flow?') }}</p>
        </script>
        <!-- /Delete Flow Template -->
    </div>
</div>

<style>
    /* Modern Table Container & Controls */
    .modern-table-container { background: #ffffff; border-radius: 12px; border: 1px solid #e9ecef; box-shadow: 0 4px 18px rgba(2,6,23,0.06); overflow: hidden; }
    .table-header-controls { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; background: linear-gradient(135deg,#f9fafb,#f3f4f6); border-bottom: 1px solid #e9ecef; }
    .entries-control { display: flex; align-items: center; gap: .5rem; color: #6b7280; }
    .entries-select { padding: .35rem .6rem; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; color: #111827; min-width: 64px; }
    .entries-text { font-size: .9rem; color: #6b7280; }
    .search-control { position: relative; }
    .search-input { padding: .5rem .9rem .5rem 2.2rem; border: 1px solid #cbd5e1; border-radius: 10px; min-width: 220px; }
    .search-input:focus { outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,.12); border-color: #93c5fd; }
    .search-icon { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); color: #9ca3af; }

    /* Modern DataTable styles */
    .modern-datatable { width: 100% !important; border-collapse: collapse !important; }
    .modern-datatable thead th { background: #f8f9fa !important; border: none !important; padding: 1rem 1.25rem !important; font-weight: 700 !important; font-size: .85rem !important; color: #495057 !important; text-transform: uppercase !important; letter-spacing: .4px !important; }
    .modern-datatable tbody td { padding: .9rem 1.25rem !important; vertical-align: middle !important; font-size: .92rem !important; color: #334155 !important; border-top: 1px solid #eef2f7 !important; }
    .modern-datatable tbody tr:hover { background: #fbfbfd !important; }

    /* Hide native DataTables controls (using custom ones) */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { display: none !important; }

    /* Actions dropdown */
    .action-kebab-btn { border: 1px solid #e5e7eb; }
    .action-kebab-btn:hover { background: #f3f4f6; }
    .action-dropdown .dropdown-menu { min-width: 220px; border-radius: 10px; }

    @media (max-width: 768px){
        .search-input{ min-width: 140px; }
    }

    /* Action buttons (modern & responsive) */
    .wf-action-buttons { display: inline-flex; gap: .35rem; align-items: center; flex-wrap: nowrap; }
    .wf-action-btn { 
        display: inline-flex; align-items: center; gap: .35rem; 
        padding: .45rem .6rem; border-radius: 8px; border: 1px solid #e5e7eb; 
        background: #ffffff; color: #111827; font-weight: 600; font-size: .82rem; 
        transition: transform .15s ease, box-shadow .2s ease, background .2s ease; text-decoration: none;
    }
    .wf-action-btn i { font-size: .85rem; }
    .wf-action-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(2,6,23,.08); background: #f9fafb; text-decoration: none; }
    .wf-btn-send { border-color: rgba(34,160,107,.25); color: #0b7753; }
    .wf-btn-send:hover { background: linear-gradient(135deg,#e8fff6,#f6fffb); }
    .wf-btn-preview { border-color: #d1d5db; }
    .wf-btn-edit { border-color: rgba(59,130,246,.25); color: #1d4ed8; }
    .wf-btn-edit:hover { background: linear-gradient(135deg,#eef2ff,#f5f8ff); }
    .wf-btn-delete { border-color: rgba(239,68,68,.28); color: #b91c1c; }
    .wf-btn-delete:hover { background: linear-gradient(135deg,#fff1f2,#fff5f5); }

    /* Tighten buttons on small screens */
    @media (max-width: 576px){
        .wf-action-btn { padding: .4rem .5rem; border-radius: 7px; }
    }

    /* Modern gradient buttons */
    .btn-neo { border-radius: 12px; font-weight: 600; padding: 0.65rem 1.1rem; transition: transform 200ms ease, box-shadow 200ms ease, background-position 350ms ease, color 180ms ease; line-height: 1.25rem; }
    .btn-neo i { margin-right: .4rem; }
    .btn-neo-gradient-green { color: #ffffff !important; border: 0; background-image: linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%); background-size: 200% 200%; box-shadow: 0 2px 8px rgba(11, 119, 83, 0.18); }
    .btn-neo-gradient-green:hover, .btn-neo-gradient-green:focus { background-position: right center; transform: translateY(-1px); box-shadow: 0 10px 24px rgba(11, 119, 83, 0.28); }
    .btn-neo-gradient-green:active { transform: translateY(0); box-shadow: 0 6px 14px rgba(11, 119, 83, 0.22); }
    .btn-neo-gradient-ghost { color: #0B7753 !important; border: 2px solid transparent; background: linear-gradient(#ffffff, #ffffff) padding-box, linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%) border-box; box-shadow: 0 2px 8px rgba(11, 119, 83, 0.10); }
    .btn-neo-gradient-ghost:hover, .btn-neo-gradient-ghost:focus { color: #ffffff !important; background: linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%) padding-box, linear-gradient(135deg, #1ad19a 0%, #12b07e 50%, #0B7753 100%) border-box; transform: translateY(-1px); box-shadow: 0 10px 24px rgba(11, 119, 83, 0.22); }
    .btn-neo-gradient-ghost:active { transform: translateY(0); box-shadow: 0 6px 14px rgba(11, 119, 83, 0.18); }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const refreshBtn = document.getElementById('refresh-btn');
        const loadingElement = document.getElementById('loading');
        const flowsContainer = document.getElementById('flows-container');

        // Initialize delete buttons
        document.querySelectorAll('.delete-flow-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Get the form element
                const form = this.closest('form');
                
                // Get flow details from data attributes
                const flowId = this.getAttribute('data-flow-id');
                const flowName = this.getAttribute('data-flow-name');
                
                // Show confirmation
                console.log('Showing confirmation dialog for flow:', flowName);
                if (confirm('{{ __tr("Are you sure you want to delete flow") }} "' + flowName + '"? {{ __tr("This action cannot be undone.") }}')) {
                    console.log('User confirmed deletion of flow:', flowName);
                    // Submit the form directly
                    form.submit();
                } else {
                    console.log('User cancelled deletion of flow:', flowName);
                }
            });
        });

        // Refresh Functionality
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                loadingElement.classList.remove('d-none');
                flowsContainer.classList.add('d-none');
                window.location.reload();
            });
        }

        // Initialize DataTable and wire custom controls
        if (typeof $.fn.DataTable !== 'undefined') {
            const table = $('#flowsTable').DataTable({
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
                language: { paginate: { next: '<i class="fa fa-angle-right"></i>', previous: '<i class="fa fa-angle-left"></i>' } }
            });

            // Custom controls
            const entriesSelect = document.getElementById('wf-entries-per-page');
            const searchInput = document.getElementById('wf-table-search');
            if (entriesSelect) {
                entriesSelect.value = table.page.len();
                entriesSelect.addEventListener('change', function(){
                    const len = parseInt(this.value, 10) || 10;
                    table.page.len(len).draw();
                });
            }
            if (searchInput) {
                searchInput.addEventListener('keyup', function(){
                    table.search(this.value).draw();
                });
            }
            $('#flowsTable').on('draw.dt', function(){
                if (entriesSelect) entriesSelect.value = table.page.len();
            });
        }
    });
</script>
@endsection