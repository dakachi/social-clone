@php
	$countPostBlog = Home::countPostBlog();
	$blogs = Home::getBlogs();
	$categories = Home::getBlogCategories();
	$tags = Home::getBlogTags();
@endphp

<section class="py-24 md:pb-32 bg-blueGray-50">
    <!-- Search Popup Overlay -->
    <div class="search-overlay fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <form action="{{ url()->current() }}" method="GET">
                	<div class="flex justify-between items-center mb-4">
	                    <h3 class="text-xl font-bold font-heading">{{ __("Search") }}</h3>
	                    <button class="close-search text-gray-500 hover:text-gray-700">
	                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24">
	                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
	                        </svg>
	                    </button>
	                </div>
	                <div class="mb-4">
	                    <input class="px-4 py-3.5 w-full text-gray-400 font-medium placeholder-gray-400 bg-white outline-none border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300" type="text" name="keyword" placeholder="{{ __("Enter your keyword") }}"/>
	                </div>
	                <button type="submit" class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors">{{ __("Search") }}</button>
                </form>
            </div>
        </div>
    </div>
    <div class="container mx-auto px-4 py-8 relative z-20">
        <!-- Blog Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <h1 class="text-6xl md:text-8xl lg:text-10xl font-bold font-heading leading-none text-gray-900">{{ __("Blog") }}</h1>
                <button class="search-trigger p-3 bg-white rounded-full shadow-md hover:shadow-lg transition-shadow">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewbox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </div>
            <p class="mt-4 text-xl text-gray-600">{{ __("Discover our latest and most exciting articles.") }}</p>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="order-2 lg:col-span-1">
                <!-- Categories -->
				<div class="bg-white rounded-4xl p-6 mb-6 shadow-sm">
				    <h6 class="mb-6 text-lg font-bold font-heading leading-snug text-gray-900">{{ __("Categories") }}</h6>
				    <ul class="space-y-3">
				        <li>
				            <a href="{{ route('blogs') }}"
				               class="text-base transition-colors {{ (request()->routeIs('blogs')) ? 'text-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600' }}">
				                {{ __("All Categories") }} ({{ $countPostBlog }})
				            </a>
				        </li>
				        @foreach($categories as $cat)
				            <li>
			               		<a href="{{ url('blogs/'.$cat->slug) }}"
				                   class="text-base transition-colors {{ (Request::segment(2) == $cat->slug) ? 'text-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600' }}">
				                    {{ $cat->name }} ({{ $cat->articles_count }})
				                </a>
				            </li>
				        @endforeach
				    </ul>
				</div>

				<!-- Popular Tags -->
				<div class="bg-white rounded-4xl shadow-sm p-6 mb-6">
				    <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __("Popular Tags") }}</h3>
				    <div class="flex flex-wrap gap-2">
				        @foreach($tags as $tag)
				            <a href="{{ url('blogs/tag/'.$tag->slug) }}"
				               class="px-3 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-800 text-sm font-medium rounded-lg transition duration-200">
				                {{ $tag->name }}
				            </a>
				        @endforeach
				    </div>
				</div>
            </div>

			<div class="order-1 lg:col-span-3">
			    <!-- Blog Posts Grid -->
			    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
			        @forelse($blogs as $blog)
			            <article class="bg-white rounded-4xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
			                <a href="{{ route('blog.detail', $blog->slug) }}">
							    <img src="{{ $blog->thumbnail ? Media::url($blog->thumbnail) : 'https://placehold.co/400x250' }}" alt="Blog post image" class="w-full h-48 object-cover"/>
							</a>
			                <div class="p-6">
			                    <div class="flex items-center gap-2 mb-3">
			                        {{-- Category --}}
			                        @if($blog->category)
			                            <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded">{{ $blog->category->name }}</span>
			                        @endif
			                        <span class="text-sm text-gray-500">
			                            {{ $blog->created ? \Carbon\Carbon::parse($blog->created)->format('d M Y') : '' }}
			                        </span>
			                    </div>
			                    <h4 class="text-xl font-bold font-heading leading-snug text-gray-900 mb-3">
								    <a href="{{ route('blog.detail', $blog->slug) }}" class="hover:text-indigo-600 transition-colors">
								        {{ $blog->title }}
								    </a>
								</h4>
			                    <p class="text-base text-gray-600 mb-4">
			                        {{ Str::limit(strip_tags($blog->desc), 120) }}
			                    </p>
			                    <a href="{{ route('blog.detail', $blog->slug) }}" class="text-indigo-600 font-medium hover:text-indigo-700 transition-colors">
			                        {{ __("Read more") }}  →
			                    </a>
			                </div>
			            </article>
			        @empty
			            <div class="col-span-full flex flex-col items-center justify-center py-16">
			                <!-- Empty Icon SVG -->
							<svg width="150" height="150" fill="none" viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4 opacity-60">
							  <rect x="12" y="24" width="72" height="48" rx="8" fill="#F3F4F6"/>
							  <rect x="24" y="36" width="48" height="6" rx="3" fill="#E5E7EB"/>
							  <rect x="24" y="48" width="28" height="6" rx="3" fill="#E5E7EB"/>
							  <circle cx="76" cy="60" r="6" fill="#A5B4FC"/>
							  <path d="M16 72C16 68.6863 18.6863 66 22 66H74C77.3137 66 80 68.6863 80 72V74C80 75.1046 79.1046 76 78 76H18C16.8954 76 16 75.1046 16 74V72Z" fill="#E0E7FF"/>
							  <path d="M36 56H60" stroke="#A5B4FC" stroke-width="2" stroke-linecap="round"/>
							</svg>
			                <h3 class="text-2xl font-bold text-gray-500 mb-2">{{ __("No blog posts found") }}</h3>
			                <p class="text-gray-400 mb-6">{{ __("We couldn't find any blog posts matching your criteria.") }}</p>
			                <a href="{{ route('blogs') }}" class="inline-block px-5 py-3 bg-indigo-600 text-white rounded-lg font-semibold shadow hover:bg-indigo-700 transition">{{ __("Back to All Blogs") }}</a>
			            </div>
			        @endforelse
			    </div>
			    <!-- Pagination -->
			    <div class="flex justify-center items-center space-x-2">
			        {!! $blogs->links('partials.pagination') !!}
			    </div>
			</div>
        </div>
    </div>
</section>