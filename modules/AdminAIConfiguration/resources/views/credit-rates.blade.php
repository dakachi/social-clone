@php
    $providers  = \AI::getPlatforms();
    $modelList  = collect($providers)->mapWithKeys(fn($title, $provider) => [
        $provider => \AI::getAvailableModels($provider)
    ]);
@endphp

<div class="card shadow-none border-gray-300 mb-4">
    <div class="card-header fw-6 fs-18">
        {{ __("AI Model Rates") }}
    </div>
    <div class="card-body">

        {{-- Hướng dẫn --}}
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-body">
                <ul class="mb-0 fs-14">
                    <li>
                        <b>{{ __("Purpose:") }}</b>
                        {{ __("Customize the conversion rate from token to credit for each AI model to control AI usage costs in your system.") }}
                    </li>
                    <li class="mt-3">
                        <b>{{ __("How to use:") }}</b>
                        {{ __("For each model, enter the number of credits that will be deducted for each token used.") }}<br>
                        <span class="text-900">{{ __("Example:") }}</span>
                        <b>1</b> {{ __("means 1 token = 1 credit (default);") }}
                        <b>20</b> {{ __("means 20 tokens = 1 credit (using this model will cost 20x).") }}
                    </li>
                    <li class="mt-3">
                        <b>{{ __("Note:") }}</b>
                        {{ __("If you leave a field blank, the system will use the default value of 1 credit/token.") }}<br>
                        {{ __("You can adjust this rate at any time to suit your pricing strategy or cost control needs.") }}
                    </li>
                </ul>
            </div>
        </div>

        {{-- Provider + Models --}}
        @foreach($providers as $provider => $title)
            <div class="fw-6 mb-3 mt-20 fs-18 text-primary border-bottom pb-1">
                {{ __($title) }}
            </div>

            @foreach(($modelList[$provider] ?? []) as $category => $models)
                <div class="mb-3">
                	<div class="mb-2 text-muted fw-5 fs-13">
	                    {{ __("Category:") }} <span class="text-dark">{{ ucfirst($category) }}</span>
	                </div>

	                @foreach($models as $modelKey => $info)
	                    <div class="mb-2">
	                        <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center fs-14 gap-16 shadow-sm hover-shadow transition">
	                            <div>
	                                <div class="fw-6">{{ $info['name'] ?? $modelKey }}</div>
	                                <small class="text-muted fs-12">
	                                    {{ __("API Type:") }} {{ $info['api_type'] ?? 'n/a' }}
	                                </small>
	                            </div>
	                            <div class="text-end">
	                                <label class="form-label fs-11 text-muted mb-1 d-block">
	                                    {{ __("Credits/Token") }}
	                                </label>
	                                <input type="number"
	                                       step="0.01"
	                                       min="0.01"
	                                       class="form-control text-end w-100"
	                                       style="max-width: 90px"
	                                       name="credit_rates[{{ $modelKey }}]"
	                                       value="{{ old("credit_rates.$modelKey", $rates[$modelKey] ?? 1) }}"
	                                       placeholder="1"
	                                       required>
	                            </div>
	                        </div>
	                    </div>
	                @endforeach
                </div>
            @endforeach
        @endforeach

    </div>
</div>
