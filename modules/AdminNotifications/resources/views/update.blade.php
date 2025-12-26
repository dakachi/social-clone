<div class="modal fade" id="sendNotificationModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content actionForm" 
              action="{{ module_url("save") }}" 
              method="POST"
              data-call-success="Main.closeModal('sendNotificationModal'); Main.DataTable_Reload('#DataTable');">
            @csrf

            <div class="modal-header">
                <h1 class="modal-title fs-16">{{ __("Send Notification") }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="msg-errors"></div>

                <input type="text" class="form-control d-none" name="id"  value="{{ $result->id??"" }}">

                @empty($result)
                <div class="mb-3">
                    <label class="form-label">{{ __("Send To Users") }}</label>
                    <select name="user_ids[]" class="form-select h-auto" data-control="select2" data-select2-tags="true" multiple required>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->fullname }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ __('You can select multiple users') }}</div>
                </div>
                @endempty

                <div class="mb-3">
                    <label class="form-label">{{ __("Title") }}</label>
                    <input type="text" class="form-control" name="title" required placeholder="{{ __('Enter notification title') }}" value="{{ $result->title??"" }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __("Message") }}</label>
                    <textarea name="message" class="form-control input-emoji" rows="4" required placeholder="{{ __('Enter your message') }}">{{ $result->message??"" }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __("URL (Optional)") }}</label>
                    <input type="url" class="form-control" name="url" placeholder="{{ __("Enter your url notification") }}" value="{{ $result->url??"" }}">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Send') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    Main.Select2();
    Main.Emoji();
</script>
