<div class="border-bottom mb-1 pt-5 bg-polygon">
    
    <div class="container">
        <div class="d-flex justify-content-center align-self-center mb-4">
        
            <div class="d-flex flex-column justify-content-center align-self-center">
                <div class="size-120 size-child b-r-100 mb-2 mx-auto border border-primary-200 p-1">
                    <img src="{{ Media::url($user->avatar) }}" class="b-r-100">
                </div>

                <div class="fw-6  mx-auto mb-2">{{ $user->fullname }}</div>

                <div class="d-flex flex-nowrap align-self-center mx-auto gap-16 fs-14 text-gray-600">
                    <div class="d-flex gap-8 align-self-center fw-5"><span><i class="text-gray-900 fa-light fa-user"></i></span> <span>{{ $user->username }}</span></div>
                    <div class="d-flex gap-8 align-self-center fw-5"><span><i class="text-gray-900 fa-light fa-envelope"></i></span> <span>{{ $user->email }}</span></div>
                    <div class="d-flex gap-8 align-self-center fw-5"><span><i class="text-gray-900 fa-light fa-box-open"></i></span> <span>{{ $user->plan->name ?? __("No Plan") }}</span></div>
                </div>
            </div>
        </div>
        <nav class="flex items-center justify-center">
            <div class="nav nav-tabs border-bottom-0">
                <a href="{{ module_url() }}" class="nav-link px-2 {{ Request::segment(3) == ""?"active":"" }}" >
                    <i class="fa-light fa-user me-1 fs-16"></i> {{ __('Account') }}
                </a>
                <a href="{{ module_url('plan') }}" class="nav-link px-2 {{ Request::segment(3) == "plan"?"active":"" }}">
                    <i class="fa-light fa-box-open me-1 fs-16"></i> {{ __('Plan') }}
                </a>
                <a href="{{ module_url('billing') }}" class="nav-link px-2 {{ Request::segment(3) == "billing"?"active":"" }}">
                    <i class="fa-light fa-file-invoice me-1 fs-16"></i> {{ __('Billing') }}
                </a>
                <a href="{{ module_url('settings') }}" class="nav-link px-2 d-none {{ Request::segment(3) == "settings"?"active":"" }}">
                    <i class="fa-light fa-gear me-1 fs-16"></i> {{ __('Settings') }}
                </a>
            </div>
        </nav>
    </div>

</div>