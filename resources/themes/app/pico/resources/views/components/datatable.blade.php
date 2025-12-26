@php
$Datatable
@endphp

<div class="container pb-5">
    <form class="actionMulti">
        <div class="card mt-5">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center w-100 gap-8">
                    <!-- Table Info Section -->
                    <div class="table-info"></div>
                    <div class="d-flex flex-wrap gap-8">
                        <!-- Search Section -->
                        @component('components.datatable_search', ['placeholder' => 'Search', 'searchField' => 'datatable_filter[search]']) @endcomponent
                        
                        <!-- Filters Dropdown -->
                        @component('components.datatable_filters', ['filters' => $Datatable['filters'] ?? []]) @endcomponent
                        
                        <!-- Status Filter -->
                        @component('components.datatable_select', ['name' => 'datatable_filter[status]', 'options' => $Datatable['status_filter']?? []  ]) @endcomponent
                        
                        <!-- Actions Dropdown -->
                        @component('components.datatable_actions', ['actions' => $Datatable['actions'] ?? []]) @endcomponent
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0 border-0">
                @if(!empty($Datatable['columns']))
                    <div class="table-responsive">
                        <!-- Table -->
                        @if(!isset($customTable) || !$customTable)
                            @component('components.datatable_table', ['element' => $Datatable['element'], 'columns' => $Datatable['columns'], 'url' => module_url('list')]) @endcomponent
                        @else
                            {{ $slot }}
                        @endif
                        
                    </div>
                @endif
            </div>

            <div class="card-footer justify-center border-top-0">
                <div class="d-flex flex-wrap justify-content-center align-items-center w-100 justify-content-md-between gap-20">
                    <div class="d-flex align-items-center gap-8 fs-14 text-gray-700 table-size"></div>
                    <div class="d-flex table-pagination"></div>
                </div>
            </div>
        </div>
    </form>
</div>