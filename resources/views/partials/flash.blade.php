@if(session('message'))
    <div class="alert alert-info alert-dismissible fade show fade-in" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i data-lucide="info" class="icon flex-shrink-0"></i>
            <span>{{ session('message') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i data-lucide="check-circle" class="icon flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i data-lucide="alert-circle" class="icon flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show fade-in" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i data-lucide="alert-triangle" class="icon flex-shrink-0"></i>
            <span>{{ session('warning') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show fade-in" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i data-lucide="info" class="icon flex-shrink-0"></i>
            <span>{{ session('info') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i data-lucide="alert-circle" class="icon flex-shrink-0 mt-1"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
