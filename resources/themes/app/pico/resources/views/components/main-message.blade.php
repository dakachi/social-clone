{{-- SUCCESS --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm ps-4 pe-5 py-3 rounded-4" role="alert">
        <span class="me-3 d-flex align-items-center justify-content-center size-40 b-r-20 bg-success">
            <i class="fa fa-check-circle text-white fs-35"></i>
        </span>
        <span class="flex-grow-1 fs-16">
            {{ session('success') }}
        </span>
        <button type="button" class="btn-close ms-3 mt-7" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- ERROR --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm ps-4 pe-5 py-3 rounded-4" role="alert">
        <span class="me-3 d-flex align-items-center justify-content-center size-40 b-r-20 bg-danger">
            <i class="fa fa-times-circle text-white fs-35"></i>
        </span>
        <span class="flex-grow-1 fs-16">
            {{ session('error') }}
        </span>
        <button type="button" class="btn-close ms-3 mt-7" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- WARNING --}}
@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm ps-4 pe-5 py-3 rounded-4" role="alert">
        <span class="me-3 d-flex align-items-center justify-content-center size-40 b-r-20 bg-warning">
            <i class="fa fa-exclamation-triangle text-white fs-20"></i>
        </span>
        <span class="flex-grow-1 fs-16">
            {{ session('warning') }}
        </span>
        <button type="button" class="btn-close ms-3 mt-7" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- WARNING --}}
@if (session('info'))
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center shadow-sm ps-4 pe-5 py-3 rounded-4" role="alert">
        <span class="me-3 d-flex align-items-center justify-content-center size-40 b-r-20 bg-info">
            <i class="fa fa-exclamation-triangle text-white fs-20"></i>
        </span>
        <span class="flex-grow-1 fs-16">
            {{ session('info') }}
        </span>
        <button type="button" class="btn-close ms-3 mt-7" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif