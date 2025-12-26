<script type="text/javascript">
@if(get_option('file_addobe_express_status') && Access::permission('appfiles.image_editor'))
	Files.loadAdobeExpress('{{ get_option('file_addobe_express_app_name') }}', '{{ get_option('file_addobe_express_api_key') }}');
@endif
@if(get_option('file_onedrive_status') && Access::permission('appfiles.onedrive'))
	Files.openOneDriveActions('{{ get_option('file_onedrive_api_key') }}');
@endif
@if(get_option('file_google_drive_status') && Access::permission('appfiles.google_drive'))
	Files.pickGoogleDriveFile('{{ get_option('file_google_drive_api_key') }}', '{{ get_option('file_google_drive_client_id') }}');
@endif
@if(get_option('file_dropbox_status') && Access::permission('appfiles.dropbox'))
	Files.Dropbox('{{ get_option('file_dropbox_api_key') }}');
@endif

</script>