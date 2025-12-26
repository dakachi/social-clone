@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $showPages = 2;
    @endphp
    <div class="flex justify-center items-center space-x-2 mt-8">

        {{-- Previous Page Link --}}
        @if ($currentPage == 1)
            <span class="px-3 py-2 text-gray-400 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->url($currentPage - 1) }}"
               class="px-3 py-2 text-gray-500 hover:text-gray-700 transition-colors"
               aria-label="Previous">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
        @endif

        {{-- First page + ... --}}
        @if ($currentPage > $showPages + 1)
            <a href="{{ $paginator->url(1) }}"
               class="px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                1
            </a>
            @if ($currentPage > $showPages + 2)
                <span class="px-4 py-2 text-gray-500">...</span>
            @endif
        @endif

        {{-- Page Numbers --}}
        @for ($i = max(1, $currentPage - $showPages); $i <= min($lastPage, $currentPage + $showPages); $i++)
            @if ($i == $currentPage)
                <span class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg">{{ $i }}</span>
            @else
                <a href="{{ $paginator->url($i) }}"
                   class="px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    {{ $i }}
                </a>
            @endif
        @endfor

        {{-- ... + Last page --}}
        @if ($currentPage < $lastPage - $showPages)
            @if ($currentPage < $lastPage - $showPages - 1)
                <span class="px-4 py-2 text-gray-500">...</span>
            @endif
            <a href="{{ $paginator->url($lastPage) }}"
               class="px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                {{ $lastPage }}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($currentPage == $lastPage)
            <span class="px-3 py-2 text-gray-400 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->url($currentPage + 1) }}"
               class="px-3 py-2 text-gray-500 hover:text-gray-700 transition-colors"
               aria-label="Next">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        @endif
    </div>
@endif