"use strict";

window.Files = window.Files || {};

var Files = new (function () 
{
    var Files = this;
    var Files_url = VARIABLES.url+'app/files/';
    var saveFilesFromCloudUrl = Files_url + 'save_file_from_cloud';
    var uploadFilesUrl = Files_url + 'upload_files';
    var CC_CDN_URL = "https://cc-embed.adobe.com/sdk/v4/CCEverywhere.js";
    var DROPBOXJS_URL = "https://www.dropbox.com/static/api/2/dropins.js";
    var OneDriveJS_URL = "https://js.live.net/v7.2/OneDrive.js";
    var GoogleDriveJS_URL = "https://apis.google.com/js/api.js";
    var dropboxApiKey = false;
    var pickerApiLoaded = false;
    var pickerApiKey = false;
    var pickerClientID = false;
    var pickerApiLoaded = false;
    var currentAjaxScroll;
    var oauthToken;
    var ImageEditor;
    var allowedExtensions = [
        '.pdf', '.doc', '.docx', '.csv', '.jpeg', 
        '.png', '.jpg', '.gif', '.svg', '.zip', 
        '.xls', '.xlsx', '.mp4', '.mov', '.mp3', 
        '.webp', '.ogg'
    ];

    Files.init = function( reload ) 
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': VARIABLES.csrf
            }
        });

        if(reload || reload == undefined){
           $(document).on('click', '.editImage', function() {
                var fileUrl = $(this).data('file');
                var id = $(this).data('id');
                var file_id = $(this).data('file-id');

                ImageEditor = new FilerobotImageEditor({
                    tools: [
                        'adjust',
                        'effects',
                        'filters',
                        'orientation',
                        'crop',
                        'resize',
                        'watermark',
                        'shapes',
                        'text'
                    ]
                });
                ImageEditor.open(fileUrl);
                $(`<button id="btnSaveImage" class="btn btn-success btn-sm px-4" data-file-id="${file_id}">Save</button>`).insertBefore( ".sc-fzoXWK.cwaaKE" );
            });

            $(document).on('click', '#btnSaveImage', function() {
                var file_id = $(this).data('file-id');
                var $canvas = $('.filerobot-edit-canvas').get(0);
                if (!$canvas) {
                    return;
                }
                var dataURL = $canvas.toDataURL('image/png');
                var blob = Files.dataURLtoBlob(dataURL);
                var formData = new FormData();
                var folder_id = $('[name="folder_id"]:checked').val();
                var randomName = Files.randomFileName('png');
                formData.append('files[]', blob, randomName);
                formData.append("folder_id", folder_id == undefined ? "0" : folder_id);

                Main.ajaxPost( $(this), uploadFilesUrl, formData, function(res) {
                    ImageEditor.close();
                    var file_id = file_id=="undefined" ? 0 : file_id;
                    console.log(file_id);
                    Main.ajaxScroll(true, file_id );
                });
            });
        }

        Files.doUpload();
        Files.Actions();
        Files.DragAndDropSelect();
        Files.enableGlobalDropUpload();
    },

    Files.Actions = function(){
        $(document).on("change", ".files .file-item input", function(event){
            var that = $(this);
            var parents = $(this).parents(".file-item");
            if(!parents.hasClass("selected")){
                if(that.is(":checked")){
                    Files.addFiles(parents);
                }else{
                    Files.unselectFiles(parents);
                }
            }
        });

        $(document).on("click", ".file-selected-media .file-item .remove", function(){
            Files.unselectFiles( $(this).parents(".file-item") );
        });

        $(document).on('click', '.btnAddFiles', function(e) {
            // Find the closest form with the 'actionForm' class
            var $form = $(this).closest('form');
            var $id = $form.data("id");

            // Gather values from checked checkboxes with name "id[]"
            var selectedValues = [];
            $form.find('input[name="id[]"]:checked').each(function() {
                selectedValues.push($(this).val());
                if($("." + $id).length > 0 && $("." + $id).hasClass("form-img")){
                   $("." + $id).css('background-image', 'url("'+$(this).val()+'")');
                }
            });

            // Join all selected values with a comma
            var joinedValues = selectedValues.join(',');

            // Set the joined values to the target input with data-id="thumbnail"
            // Adjust the selector if the input is outside the form or has a different identifier
            $('#' + $id).val(joinedValues);

            Main.closeModal('filesModal');
        });
    },

    Files.dataURLtoBlob = function(dataurl){
        var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
        while(n--) u8arr[n] = bstr.charCodeAt(n);
        return new Blob([u8arr], {type:mime});
    },

    Files.randomFileName = function(extension = 'png'){
        return 'edited-' + Date.now() + '-' + Math.random().toString(36).substr(2, 6) + '.' + extension;
    },

    Files.addFiles = function(file){
        if (!file.hasClass("file-folder-item")) {
            var id = file.attr("data-id");
            var name = file.attr("data-name");
            var select_multi = $(".files").attr("data-select-multi");

            if($(".btnAddFiles").length == 0 || $(".btnAddFiles").data("transfer") == ""){
                if(select_multi != undefined && select_multi == 0){
                    $(".file-manager").find(".fm-list-item").removeClass("active");
                    $(".file-manager").find(".fm-list-item input:checkbox").prop('checked',false);
                    $(".fm-selected-media#"+name+" .items").html("");
                }
            }

            if(file.hasClass("ui-draggable-handle")){
                file.draggable( "destroy" );
            }

            file.removeClass("ui-draggable-handle");
            file.find("input").prop('checked', true);
            var item = file.clone();

            item.find("label").removeAttr("for");
            item.find("input").attr("type", "text").attr("name", name+"[]").hide();
            item.find(".file-list-media").addClass("rounded");
            item.addClass("border rounded selected").removeClass('mb-3 ui-draggable-handle ui-droppable active');
            item.append('<button type="button" href="javascript:void(0)" class="remove bg-white border b-r-100 text-danger w-20 h-20 fs-12"><i class="fal fa-times"></i></button>');

            if($(".btnAddFiles").length == 0 || $(".btnAddFiles").data("transfer") == ""){
                var copy_item = item.clone();
                if(name != undefined && name != ""){
                    if( $(".file-selected-media#"+name+" .items .file-item[data-id="+copy_item.data("id")+"]").length == 0 ){
                        copy_item.appendTo(".file-selected-media#"+name+" .items");
                    }
                }
            }

            Files.checkSelectedEmpty();

            return item;
        }
    },

    Files.unselectFiles = function(file){
        var id = file.attr('data-id');
        if(!file.hasClass("file-item")){
            var id = file.parents(".file-item").attr('data-id');
        }

        $(".file-widget .file-item[data-id='"+id+"'] input").prop('checked', false);
        $(".file-selected-media .file-item[data-id='"+id+"']").remove();
        Files.checkSelectedEmpty();
        return file;
    },

    Files.checkSelectedEmpty = function(){
        if( $(".file-selected-media").find(".items .file-item").length > 0 ){
            $(".file-selected-media").find(".drophere").hide();
        }else{
            $(".file-selected-media").find(".drophere").show();
        }
    },

    Files.DragAndDropSelect = function(){
        $(".file-selected-media").find(".items").sortable({
            containment: "parent",
            cursor: "-webkit-grabbing",
            distance: 10,
            items: ".file-item",
            placeholder: "file-item item--placeholder",

            stop: function(event, ui) {
                if ($(".file-selected-media").find(".file-item").length > 0) {
                    $(".file-selected-media").removeClass('droppable');
                } else {
                    $(".file-selected-media").addClass('droppable');
                }
            },

            receive: function(event, ui) {
                ui.helper.remove();
                Files.addFiles(ui.item);
            },

            update: function() {
                if ($(".file-selected-media").find(".file-item").length == 0) {
                    $(".file-selected-media").addClass('none');
                }
            }
        });

        $(".file-widget").on("mouseover", ".file-item:not(.active,.fm-folder-item)", function (el) {
            var that = $(this);
            that.draggable({
                addClasses: false,
                connectToSortable: $(".file-selected-media").find(".items"),
                containment: "document",
                revert: "invalid",
                revertDuration: 200,
                distance: 10,
                appendTo: $(".file-selected-media").find(".items"),
                cursor: "-webkit-grabbing",
                cursorAt: { 
                    left: 35,
                    top: 35
                },
                zIndex: 1000,

                drop: function (event, ui) { 
                    console.log("added");
                },

                helper: function() {
                    var item = that.clone();

                    item.find("label").removeAttr("for");
                    item.find("input").attr("type", "text").attr("name", name+"[]").hide();
                    item.find(".file-list-media").addClass("rounded");
                    item.addClass("border rounded selected").removeClass('mb-3 ui-draggable-handle ui-droppable active');
                    item.append('<button type="button" href="javascript:void(0)" class="remove bg-white border b-r-100 text-danger w-20 h-20 fs-12"><i class="fal fa-times"></i></button>');

                    var copy_item = item.clone();
                    copy_item.appendTo(".file-selected-media .items");
                    copy_item.remove();

                    return item;
                },

                start: function(event, ui) {
                    $(".file-selected-media").find(".items").sortable("disable");
                    $(".file-list").addClass("draggable");
                    $(".file-selected-media").find(".drophere").show();
                    $(".file-selected-media").find(".drophere .has-action").show();
                    $(".file-selected-media").find(".drophere .no-action").hide();
                },

                stop: function(event, ui) {
                    $(".file-list").removeClass("draggable");
                    $(".file-selected-media").find(".drophere .has-action").hide();
                    $(".file-selected-media").find(".drophere .no-action").show();
                    $(".file-selected-media").find(".items").sortable("enable");
                    Files.checkSelectedEmpty();
                    that.draggable( "destroy" );
                }
            });
        });

        $(document).on("mouseout", ".file-widget .file-item", function (el) {
            var that = $(this);
            that.removeClass("ui-draggable-handle");
        });
    },

    Files.loadSuccess = function(result){
        var folder_id = $('[name="folder_id"]:checked').val();
        if(folder_id != undefined)
            $(".uploadFromURL").attr("data-id", folder_id);
    },

    Files.enableGlobalDropUpload = function() {
        var $overlay = $("#drag-overlay");

        // Khi kéo file vào body
        $("body").on("dragover", function(e) {
            e.preventDefault();
            e.stopPropagation();
            $overlay.addClass("active");
        });

        // Khi rời body
        $("body").on("dragleave", function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Kiểm tra nếu thực sự rời ra ngoài
            if (e.originalEvent.pageX === 0 && e.originalEvent.pageY === 0) {
                $overlay.removeClass("active");
            }
        });

        // Khi thả file vào body
        $("body").on("drop", function(e) {
            e.preventDefault();
            e.stopPropagation();
            $overlay.removeClass("active");

            var files = e.originalEvent.dataTransfer.files;
            if (!files || files.length === 0) return;

            var formData = new FormData();
            var folder_id = $('[name="folder_id"]:checked').val() || 0;
            formData.append("folder_id", folder_id);

            for (var i = 0; i < files.length; i++) {
                formData.append("files[]", files[i]);
            }

            // Gọi ajax upload
            Main.ajaxPost($(this), Files_url + "upload_files", formData, function(res) {
                Main.ajaxScroll(true);
            });
        });
    },

    Files.doUpload = function(upload_id) {
        var upload_id = (upload_id == undefined) ? '#file-upload' : upload_id;
        var upload_id_name = upload_id.replaceAll("#", "");

        $(document).on('change', upload_id, function() {
            var folder_id = $('[name="folder_id"]:checked').val();
            var form_data = new FormData();
            form_data.append("name", upload_id_name);
            form_data.append("folder_id", folder_id == undefined ? "0" : folder_id);

            var totalfiles = document.getElementById(upload_id_name).files.length;
            for (var index = 0; index < totalfiles; index++) {
                form_data.append("files[]", document.getElementById(upload_id_name).files[index]);
            }

            $(this).val('');

            var action = $(this).data('url') || Files_url + 'upload_files';

            Main.ajaxPost($(this), action, form_data, function(result) {});
            return false;
        });
    },

    Files.saveEditedImage = function(blob, id, callback) {
        var formData = new FormData();

        formData.append('image', blob, 'edited-image.jpg');
        formData.append('id', id); // hoặc file_id nếu bạn cần
        formData.append('folder_id', folder_id);

        $.ajax({
            url: Files_url + 'save_file', // endpoint đã có
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if(callback) callback(res);
                Main.notify('success', 'Đã lưu ảnh chỉnh sửa!');
            },
            error: function(xhr) {
                Main.notify('error', 'Lưu ảnh thất bại: ' + (xhr.responseText || ''));
            }
        });
    },

    Files.Dropbox = function(apiKey) {
        const DROPBOXJS_URL = "https://www.dropbox.com/static/api/2/dropins.js";

        // Nếu chưa load SDK thì load
        if (!document.getElementById("dropboxjs")) {
            const script = document.createElement("script");
            script.id = "dropboxjs";
            script.setAttribute("data-app-key", apiKey);
            script.src = DROPBOXJS_URL;
            script.onload = () => Files._watchDropboxChooser();
            document.body.appendChild(script);
        } else {
            Files._watchDropboxChooser();
        }
    },

    Files._watchDropboxChooser = function() {
        const chooserOptions = {
            success: function(files) {
                const fileData = files.map(file => ({
                    from: "dropbox",
                    link: file.link,
                    name: file.name,
                    type: file.name.split('.').pop()
                }));

                Files.saveFilesFromCloudToServer($("#dropbox-chooser"), fileData);
            },
            cancel: function() {},
            linkType: "direct",
            multiselect: true,
            extensions: [
                '.pdf', '.doc', '.docx', '.csv', '.jpeg', '.png', '.jpg',
                '.gif', '.svg', '.zip', '.xls', '.xlsx', '.mp4', '.mov',
                '.mp3', '.webp', '.ogg'
            ],
            folderselect: false
        };

        // Quan sát sự xuất hiện của nút #dropbox-chooser
        const observer = new MutationObserver(() => {
            const el = document.getElementById("dropbox-chooser");
            if (el && !el.dataset.bound) {
                el.dataset.bound = "true";
                el.addEventListener("click", function () {
                    if (typeof Dropbox !== "undefined") {
                        Dropbox.choose(chooserOptions);
                    }
                });
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    },

    Files.saveFilesFromCloudToServer = function($this, $files){
        var folder_id = $('[name="folder_id"]:checked').val();
        if(folder_id == undefined){
            folder_id = 0;
        }

        var formData = new FormData();

        $files.forEach(file => {
            formData.append('files[]', JSON.stringify(file));
        });

        formData.append('folder_id', folder_id);

        Main.ajaxPost( $this, saveFilesFromCloudUrl, formData, function(){

        });
    },

    /*ONE DRIVE*/
    Files.openOneDriveActions = function(clientId){
        ((document, url) => {
            // Create a new <script> element.
            const script = document.createElement("script");
            // Set the source URL to load the Dropbox SDK.
            script.src = url;
            // When the script loads, check if Dropbox is available
            script.onload = async () => {
                $(document).on("click", "#onedrive-chooser", function(){
                    Files.openOneDrivePicker(clientId);
                });
            };
            // Append the script to the body to load it.
            document.body.appendChild(script);
        })(document, OneDriveJS_URL);
    }

    Files.openOneDrivePicker = function(clientId) {
        var $this = $("#onedrive-chooser");
        var odOptions = {
            clientId: clientId,
            action: 'download',
            multiSelect: true,
            advanced: {
                redirectUri: VARIABLES.url + "app/files",
                filter: '.pdf,.doc,.docx,.csv,.jpeg,.png,.jpg,.gif,.svg,.zip,.xls,.xlsx,.mp4,.mov,.mp3,.webp,.ogg'
            },
            success: function(files) {

                var fileData = files.value.map(file => ({
                    from: "onedrive",
                    link: file["@microsoft.graph.downloadUrl"],
                    name: file['name'],
                    access_token: files.accessToken,
                    type: file['name'].split('.').pop()
                }));
                Files.saveFilesFromCloudToServer($this, fileData);
            },
            cancel: function() { /* cancel handler */ },
            error: function(e) { console.log(e); }
        };
        OneDrive.open(odOptions);
    },

    /* ================================
       GOOGLE DRIVE (GIS + Picker API)
    ================================ */
    Files.onGoogleDriveAuthApiLoad = function () {
        if (!window.google || !google.accounts || !google.accounts.oauth2) {
            console.error("Google Identity Services SDK not loaded.");
            return;
        }

        const client = google.accounts.oauth2.initTokenClient({
            client_id: pickerClientID,
            scope: "https://www.googleapis.com/auth/drive.readonly",
            callback: (tokenResponse) => {
                if (tokenResponse && tokenResponse.access_token) {
                    oauthToken = tokenResponse.access_token;
                    Files.createGoogleDrivePicker();
                } else {
                    console.error("Google Drive: Failed to get access token.");
                }
            },
        });

        // Request access token (must be triggered by user action)
        client.requestAccessToken();
    };

    Files.onGoogleDrivePickerApiLoad = function () {
        pickerApiLoaded = true;
    };

    Files.createGoogleDrivePicker = function () {
        if (pickerApiLoaded && oauthToken) {
            var picker = new google.picker.PickerBuilder()
                .addView(google.picker.ViewId.DOCS)
                .setOAuthToken(oauthToken)
                .setDeveloperKey(pickerApiKey)
                .setCallback(Files.pickerGoogleDriveCallback)
                .setSelectableMimeTypes(
                    allowedExtensions
                        .map((ext) => {
                            switch (ext) {
                                case ".pdf": return "application/pdf";
                                case ".doc": return "application/msword";
                                case ".docx": return "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
                                case ".csv": return "text/csv";
                                case ".jpeg":
                                case ".jpg": return "image/jpeg";
                                case ".png": return "image/png";
                                case ".gif": return "image/gif";
                                case ".svg": return "image/svg+xml";
                                case ".zip": return "application/zip";
                                case ".xls": return "application/vnd.ms-excel";
                                case ".xlsx": return "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                                case ".mp4": return "video/mp4";
                                case ".mov": return "video/quicktime";
                                case ".mp3": return "audio/mpeg";
                                case ".webp": return "image/webp";
                                case ".ogg": return "audio/ogg";
                                default: return "";
                            }
                        })
                        .filter(Boolean)
                        .join(",")
                )
                .build();
            picker.setVisible(true);
        } else {
            console.warn("Google Drive Picker not ready yet.");
        }
    };

    Files.pickerGoogleDriveCallback = function (data) {
        var $this = $("#google-drive-chooser");
        if (data.action === google.picker.Action.PICKED) {
            var files = data.docs
                .filter((file) => {
                    var extension = file.name.split(".").pop().toLowerCase();
                    return allowedExtensions.includes("." + extension);
                })
                .map((file) => ({
                    from: "google_drive",
                    link: file.id,
                    name: file.name,
                    type: file.name.split(".").pop(),
                    access_token: oauthToken,
                }));

            if (files.length) {
                Files.saveFilesFromCloudToServer($this, files);
            } else {
                console.error("No valid files selected.");
            }
        }
    };

    Files.pickGoogleDriveFile = function (apiKey, clientId) {
        pickerApiKey = apiKey;
        pickerClientID = clientId;

        // Load Google Identity Services (GIS)
        ((document, url) => {
            if (!document.getElementById("gis-sdk")) {
                const script = document.createElement("script");
                script.id = "gis-sdk";
                script.src = url;
                script.async = true;
                script.defer = true;

                script.onload = () => {
                    console.log("GIS loaded ✅");

                    // Sau khi GIS load xong mới bind click
                    $(document).on("click", "#google-drive-chooser", function () {
                        if (!pickerApiLoaded) {
                            console.warn("Google Picker chưa sẵn sàng");
                            return;
                        }
                        Files.onGoogleDriveAuthApiLoad();
                    });
                };

                document.body.appendChild(script);
            }
        })(document, "https://accounts.google.com/gsi/client");

        // Load Google API (Picker SDK)
        ((document, url) => {
            if (!document.getElementById("gapi-sdk")) {
                const script = document.createElement("script");
                script.id = "gapi-sdk";
                script.src = url;
                script.async = true;
                script.defer = true;

                script.onload = () => {
                    console.log("gapi loaded ✅");
                    gapi.load("picker", { callback: Files.onGoogleDrivePickerApiLoad });
                };

                document.body.appendChild(script);
            }
        })(document, GoogleDriveJS_URL);
    };

    Files.loadAdobeExpress = function(appName, clientId) {
        var CCEverywhere;
        ((document, url) => {
            const script = document.createElement("script");
            script.src = url;
            script.onload = async () => {
                if (!window.CCEverywhere) {
                    return;
                }

                CCEverywhere  = await window.CCEverywhere.initialize({
                    clientId: clientId,
                    appName: appName,
                });
            };
            document.body.appendChild(script);
        })(document, CC_CDN_URL);

        $(document).on('click', '#adobe-express', function (e) {
            e.preventDefault();

            $(".next-index ").remove();

            const d = new Date();
            let $time = d.getTime();

            let $elementClass = 'next-index next-index_' + $time;
            let $body = $('body');
            $body.append(`<div class='${$elementClass}'><div>`);

            const removeElement = () => {
                $(`.${elementClass}`).remove();
            };

            setTimeout(function () {
                // Initialize the SDK before this 
                CCEverywhere.editor.create();



            }, 1000);
        });
    };
});

Files.init();