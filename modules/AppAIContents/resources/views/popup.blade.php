<div class="modal fade" id="aiContentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-xl modal-dialog-centered">
		<div class="modal-content">
			<input type="text" class="d-none" name="type" value="0">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("AI Contents") }}</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-0">


			        <div class="d-flex gap-0 max-h-800 min-h-600 hp-100">
			            <div class="ai-cate col d-none d-lg-block border-end">
			                <div class="d-flex flex-column flex-fill hp-100">
			                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom position-relative zIndex-3">
			                        <div class="fs-16 fw-5">{{ __("AI Templates") }}</div>
			                        <div class="d-block d-lg-none">
			                            <div class="btn btn-icon btn-sm btn-light btn-hover-danger b-r-50 a-rotate closeAICate">
			                                <i class="fa-light fa-xmark"></i>
			                            </div>
			                        </div>
			                    </div>

			                    <div class="d-flex flex-column flex-column-fluid overflow-y-auto p-3 fs-12 hp-100 position-relative ai-template-data ajax-pages" data-url="{{ module_url("categories") }}" data-resp=".ai-template-data">
			                        <div class="w-100 d-flex justify-content-center mt-120 fs-50 text-gray-600">
			                            <i class="fa-light fa-loader fa-spin"></i>
			                        </div>
			                    </div>
			                </div>
			            </div>
			            <div class="ai-form col d-flex d-lg-block flex-column">
			                <div class="d-flex flex-column flex-column-fluid ">
			                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom position-relative zIndex-3 d-block d-lg-none">
			                        <div class="fs-16 fw-5"><i class="fal fa-lightbulb"></i> {{ __("AI Content Generation") }}</div>

			                        <div>
			                            <a class="btn btn-outline btn-info btn-sm openAICate" href="javascript:void(0);">
			                                <span>{{ __('Templates') }}</span>
			                            </a>
			                        </div>
			                    </div>
			                    <form class="p-4 actionForm" action="{{ route("app.ai-contents.process", ['any' => 'popup']) }}" data-content="ai-result-data" data-call-success="AIContent.openResult();">
			                        <div class="row">
			                            <div class="col-md-12 mb-3">
			                                <label class="form-label">{{ __("Your prompt") }}</label>
			                                <textarea class="form-control p-2" rows ="4" name="prompt"></textarea>
			                            </div>

			                            @include("appaicontents::options", [
			                                "hashtags" => true,
			                                "total_result" => true,
			                            ])

			                            <div class="col-md-12">
			                                <button type="submit" class="btn btn-dark b-r-50 px-4">{{ __("Generate") }}</button>
			                            </div>
			                        </div>
			                    </form>
			                </div>
			            </div>
			            <div class="ai-result col d-none d-lg-block border-start ai-result-data">
			                <div class="d-flex flex-column flex-fill hp-100">
			                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom position-relative zIndex-3">
			                        <div class="fs-16 fw-5">{{ __("Get started") }}</div>
			                        <div class="d-block d-lg-none">
			                            <div class="btn btn-icon btn-sm btn-light btn-hover-danger b-r-50 a-rotate closeAIResult">
			                                <i class="fa-light fa-xmark"></i>
			                            </div>
			                        </div>
			                    </div>

			                    <div class="d-flex flex-column flex-column-fluid overflow-y-auto p-3 fs-14 hp-100 position-relative">
			                        <div class="mb-3">
			                            {{ __("Start by choosing a prompt from the Prompt Templates panel on the left. You can either use the random prompt button or create one manually.") }}
			                        </div>
			                        <div class="mb-3">
			                            {{ __("Craft or modify your prompt to specify the desired AI output. Click the Generate button to start the generation process.") }}
			                        </div>
			                        <div class="mb-3">
			                            {{ __("Five results have been generated for your prompt.") }}
			                        </div>
			                    </div>
			                </div>
			            </div>

			        </div>



			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
Main.ajaxPages();
</script>