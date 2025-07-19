@extends('layouts.app', ['title' => $pageTitle])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-200">{{ __tr('WhatsApp Commerce Settings') }}</h1>
                <div>
                    <button id="testCommerceConnection" class="btn btn-info btn-sm">
                        <i class="fas fa-plug"></i> {{ __tr('Test Connection') }}
                    </button>
                    <button id="fetchCommerceSettings" class="btn btn-primary btn-sm">
                        <i class="fas fa-sync"></i> {{ __tr('Fetch Settings') }}
                    </button>
                </div>
            </div>

            <!-- Commerce Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __tr('Commerce Status') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    @if($commerceSummary['is_commerce_enabled'])
                                        <i class="fas fa-check-circle text-success fa-2x"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger fa-2x"></i>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ __tr('Commerce Status') }}</h5>
                                    <p class="mb-0 text-muted">
                                        {{ $commerceSummary['is_commerce_enabled'] ? __tr('Enabled') : __tr('Disabled') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    @if($commerceSummary['catalog_id'])
                                        <i class="fas fa-store text-success fa-2x"></i>
                                    @else
                                        <i class="fas fa-store text-muted fa-2x"></i>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ __tr('Catalog') }}</h5>
                                    <p class="mb-0 text-muted">
                                        @if($commerceSummary['catalog_name'])
                                            {{ $commerceSummary['catalog_name'] }} ({{ $commerceSummary['product_count'] }} {{ __tr('products') }})
                                        @else
                                            {{ __tr('No catalog configured') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commerce Settings Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __tr('Commerce Configuration') }}</h6>
                </div>
                <div class="card-body">
                    <form id="commerceSettingsForm" class="lw-ajax-form lw-form" method="post" action="{{ route('vendor.whatsapp.commerce.update') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <x-lw.checkbox 
                                    id="enableWhatsAppCommerce" 
                                    name="enable_whatsapp_commerce" 
                                    :checked="$commerceSummary['is_commerce_enabled']" 
                                    data-lw-plugin="lwSwitchery" 
                                    :label="__tr('Enable WhatsApp Commerce')" 
                                />
                                
                                <x-lw.checkbox 
                                    id="autoSyncCommerceSettings" 
                                    name="auto_sync_commerce_settings" 
                                    :checked="$commerceSummary['auto_sync_enabled']" 
                                    data-lw-plugin="lwSwitchery" 
                                    :label="__tr('Auto-sync Commerce Settings')" 
                                />
                            </div>
                            <div class="col-md-6">
                                <x-lw.checkbox 
                                    id="catalogVisibility" 
                                    name="catalog_visibility" 
                                    :checked="$commerceSummary['is_catalog_visible']" 
                                    data-lw-plugin="lwSwitchery" 
                                    :label="__tr('Make Catalog Visible to Customers')" 
                                />
                                
                                <x-lw.checkbox 
                                    id="cartEnabled" 
                                    name="cart_enabled" 
                                    :checked="$commerceSummary['is_cart_enabled']" 
                                    data-lw-plugin="lwSwitchery" 
                                    :label="__tr('Enable Shopping Cart')" 
                                />
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __tr('Save Settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Catalog Information -->
            @if($commerceSummary['catalog_id'])
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __tr('Catalog Information') }}</h6>
                    <button id="loadCatalogProducts" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-boxes"></i> {{ __tr('View Products') }}
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>{{ __tr('Catalog ID:') }}</strong><br>
                            <code>{{ $commerceSummary['catalog_id'] }}</code>
                        </div>
                        <div class="col-md-4">
                            <strong>{{ __tr('Product Count:') }}</strong><br>
                            {{ $commerceSummary['product_count'] }}
                        </div>
                        <div class="col-md-4">
                            <strong>{{ __tr('Vertical:') }}</strong><br>
                            {{ $commerceSummary['catalog_vertical'] ?: __tr('Not specified') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catalog Products -->
            <div id="catalogProductsCard" class="card shadow mb-4" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __tr('Catalog Products') }}</h6>
                </div>
                <div class="card-body">
                    <div id="catalogProductsContainer">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="mt-2 text-muted">{{ __tr('Loading products...') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Help Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __tr('About WhatsApp Commerce') }}</h6>
                </div>
                <div class="card-body">
                    <p>{{ __tr('WhatsApp Commerce allows you to showcase your products directly in WhatsApp conversations. Customers can browse your catalog, add items to cart, and make purchases without leaving the chat.') }}</p>
                    
                    <h6>{{ __tr('Key Features:') }}</h6>
                    <ul>
                        <li>{{ __tr('Product catalog integration') }}</li>
                        <li>{{ __tr('Shopping cart functionality') }}</li>
                        <li>{{ __tr('Seamless customer experience') }}</li>
                        <li>{{ __tr('Order management through WhatsApp') }}</li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>{{ __tr('Note:') }}</strong> {{ __tr('To use WhatsApp Commerce, you need to have a Facebook Business Manager account with a product catalog configured.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test Commerce Connection
    document.getElementById('testCommerceConnection')?.addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __tr("Testing...") }}';
        
        fetch('{{ route("vendor.whatsapp.commerce.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.reaction === 1) {
                showSuccessMessage(data.data.message);
            } else {
                showErrorMessage(data.data.message);
            }
        })
        .catch(error => {
            showErrorMessage('{{ __tr("Connection test failed") }}');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    });

    // Fetch Commerce Settings
    document.getElementById('fetchCommerceSettings')?.addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __tr("Fetching...") }}';
        
        fetch('{{ route("vendor.whatsapp.commerce.fetch") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.reaction === 1) {
                showSuccessMessage(data.data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorMessage(data.data.message);
            }
        })
        .catch(error => {
            showErrorMessage('{{ __tr("Failed to fetch settings") }}');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    });

    // Load Catalog Products
    document.getElementById('loadCatalogProducts')?.addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;
        const productsCard = document.getElementById('catalogProductsCard');
        const productsContainer = document.getElementById('catalogProductsContainer');
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __tr("Loading...") }}';
        
        productsCard.style.display = 'block';
        productsContainer.innerHTML = `
            <div class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="mt-2 text-muted">{{ __tr('Loading products...') }}</p>
            </div>
        `;
        
        fetch('{{ route("vendor.whatsapp.commerce.products") }}?limit=10', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.reaction === 1 && data.data.products) {
                displayProducts(data.data.products);
            } else {
                productsContainer.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                        <p class="mt-2">${data.data.message || '{{ __tr("No products found") }}'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            productsContainer.innerHTML = `
                <div class="text-center text-danger">
                    <i class="fas fa-times-circle fa-2x"></i>
                    <p class="mt-2">{{ __tr("Failed to load products") }}</p>
                </div>
            `;
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    });

    function displayProducts(productsData) {
        const products = productsData.data || [];
        const container = document.getElementById('catalogProductsContainer');
        
        if (products.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-box-open fa-2x"></i>
                    <p class="mt-2">{{ __tr("No products found in catalog") }}</p>
                </div>
            `;
            return;
        }
        
        let html = '<div class="row">';
        products.forEach(product => {
            html += `
                <div class="col-md-4 mb-3">
                    <div class="card">
                        ${product.image_url ? `<img src="${product.image_url}" class="card-img-top" style="height: 200px; object-fit: cover;">` : ''}
                        <div class="card-body">
                            <h6 class="card-title">${product.name || 'Unnamed Product'}</h6>
                            <p class="card-text text-muted small">${product.description || 'No description'}</p>
                            ${product.price ? `<p class="card-text"><strong>${product.price} ${product.currency || ''}</strong></p>` : ''}
                            <small class="text-muted">ID: ${product.id}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        if (productsData.paging && productsData.paging.next) {
            html += `
                <div class="text-center mt-3">
                    <button class="btn btn-outline-primary" onclick="loadMoreProducts('${productsData.paging.cursors.after}')">
                        {{ __tr('Load More Products') }}
                    </button>
                </div>
            `;
        }
        
        container.innerHTML = html;
    }

    // Helper functions for notifications
    function showSuccessMessage(message) {
        // Implement your success notification system here
        alert('Success: ' + message);
    }

    function showErrorMessage(message) {
        // Implement your error notification system here
        alert('Error: ' + message);
    }
});
</script>
@endsection
