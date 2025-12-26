@foreach($members as $member)
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="position-relative bg-light border b-r-10 p-4 w-100">
            <div class="d-flex align-items-top justify-content-between mb-2">
                <div class="fs-28 text-warning">
                    @if($member->user->fullname ?? 0)
                        <div class="size-40 size-child">
                            <img src="{{ Media::url($member->user->avatar) }}" class="b-r-50 border">
                        </div>
                    @else
                        <div class="size-40 size-child">
                            <img src="{{ text2img($member->pending) }}" class="b-r-50 border">
                        </div>
                    @endif
                </div>
                <div class="position-relative">
                    <div class="dropdown">
                        <a class="dropdown-toggle dropdown-arrow-hide text-gray-900" data-bs-toggle="dropdown" data-bs-animation="fade" href="javascript:void(0);">
                            <i class="fa-light fa-grid-2"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-2 border-1 border-gray-300 max-w-80 w-100">
                            
                            <li>
                                <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem"
                                    href="{{ module_url('update') }}"
                                    data-id="{{ $member->id_secure }}"
                                    data-popup="updateMemberModal">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
                                    <span class="fw-5">{{ __("Edit") }}</span>
                                </a>
                            </li>
                            @if($member->status == 0)
                                <li>
                                    <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem"
                                        href="{{ module_url('resend-invite') }}"
                                        data-id="{{ $member->id_secure }}">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-envelope"></i></span>
                                        <span class="fw-5">{{ __("Resend Invite") }}</span>
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem"
                                    href="{{ module_url('destroy') }}"
                                    data-id="{{ $member->id_secure }}"
                                    data-call-success="Main.ajaxScroll(true)">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can"></i></span>
                                    <span class="fw-5">{{ __("Delete") }}</span>
                                </a>
                            </li>
                        </ul>       
                    </div>
                </div>
            </div>
            <div class="fw-5 fs-14 text-gray-800 mb-1 text-truncate">
                {{ $member->user->fullname ?? $member->user->username ?? $member->pending ?? 'N/A' }}
                <div class="fs-11">
                    {{ $member->user->email ?? $member->pending ?? '' }}
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between fw-5">
                <div class="fs-12 d-flex align-items-center gap-8">
                    <span>
                        @if($member->status == 0)
                            <span class="text-primary">{{ __("Pending") }}</span>
                        @elseif($member->status == 1)
                            <span class="text-success">{{ __("Active") }}</span>
                        @else
                            <span class="text-gray-600">{{ __("Unknown") }}</span>
                        @endif
                    </span>
                    <span class="d-inline-block size-4 b-r-50 bg-gray-400"></span> 
                    <span class="text-gray-600">
                        {{ is_array($member->permissions) ? count($member->permissions) : (is_string($member->permissions) ? count(json_decode($member->permissions, true) ?? []) : 0) }} {{ __("Permissions") }}
                    </span>
                </div>
                
            </div>
        </div>
    </div>
@endforeach

@if( $members->Total() == 0 && $members->currentPage() == 1)
<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
    <span class="fs-70 mb-3 text-primary">
        <i class="fa-light fa-users"></i>
    </span>
    <div class="fw-semibold fs-5 mb-2 text-gray-900">
        {{ __('No Team Members Yet') }}
    </div>
    <div class="text-body-secondary mb-4 text-center max-w-500">
        {{ __('Invite teammates to collaborate, assign roles, and work together efficiently. Shared access makes teamwork seamless and organized.') }}
    </div>
    <a class="btn btn-dark actionItem" href="{{ module_url('invite') }}" data-popup="inviteModal" >
        <i class="fa-light fa-user-plus me-1"></i> {{ __('Invite member') }}
    </a>
</div>
@endif