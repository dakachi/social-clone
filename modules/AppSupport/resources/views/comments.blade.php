@php
$user_id = Auth::user()->id;
@endphp

@if($comments["pagination"]["last_page"] == $page || ($comments["pagination"]["last_page"] == 0 && $page == 1))

    @if($user_id == $ticket->user_id)
        <div class="d-flex justify-content-end gap-8 align-items-top mb-4">
            <div>
                <div class="d-flex align-items-center gap-8 mb-2 justify-content-end">
                    <div class="fw-5 fs-12">
                        {{ __("You") }}
                    </div>
                    <div class="size-4 bg-gray-300 b-r-100"></div>
                    <div class="text-gray-600 fs-12">{{ time_elapsed_string($ticket->created) }}</div>
                </div>
                <div class="bg-gray-100 p-2 border b-r-6 fs-13 max-w-450 wp-100 width-wrap">
                    {!! $ticket->content !!}
                </div>
            </div>
        </div>
    @else
        <div class="d-flex justify-content-start gap-8 align-items-top mb-4">
            <div class="size-32 size-child">
                <img src="{{ Media::url( $ticket->user_avatar ) }}" class="border rounded-circle">
            </div>

            <div>
                <div class="d-flex align-items-center gap-8 mb-2">
                    <div class="fw-5 fs-12">
                        {{ $ticket->user_fullname }}
                    </div>
                    <div class="size-4 bg-gray-300 b-r-100"></div>
                    <div class="text-gray-600 fs-12">{{ time_elapsed_string($ticket->created) }}</div>
                </div>
                <div class="bg-gray-100 p-2 border b-r-6 fs-13 max-w-450 wp-100 width-wrap">
                    {!! $ticket->content !!}
                </div>
            </div>
        </div>
    @endif
@endif

@if( !empty($comments["comments"]) )
   
    @foreach($comments["comments"] as $comment)

        @if($user_id == $comment->user_id)
            <div class="d-flex justify-content-end gap-8 align-items-top mb-4 comment-item">
                <div>
                    <div class="d-flex align-items-center gap-8 mb-2 justify-content-end">
                        <div class="fw-5 fs-12">
                            {{ __("You") }}
                        </div>
                        <div class="size-4 bg-gray-300 b-r-100"></div>
                        <div class="text-gray-600 fs-12">{{ time_elapsed_string($comment->changed) }}</div>
                    </div>
                    <div class="bg-gray-100 p-2 border b-r-6 fs-13 max-w-450 wp-100 width-wrap">
                        {!! $comment->comment !!}
                    </div>
                </div>
            </div>
        @else
            <div class="d-flex justify-content-start gap-8 align-items-top mb-4">
                <div class="size-32 size-child">
                    <img src="{{ Media::url( $comment->user_avatar ) }}" class="border rounded-circle">
                </div>

                <div>
                    <div class="d-flex align-items-center gap-8 mb-2">
                        <div class="fw-5 fs-12">
                            {{ $comment->user_fullname }}
                        </div>
                        <div class="size-4 bg-gray-300 b-r-100"></div>
                        <div class="text-gray-600 fs-12">{{ time_elapsed_string($comment->changed) }}</div>
                    </div>
                    <div class="bg-gray-100 p-2 border b-r-6 fs-13 max-w-450 wp-100 width-wrap">
                        {!! $comment->comment !!}
                    </div>
                </div>
            </div>
        @endif

    @endforeach

@endif