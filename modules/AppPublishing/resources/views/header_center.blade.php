@section('header_center')
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <button class="size-40 px-2 navbar-toggler border shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-light fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav py-2">
                @canany('apppublishingcampaigns', 'apppublishinglabels')
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-6 text-uppercase fs-12  {{ Request::segment(3)==""?"text-primary":"" }}" aria-current="page" href="{{ url_app("publishing") }}">
                        {{ __("Schedules") }}
                    </a>
                </li>
                @endcanany

                @can("apppublishingcampaigns")
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-6 text-uppercase fs-12 {{ Request::segment(3)=="campaigns"?"text-primary":"" }}" aria-current="page" href="{{ url_app("publishing/campaigns") }}">
                        {{ __("Campaigns") }}
                    </a>
                </li>
                @endcan

                @can("apppublishinglabels")
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-6 text-uppercase fs-12 {{ Request::segment(3)=="labels"?"text-primary":"" }}" aria-current="page" href="{{ url_app("publishing/labels") }}">
                        {{ __("Labels") }}
                    </a>
                </li>
                @endcan

                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-6 text-uppercase fs-12 {{ Request::segment(3)=="draft"?"text-primary":"" }}" aria-current="page" href="{{ url_app("publishing/draft") }}">
                        {{ __("Draft") }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
@endsection