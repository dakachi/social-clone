@php
	$countPostBlog = Home::countPostBlog();
	$recentPlogs = Home::getRecentBlogs();
	$categories = Home::getBlogCategories();
	$tags = Home::getBlogTags();
	$blogDetail = Home::getBlogDetail();
@endphp

@section('pagetitle', $blogDetail->title)

<section class="py-24 md:pb-32 bg-blueGray-50">
	<div class="container mx-auto px-4 py-8 relative z-20">
	    <!-- Blog Detail Header -->
		<div class="mb-8">
		    <div class="mb-6">
		        <div class="flex flex-wrap items-center gap-2 mb-4">
		            @if($blogDetail->category)
		                <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-lg">
		                    {{ $blogDetail->category->name }}
		                </span>
		            @endif
		            @foreach($blogDetail->tags as $tag)
		                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-lg">
		                    {{ $tag->name }}
		                </span>
		            @endforeach
		        </div>
		        <h1 class="text-4xl lg:text-5xl font-bold font-heading tracking-px-n leading-tight text-gray-900 mb-4">
		            {{ $blogDetail->title }}
		        </h1>
		        <div class="flex flex-wrap items-center gap-6 text-gray-600">
		            <span>{{ __("Created at: ") }} {{ $blogDetail->created ? \Carbon\Carbon::parse($blogDetail->created)->format('d M, Y') : '' }}</span>
		        </div>
		    </div>
		</div>
	    <div class="grid lg:grid-cols-4 gap-8">
	    	 <!-- Sidebar -->
	        <div class="order-2 lg:col-span-1">
	            <!-- Categories -->
				<div class="bg-white rounded-4xl p-6 mb-6 shadow-sm">
				    <h6 class="mb-6 text-lg font-bold font-heading leading-snug text-gray-900">{{ __("Categories") }}</h6>
				    <ul class="space-y-3">
				    	<li>
			                <a href="{{ route('blogs') }}"
			                   class="text-base text-gray-600 hover:text-indigo-600 transition-colors">
			                   {{ __("All Categories") }} ({{ $countPostBlog }})
			                </a>
			            </li>
				        @foreach($categories as $cat)
				            <li>
				                <a href="{{ url('blogs/'.$cat->slug) }}"
				                   class="text-base text-gray-600 hover:text-indigo-600 transition-colors">
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
	            <!-- Recent Posts -->
				<div class="bg-white rounded-4xl shadow-sm p-6 mb-6">
				    <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __("Recent Posts") }}</h3>
				    <div class="space-y-4">
				        @foreach($recentPlogs as $blog)
				            <a href="{{ route('blog.detail', $blog->slug) }}" class="block group">
				                <div class="flex gap-3">
				                    <img src="{{ $blog->thumbnail ? Media::url($blog->thumbnail) : 'https://placehold.co/60x60' }}"
				                         alt="Post thumbnail"
				                         class="w-15 h-15 object-cover rounded-lg flex-shrink-0"/>
				                    <div>
				                        <h4 class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 transition duration-200 line-clamp-2">
				                            {{ $blog->title }}
				                        </h4>
				                        <p class="text-xs text-gray-500 mt-1">
				                            {{ $blog->created ? \Carbon\Carbon::parse($blog->created)->format('d M, Y') : '' }}
				                        </p>
				                    </div>
				                </div>
				            </a>
				        @endforeach
				    </div>
				</div>
	        </div>
	        <!-- Main Content -->
	        <div class="order-1 lg:col-span-3">
	            <article class="bg-white rounded-4xl shadow-sm p-8 mb-8">
				    <img src="{{ $blogDetail->thumbnail ? Media::url($blogDetail->thumbnail) : 'https://placehold.co/800x400' }}"
				         alt="Featured Image"
				         class="w-full h-64 object-cover rounded-lg mb-8"/>
				    <div class="prose prose-lg max-w-none">
				        {!! $blogDetail->content !!}
				    </div>
				    <!-- Tags Section -->
				    @if($blogDetail->tags && count($blogDetail->tags))
				    <div class="border-t pt-6 mt-8">
				        <h4 class="text-lg font-semibold text-gray-900 mb-3">{{ __("Tags:") }}</h4>
				        <div class="flex flex-wrap gap-2">
				            @foreach($blogDetail->tags as $tag)
				                <a href="{{ route('blog.tag', $tag->slug) }}"
				                   class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition duration-200">
				                    #{{ $tag->name }}
				                </a>
				            @endforeach
				        </div>
				    </div>
				    @endif
				</article>
	        </div>
	    </div>
	</div>
</section>