@extends('layouts.app')

@section('content')
<div class="container py-4 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Flow Preview</h2>
                    <a href="{{ route('whatsapp-flows.index') }}" class="btn btn-outline-primary">
                        <i class="fa fa-arrow-left"></i> Back to Flows
                    </a>
                </div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                   
                    
                    <div id="previewContainer" class="d-flex justify-content-center">
                        @if(isset($flow['preview_url']))
                            <div class="preview-wrapper">
                                <iframe 
                                    src="{{ $flow['preview_url'] }}"
                                    width="1000"
                                    height="1000"
                                    style="border: 1px solid #ddd; border-radius: 8px; background: #fff;"
                                    allow="clipboard-write"
                                ></iframe>
                            </div>
                        @else
                            <div class="alert alert-danger">
                                Preview URL not available. Please try again later.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.preview-wrapper {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
}

#previewContainer {
    min-height: 850px;
    width: 100%;
}

iframe {
    max-width: 100%;
    height: 800px;
    border: 1px solid #ddd !important;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>
@endsection
