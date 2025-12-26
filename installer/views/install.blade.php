@extends('layout')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200" style="max-width: 900px; margin: 0 auto;">
        <div class="container mx-auto px-8 py-10">
            
            @include("steps/steps")

            <!-- Step Content -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-8 md:p-10">
                @include("steps/step1")
                @include("steps/step2")
                @include("steps/step3")
                @include("steps/step4")
            </div>
                
            @include("steps/navigation")
        </div>
    </div>
</div>
@endsection