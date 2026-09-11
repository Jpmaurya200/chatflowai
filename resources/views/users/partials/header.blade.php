<div class="header pt-4 pb-2">
    <div class="container-fluid">
        <div class="lw-modern-page-header">
            <div class="lw-title-wrap">
                @isset($title)
                <h1 class="lw-page-title mb-1">
                    <span>{{ $title }}</span>
                </h1>
                @endisset
                @if (isset($description) && $description)
                <p class="text-muted mb-0">{{ $description }}</p>
                @endif
            </div>
            @if(isset($actionBtn) && $actionBtn)
            <div class="lw-header-actions">
                {!! $actionBtn !!}
            </div>
            @endif
        </div>
    </div>
</div>