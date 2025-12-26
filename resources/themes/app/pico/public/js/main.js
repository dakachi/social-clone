"use strict";

window.ChartInstances = window.ChartInstances || {};
window._codemirror_editors = [];

var Main = new (function () 
{
    var Main = this;
    var ScrollNo = 0;
    var typeText;
    var that_tmp, action_tmp, data_tmp, _function_tmp;
    var typingTimers = new Map();

    Main.init = function( reload ) 
    {
        if(reload || reload == undefined){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': VARIABLES.csrf
                }
            });

            Main.selectAccount();
            Main.Checkbox();

        }

        Main.Layout();
        Main.selectedAccount();
        Main.lazyload();
        Main.addToField();
        Main.Tags();
        Main.Select2();
        Main.Dropdown();
        Main.checkboxProcess();
        Main.Tooltip();
        Main.Popover();
        Main.Search();
        Main.actionChange();
        Main.actionItem();
        Main.actionMultiItem();
        Main.activeItem();
        Main.actionForm();
        Main.imgUploadPreview();
        Main.DateTime();
        Main.dateRange();
        Main.ajaxPages();
        Main.ajaxPageActions();
        Main.ajaxScroll();
        Main.dataScroll();
        Main.ajaxScrollTop();
        Main.dataScrollTop();
        Main.ajaxScrollActions();
        Main.ajaxScrollTopActions();
        Main.Calendar();
        Main.Emoji();
        Main.codeMirror();
        Main.Tinymce();
        Main.Range();
        Main.exportCharts();
        Main.generalAIContent();
        Main.DataTable_Static();

        $(function () {
            $(".selectTimes").each(function () {
                Main.initSpecificTime(this);
            });
        });
    },

    Main.Layout = function(){
        $(document).on("click", ".sidebar-toggle", function(){
            if( $("html").hasClass("sidebar-small") ){
                $("html").removeClass("sidebar-small");
                $("html").addClass("sidebar-open");
                $(this).removeClass("open");
                $.post(VARIABLES.url + "auth/sidebar-state", { sidebar_small: 0 }, function(res){});
            }else{
                $("html").addClass("sidebar-small");
                $("html").removeClass("sidebar-open");
                $(this).addClass("open");
                $.post(VARIABLES.url + "auth/sidebar-state", { sidebar_small: 1 }, function(res){});
            }
        });
    },

    Main.copyToClipboard = function(selector, notifyText = 'Copied!', notifyType = 1) {
        var input = document.querySelector(selector);
        if (!input) return;

        if (navigator.clipboard) {
            navigator.clipboard.writeText(input.value).then(function() {
                Main.showNotify('', notifyText, notifyType);
            }, function() {
                input.select();
                input.setSelectionRange(0, 99999);
                document.execCommand("copy");
                Main.showNotify('', notifyText, notifyType);
            });
        } else {
            input.select();
            input.setSelectionRange(0, 99999);
            document.execCommand("copy");
            Main.showNotify('', notifyText, notifyType);
        }
    },

    Main.escapeHtml = function(str) {
        return str.replace(/[&<>"']/g, function (m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[m];
        });
    },

    Main.nl2br = function(str) {
        return Main.escapeHtml(str).replace(/\n/g, '<br>');
    },

    Main.lazyload = function(){
        $('img').each(function () {
            var $img = $(this);
            var src = $img.attr('src');

            if ($img.hasClass('lazy') && src) {
                $img.attr('data-src', src);
                $img.attr('src', '/images/empty.png');
                $img.addClass('lazyload');
                $img.attr('onerror', "this.src='/images/empty.png'");
            }
        });
    },

    Main.checkboxProcess = function (_el) {
        $("input[type='checkbox'], input[type='radio']").each(function () {
            Main.checkboxActive($(this));
        });

        $(document).on("change", "input[type='checkbox'], input[type='radio']", function () {
            Main.checkboxActive($(this));
        });
    },

    Main.checkboxActive = function (_el) {
        const addClass = _el.data('add-class');
        const removeClass = _el.data('remove-class');
        const $label = $(`label[for='${_el.attr('id')}']`);

        if (!addClass && !removeClass) {
            return;
        }

        if (_el.attr('type') === 'checkbox') {
            if (_el.is(':checked')) {
                $label.addClass(addClass).removeClass(removeClass);
            } else {
                $label.addClass(removeClass).removeClass(addClass);
            }
        } else if (_el.attr('type') === 'radio') {
            const groupName = _el.attr('name');

            $(`input[name='${groupName}']`).each(function () {
                const $radioLabel = $(`label[for='${$(this).attr('id')}']`);
                $radioLabel.removeClass(addClass).removeClass(removeClass);
            });

            if (_el.is(':checked')) {
                $label.addClass(addClass).removeClass(removeClass);
            }
        }
    },

    Main.initSpecificTime = function(containerSelector) {
        var container = $(containerSelector);

        function addSpecificTime() {
            var item = container.find(".tempPostByTimes .input-group");
            var c = item.clone();
            c.find("input").attr("name", "time_posts[]").addClass("onlytime").val("");
            container.find(".listPostByTimes").append(c);
            if (typeof Main !== 'undefined' && typeof Main.DateTime === 'function') {
                Main.DateTime();
            }
            updateRemoveButtonState();
            return false;
        }

        function removeSpecificTime() {
            $(this).closest(".input-group").remove();
            updateRemoveButtonState();
        }

        function updateRemoveButtonState() {
            var removeButtons = container.find(".listPostByTimes .remove");
            if (removeButtons.length < 2) {
                removeButtons.addClass("disabled");
            } else {
                removeButtons.removeClass("disabled");
            }
        }

        container.on("click", ".addSpecificTimes", addSpecificTime);
        container.on("click", ".listPostByTimes .remove:not(.disabled)", removeSpecificTime);

        updateRemoveButtonState();
    },

    Main.selectedAccount = function(el){
        if ($(".am-list-account").length > 0) {
            $(".am-list-account .am-choice-body .am-choice-item").each(function() {
                var item = $(this);
                if(item.find("input").is(':checked')){
                    var selected_item_html = item.find(".am-choice-item-selected").html();
                    $(".am-selected-list").append(selected_item_html);
                    $(".am-selected-empty").hide();

                    $(".option-network").addClass("d-none");
                    $('.am-selected-list .am-selected-item').each(function() {
                        var network = $(this).data("network");
                        if (network) {
                            $(`[data-option-network="${network}"]`).removeClass("d-none");
                        }
                    });
                }
            });
        }
    },

    Main.selectAccount = function(el){
        $(document).off('click').on("click", ".am-open-list-account", function(e){
            e.preventDefault();    
            if($(".am-list-account").hasClass("down")){
                $(".am-list-account").slideUp().removeClass("down");
                $(".am-choice-box").removeClass("active");
            }else{
                $(".am-list-account").slideDown().addClass("down");
                $(".am-choice-box").addClass("active");
            }
        });

        $(document).mouseup(function (e) {
            e.preventDefault();    
            var container = $(".am-choice-box");
            if(!container.is(e.target) && 
            container.has(e.target).length === 0) {
                $(".am-list-account").slideUp().removeClass("down");
                $(".am-choice-box").removeClass("active");
            }
        });

        $(document).on("change", ".am-choice-body .am-choice-item", function(e){
            e.preventDefault();    
            var that = $(this);
            if(!that.find("input").is(":checked")){
                var id = that.find("input").val();
                $(".am-selected-list .am-selected-item[data-id='"+id+"']").remove();

                if( $(".am-selected-list .am-selected-item").length == 0 ){
                    $(".am-selected-empty").show();
                }

                Main.checkListChoiceEmpty();
            }else{
                Main.CheckAndSelect(that);
            }
        });

        $(document).on("click", ".am-selected-list .am-selected-item .remove", function(e){
            e.preventDefault();    
            var that = $(this).parents(".am-selected-item");
            var id = that.attr("data-id");
            that.remove();
            $(".am-choice-body .am-choice-item input[value='"+id+"']").prop('checked',false);
            Main.checkListChoiceEmpty();
        });

        $(document).on("click", ".btnSelectedGroup", function(e){
            e.preventDefault();    
            var accounts = $(this).data("accounts");
            $(".am-selected-list .am-selected-item").remove();
            $(".am-choice-body .am-choice-item").each(function(){
                var pid = $(this).data("pid");
                if( jQuery.inArray( pid.toString(), accounts ) != -1 || jQuery.inArray( parseInt(pid), accounts ) != -1 ){
                    Main.CheckAndSelect($(this));
                }else{
                    $(this).find("input").prop('checked', false);
                    var id = $(this).find("input").val();
                    $(".am-selected-list .am-selected-item[data-id='"+id+"']").remove();
                    if( $(".am-selected-list .am-selected-item").length == 0 ){
                        $(".am-selected-empty").show();
                    }
                    Main.checkListChoiceEmpty();
                }
            });

             return false;
        });

        $(document).on("change", ".am-list-account .checkbox-all", function(e){
            setTimeout(function(){
                Main.updateSelectedList();
            }, 100);
        });

        $(document).on('click', '.select-group', function(e) {
            e.preventDefault();

            var pids = [];
            try {
                pids = JSON.parse($(this).attr('data-accounts') || '[]');
            } catch (e) { pids = []; }

            // Bỏ hết check trước
            $('.am-choice-item .checkbox-item').prop('checked', false);

            // Check theo group
            $('.am-choice-item').each(function() {
                var pid = $(this).attr('data-pid');
                if (pid && pids.includes(pid)) {
                    $(this).find('.checkbox-item').prop('checked', true);
                }
            });

            // Update trạng thái "check all"
            var $body = $('.am-choice-body');
            var all = $body.find('.checkbox-item').length;
            var checked = $body.find('.checkbox-item:checked').length;
            $('.checkbox-all').prop('checked', all === checked);
            Main.updateSelectedList();
        });

    },

    Main.CheckAndSelect = function(item){
        item.find("input").prop("checked", true);
        var selected_item_html = item.find(".am-choice-item-selected").html();
        $(".am-selected-list").append(selected_item_html);
        $(".am-selected-empty").hide();

        $(".option-network").addClass("d-none");
        $('.am-selected-list .am-selected-item').each(function() {
            var network = $(this).data("network");
            if (network) {
                $(`[data-option-network="${network}"]`).removeClass("d-none");
            }
        });
    },

    Main.checkListChoiceEmpty = function(){
        if( $(".am-selected-list .am-selected-item").length == 0 ){
            $(".am-selected-empty").show();
            $(".option-network").addClass("d-none");
        }

        $('.am-selected-list .am-selected-item').each(function() {
            var network = $(this).data("network");
            $(".option-network").addClass("d-none");
            if (network) {
                $(`[data-option-network="${network}"]`).removeClass("d-none");
            }
        });
    },

    Main.updateSelectedList = function() {
        var $selectedList = $(".am-selected-list");
        $selectedList.empty();

        // Find all checked checkbox-item and append their selected html
        var checkedCount = 0;
        $(".am-choice-body .am-choice-item .checkbox-item:checked").each(function() {
            var $item = $(this).closest('.am-choice-item');
            var selectedHtml = $item.find(".am-choice-item-selected").html();
            $selectedList.append(selectedHtml);
            checkedCount++;
        });

        // Show or hide empty message
        if (checkedCount === 0) {
            $(".am-selected-empty").show();
        } else {
            $(".am-selected-empty").hide();
        }
    },

    Main.generalAIContent = function(){
        $(document).on('click','.generalAIContent', function(){
            var that   = $(this);
            var data   = new FormData();
            var action     = that.data("url");
            var content = $(".post-caption")[0].emojioneArea.getText();
            if(content != undefined) data.append("content", content);
            Main.ajaxPost(that, action, data, function(result){
                if (result.status == 1) {
                    Main.typeText(".post-caption", result.data);
                }
            });
            return false;
        });
    },

    Main.Uncheckbox = function(_el)
    {
        $(_el).find('input.checkbox-all').prop('checked',false).removeClass('checked');
    }

    Main.addToField = function(){
        $(document).on("click", ".addToField", function(){
            var field = $(this).data("field");
            var content = $(this).data("content");
            var refresh = $(this).data("refresh");

            if(refresh && refresh != undefined){
                $(field).val("");
            }

            clearTimeout(typeText);
            Main.typeText(field, content);
        });
    },

    Main.typeText = function(el, text, index = 0, clear = true) {
        const $el = $(el);
        const isEmojiInput = $el.data("emojioneArea") !== undefined;
        const emojiArea = isEmojiInput ? $el[0].emojioneArea : null;
        const elementId = $el.attr("id") || $el.data("typing-id") || Date.now();

        $el.data("typing-id", elementId); // assign id if not exists

        text = String(text ?? "");

        // Clear existing timer if running
        if (typingTimers.has(elementId)) {
            clearTimeout(typingTimers.get(elementId));
            typingTimers.delete(elementId);
        }

        if (clear) {
            if (isEmojiInput) {
                emojiArea.setText("");
            } else {
                $el.val("");
            }
        }

        function stepType(i) {
            if (i < text.length) {
                const current = isEmojiInput ? emojiArea.getText() : $el.val();
                const nextChar = text.charAt(i);

                if (isEmojiInput) {
                    emojiArea.setText(current + nextChar);
                } else {
                    $el.val(current + nextChar);
                }

                const timer = setTimeout(() => {
                    stepType(i + 1);
                }, 10);

                typingTimers.set(elementId, timer);
            } else {
                typingTimers.delete(elementId);
            }
        }

        stepType(index);
    },

    Main.Dropdown = function(){
        $('[data-bs-dropdown-close="true"]').on('click', function() {
            $(this).parents(".btn-group").find(".dropdown-toggle").removeClass("show");
            $(this).parents(".btn-group").find(".dropdown-menu").removeClass("show");
        });

        $(".select2-search__field").click(function(e){
            e.stopPropagation();
        });

        if ($('.dropdown-toggle').length > 0) {   
            const dropdowns = document.querySelectorAll('.dropdown-toggle');
            dropdowns.forEach(dropdown => {
                new bootstrap.Dropdown(dropdown);
                dropdown.addEventListener('show.bs.dropdown', function () {
                    $(dropdown).parent().addClass('position-static');
                });
                dropdown.addEventListener('hide.bs.dropdown', function () {
                    $(dropdown).parent().removeClass('position-static');
                });
            });

            // Hover dropdown
            var dropdownTimeout = false;
            $('.dropdown.dropdown-hover').on('mouseenter', function () {
                clearTimeout(dropdownTimeout);
                $(this).addClass('show');
                $(this).find('.dropdown-menu').addClass('show');
            }).on('mouseleave', function () {
                var t = $(this);
                dropdownTimeout = setTimeout(function() {
                    t.removeClass('show');
                    t.find('.dropdown-menu').removeClass('show');
                }, 150);
            });

            // Đóng dropdown bằng nút .dropdown-close
            $(document).on('click', '.dropdown-close', function() {
                $(this).closest('.btn-group, .dropdown').removeClass('show');
                $(this).closest('.dropdown-menu').removeClass('show');
            });
        }

    },

    Main.Tooltip = function(){
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        $(document).on('mouseenter', '[data-bs-toggle="tooltip"]', function () {
            var tooltipInstance = bootstrap.Tooltip.getInstance(this); 
            if (!tooltipInstance) {
                new bootstrap.Tooltip(this).show();
            }
        });

        $(document).on('mouseleave', '[data-bs-toggle="tooltip"]', function () {
            var tooltipInstance = bootstrap.Tooltip.getInstance(this);
            if (tooltipInstance) {
                tooltipInstance.hide();
            }
        });
    },

    Main.Popover = function(){
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
    },

    

    Main.hideTooltips = function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            var tooltipInstance = bootstrap.Tooltip.getInstance(el);
            if (tooltipInstance) {
                tooltipInstance.hide();
                tooltipInstance.dispose();
            }
        });
    };

    Main.Search = function(el){
        if(el == undefined){
            el = 'search-list';
        }

        $(document).on('keyup', '.search-input', function() {
            var search_element = $(this).data("search");
            if( search_element != undefined ){
                el = search_element;
            }else{
                el = 'search-list';
            }

            var value = $(this).val().toLowerCase();
            $( '.' + el ).filter(function() {
              $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    },

    Main.Checkbox = function() {
        // Checkbox all
        $(document).on("change", ".checkbox-all", function(){
            var parentSelector = $(this).data("checkbox-parent");
            var $parent = parentSelector ? $(this).closest(parentSelector) : $(document);

            var isChecked = $(this).prop("checked");

            $parent.find("input.checkbox-item:checkbox").prop('checked', isChecked);
        });

        $(document).on("change", ".checkbox-item", function(){
            var $parent = $(this).closest(".am-list-account");
            var all = $parent.find(".checkbox-item").length;
            var checked = $parent.find(".checkbox-item:checked").length;
            $parent.find(".checkbox-all").prop("checked", all === checked);
        });
    },

    Main.activeItem = function(){
        $(".activeItem").each(function(){
            var find = $(this).parents(".form-check").find("input:checked");
            if(find.length > 0){
                var add_class = $(this).data("add");
                var remove_class = $(this).data("remove");
                var parent = $(this).data("parent");
                $(parent).find(".activeItem").removeClass(add_class);
                $(parent).find(".activeItem").addClass(remove_class);
                $(this).addClass(add_class).removeClass(remove_class);
            }
        });

        $(document).on("click", ".activeItem", function(){
            var that = $(this);
            var add_class = $(this).data("add");
            var remove_class = $(this).data("remove");
            var parent = $(this).data("parent");

            $(parent).find(".activeItem").removeClass(add_class);
            $(parent).find(".activeItem").addClass(remove_class);
            that.addClass(add_class).removeClass(remove_class);
        });
    },

    Main.Range = function(){
        $('input[type="range"]').ionRangeSlider({
            min: 0,
            max: 100
        });
    };

    Main.Tags = function(){
        if( $('.input-tags').length > 0 ){
            $('.input-tags').inputTags({
              
            }); 

            $(".inputTags-field").on('keydown', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    return false;

                    alert(11);
                }
            });
        }
    },

    Main.Select2 = function(){
        if ($('[data-control="select2"]').length > 0) {
            $('[data-control="select2"]').each(function(){
                var $this = $(this);
                var width = $this.data("width");
                var hide_search = $this.data("hide-search");
                var select2_item = $this.width();
                var ajaxUrl = $this.data('ajax-url');

                // Build Select2 options
                var select2Options = {
                    theme: 'bootstrap-5',
                    selectionCssClass: ":all:",
                    dropdownAutoWidth: true,
                    minimumResultsForSearch: hide_search > 0 ? -1 : 0,
                    allowHtml: true,
                    width: 'resolve',
                    tokenizer: function(m) { 
                        var dropdownClass = $this.data("select2-dropdown-class");
                        console.log($this);
                        setTimeout(function(){
                            $(".select2-dropdown-class").width($this.width()).show();
                            $(".select2-dropdown").addClass(dropdownClass);
                            //$(".select2-dropdown").parnets().width($this.width());
                            $(".select2-container").width(width);
                        }, 0);
                        return m; 
                    },
                    dropdownParent: $this.parents("form").attr("id") !== undefined ? "#" + $this.parents("form").attr("id") : "",
                    templateSelection: function(icon) {
                        var style = "";
                        if ($(icon.element).data('icon') !== undefined) {
                            style = $(icon.element).data('icon-color') !== undefined ? ' style="color: ' + $(icon.element).data('icon-color') + '"' : "";
                            return $('<span><i class="' + $(icon.element).data('icon') + ' select-option-icon"' + style + '></i> <span class="select-option-text fs-13">' + icon.text + '</span></span>');
                        }
                        if ($(icon.element).data('img') !== undefined) {
                            return $('<span class="d-block m-t--4 mt--4"><img src="' + $(icon.element).data('img') + '" class="w-17 select-option-img"> <span class="select-option-text fs-13">' + icon.text + '</span></span>');
                        }
                        return $('<span class="d-block m-t--1 mt--1"><span class="select-option-text fs-13">' + icon.text + '</span></span>');
                    },
                    templateResult: function(icon) {
                        var style = "";
                        if ($(icon.element).data('icon') !== undefined) {
                            style = $(icon.element).data('icon-color') !== undefined ? ' style="color: ' + $(icon.element).data('icon-color') + '"' : "";
                            return $('<span><i class="' + $(icon.element).data('icon') + ' select-option-icon"' + style + '></i> <span class="select-option-text fs-13">' + icon.text + '</span></span>');
                        }
                        if ($(icon.element).data('img') !== undefined) {
                            return $('<span class="d-block text-truncate" style="max-width: ' + (select2_item - 50) + 'px; min-width: ' + (select2_item - 50) + 'px"><img src="' + $(icon.element).data('img') + '" class="w-17 select-option-img"> <span class="select-option-text fs-13">' + icon.text + '</span></span>');
                        }
                        return $('<span><span class="select-option-text fs-13">' + icon.text + '</span></span>');
                    }
                };



                // If AJAX URL provided
                if (ajaxUrl) {
                    select2Options.ajax = {
                        url: ajaxUrl,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        },
                        cache: true
                    };
                }
                
                $this.select2(select2Options);

                var selectId = $this.data("selected-id");
                var selectName = $this.data("selected-text");
                if(selectId != undefined && selectName != undefined){
                    if ($this.find("option[value='" + selectId + "']").length === 0) {
                        var newOption = new Option(selectName, selectId, true, true);
                        $this.append(newOption).trigger('change');
                    } else {
                        $this.val(selectId).trigger('change');
                    }
                }

                // Responsive resize support
                function resizeSelect2() {
                    var w = $this.parent().width() || $this.width();
                    // Update select2 and dropdown width
                    $this.next('.select2-container').css('width', w + 'px');
                    $this.select2('close'); // force dropdown to refresh on next open
                }

                // Debounce for resize event
                var debounceTimer;
                $(window).on('resize', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function(){
                        resizeSelect2();
                    }, 100);
                });

                // Initial call
                setTimeout(function(){
                    resizeSelect2();
                }, 1000);

                // If select2 inside tab, resize on tab show
                $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
                    setTimeout(resizeSelect2, 50);
                });
            });

            // Prevent dropdown from closing when clicking inside it
            $(document).on('select2:open', function() {
                var select2Dropdown = $('.select2-container--open .select2-dropdown');
                select2Dropdown.on('click', function(e) {
                    e.stopPropagation();
                });
            });

            $(document).on('mousedown', '.select2-search__field', function (e) {
                var $container = $(this).closest('.select2-container');
                var $select = $container.prev('select');
                if (!$container.hasClass('select2-container--open')) {
                    setTimeout(function(){
                        $select.select2('open');
                    }, 1);
                }
            });
        }
    },

    Main.closeModal = function(id){
        $('#'+id).modal('hide');
    },

    Main.closeOffCanvas = function(id){
        var offcanvasElement = document.getElementById(id);
        var bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);
        bsOffcanvas.hide();
    },

    Main.imgUploadPreview = function(){
        $(".form-file").each(function( index, value ){
            var imgURL = $(this).find(".form-file-input-edit").val();
            $(this).find(".form-img div").css('background-image', 'url(' + imgURL + ')');
        });
        
        $(document).on("change", ".form-file-input", function(e){
            var file = this.files[0];
            if (file) {
                var imgURL = URL.createObjectURL(e.target.files[0]);
                $(this).parents(".form-file").find(".form-img div").css('background-image', 'url(' + imgURL + ')');
            }
        });
    },
    
    /*
    * DateTime
    */
    Main.DateTime = function(){

        if( $('.date').length > 0 || $('.datetime').length > 0 || $('.onlytime').length > 0 ){
            $('.date').datepicker({
                dateFormat: VARIABLES.format_date,
                beforeShow: function(s, a){
                    $('.ui-datepicker-wrap').addClass('active');
                },
                onClose: function(){
                    $('.ui-datepicker-wrap').removeClass('active');
                }
            });

            $.datepicker.regional["en"] =
            {
                closeText: "Done",
                prevText: "Prev",
                nextText: "Next",
                currentText: "Today",
                monthNames: [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ],
                monthNamesShort: [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ],
                dayNames: [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ],
                dayNamesShort: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ],
                dayNamesMin: [ "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa" ],
                weekHeader: "Wk",
                dateFormat: "dd/mm/yy",
                firstDay: 7,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: ""
            };

            $.datepicker.setDefaults($.datepicker.regional["en"]);

            $.timepicker.regional['en'] = {
                currentText: "Now",
                closeText: "Done",
                amNames: ['AM', 'A'],
                pmNames: ['PM', 'P'],
                timeFormat: 'HH:mm',
                timeSuffix: '',
                timeOnlyTitle: "Choose Time",
                timeText: "Time",
                hourText: "Hour",
                minuteText: "Minute",
                secondText: "Second",
                millisecText: "Millisecond",
                microsecText: "Microsecond",
                timezoneText: "Time Zone"
            };
            $.timepicker.setDefaults($.timepicker.regional['en']);

            if( $('.date').val() == "" ){
                $('.date').datepicker('setDate', 'today');
            }

            $('.datetime').datetimepicker({
                controlType: 'select',
                oneLine: true,
                dateFormat: VARIABLES.format_datetime[0],
                timeFormat: VARIABLES.format_datetime[1],
                beforeShow: function(s, a){
                    $('.ui-datepicker-wrap').addClass('active');
                },
                onClose: function(){
                    $('.ui-datepicker-wrap').removeClass('active');
                }
            });

            $(".datetime").each(function(){
                var that = $(this);
                if( that.val() == "" ){
                    that.datetimepicker( 'setDate', new Date() );
                }
            });

            $('.onlytime').timepicker({
                controlType: 'select',
                oneLine: true,
                dateFormat: '',
                timeFormat: VARIABLES.format_datetime[1],
                beforeShow: function(s, a){
                    $('.ui-datepicker-wrap').addClass('active');
                },
                onClose: function(){
                    $('.ui-datepicker-wrap').removeClass('active');
                }
            });

            $(".onlytime").each(function(){
                var that = $(this);
                if( that.val() == "" ){
                    that.datetimepicker( 'setDate', new Date() );
                }
            });

            $('[id^="ui-datepicker-div"]').wrapAll('<div class="ui-datepicker-wrap"></div>'); 
        }

        if( $(".dateBtn").length > 0 ){
           $('.dateBtn').datepicker({
                dateFormat: VARIABLES.format_date,
                showOn: 'button',
                constrainInput: false,
                beforeShow: function(input, inst) {
                    $('.ui-datepicker-wrap').addClass('active');
                },
                onClose: function() {
                    $('.ui-datepicker-wrap').removeClass('active');
                }
            });

            $('.input-group').each(function() {
                var $group = $(this);
                var $input = $group.find('.dateBtn');
                var $btn = $group.find('.selectDate');

                if ($input.length && $btn.length) {
                    $btn.on('click', function(e) {
                        e.preventDefault();
                        $input.datepicker('show');
                    });
                }
            });

            if ($('.ui-datepicker-wrap').length == 0 && $('[id^="ui-datepicker-div"]').length) {
                $('[id^="ui-datepicker-div"]').wrapAll('<div class="ui-datepicker-wrap"></div>');
            }
        }
    },

    Main.formatDateTime = function(date){
        if (!date || !(date instanceof Date)) return '';

        let [dateFormat, timeFormat] = VARIABLES.format_datetime;

        const pad = (n) => (n < 10 ? '0' + n : n);

        // Date parts
        const dd = pad(date.getDate());
        const mm = pad(date.getMonth() + 1);
        const yy = date.getFullYear().toString().slice(-2);
        const yyyy = date.getFullYear();

        // Time parts
        let hh = date.getHours();
        const min = pad(date.getMinutes());
        const TT = hh >= 12 ? 'PM' : 'AM';
        const h12 = pad(hh % 12 || 12);
        const h24 = pad(hh);

        // Replace in format
        const formattedDate = dateFormat
            .replace(/dd/, dd)
            .replace(/mm/, mm)
            .replace(/yyyy/, yyyy)
            .replace(/yy/, yy);

        const formattedTime = timeFormat
            .replace(/hh/, timeFormat.includes("TT") ? h12 : h24)
            .replace(/mm/, min)
            .replace(/TT/, TT);

        return `${formattedDate} ${formattedTime}`;
    },

    Main.dateRange = function() {
        if ($(".daterange").length > 0 && $("[name='daterange']").length === 0) {
            $(".daterange").removeClass("d-none");
            var $class = $(".daterange").attr("class");
            var opens = $(".daterange").attr("data-open");
            $(".daterange").attr("class", "daterange");
            $(".daterange").html(`
                <button type="button" id="daterange" class="${$class} d-flex align-items-center justify-content-between gap-8 px-2 py-2">
                    <i class="fa-light fa-calendar-days text-success fs-16"></i>
                    <span></span> <i class="fa fa-caret-down"></i>
                    <input type="hidden" class="ajax-pages-filter" name="daterange" value="">
                </button>
                <button type="submit" id="btn_daterange" class="d-none"></button>
            `);

            var start = moment().subtract(27, 'days');
            var end = moment();

            const urlParams = new URLSearchParams(window.location.search);
            const dateRange = urlParams.get("daterange");

            if (dateRange && dateRange.includes(",")) {
                const parts = dateRange.split(",");
                const parsedStart = moment(parts[0], "YYYY-MM-DD");
                const parsedEnd = moment(parts[1], "YYYY-MM-DD");

                if (parsedStart.isValid() && parsedEnd.isValid()) {
                    start = parsedStart;
                    end = parsedEnd;
                }
            }

            // Helper function to translate month names
            function translateMonths(dateString) {
                const months = [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ];
                months.forEach(function(month) {
                    dateString = dateString.replace(new RegExp(month, 'g'), month);
                });
                return dateString;
            }

            function cb(start, end) {
                var startHtml = translateMonths(start.format('MMMM D, YYYY'));
                var endHtml = translateMonths(end.format('MMMM D, YYYY'));

                $('#daterange span').html(startHtml + ' - ' + endHtml);
                $("[name='daterange']").val(start.format('YYYY-MM-DD') + "," + end.format('YYYY-MM-DD'));
            }

            $('#daterange').daterangepicker({
                opens: opens,
                startDate: start,
                endDate: end,
                ranges: {
                    'Last 7 days': [moment().subtract(6, 'days'), moment()],
                    'Last 28 days': [moment().subtract(27, 'days'), moment()],
                    'This month': [moment().startOf('month'), moment().endOf('month')],
                    'Last month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);

            cb(start, end);

            $('#daterange').on('apply.daterangepicker', function(e, picker) {
                e.preventDefault();
                $("[name='daterange']").val(picker.startDate.format('YYYY-MM-DD') + "," + picker.endDate.format('YYYY-MM-DD')).trigger('change');

                setTimeout(function() {
                    if (!$(".daterange").hasClass("no-submit")) {
                        $("#btn_daterange").trigger("click");
                    }
                }, 200);
            });
        }
    },


    Main.mediaURL = function( base_path, path) {
        if (/^(https?:)?\/\//.test(path)) {
            return path;
        }

        const base = base_path.replace(/\/$/, '');
        return `${base}/${path}`;
    },

    /*
    * DataTables
    */
    Main.DataTable_Static = function (_el = undefined, _option = {}) {
        if (typeof _el === 'undefined') {
            $('.table#DataTable_Static').each(function () {
                Main.DataTable_Static($(this), _option);
            });
            return;
        }

        if (!_option.columns) {
            const columnCount = $(_el).find('thead th').length;
            _option.columns = Array.from({ length: columnCount }, () => null);
        }

        const DataTable = $(_el).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: false,
            stateSave: false,
            destroy: true,
            order: _option.order ?? [],
            columns: _option.columns,
            columnDefs: _option.columnDefs ?? [],
            lengthMenu: _option.lengthMenu ?? [10, 25, 50, 100],
            layout: {
                bottomEnd: "paging"
            },
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            language: {
                paginate: { first: '', last: '', previous: '', next: '' },
                lengthMenu: "Show _MENU_ per page",
                zeroRecords: "No matching records found.",
                infoEmpty: "No records available"
            },
            initComplete: () => {
                setTimeout(() => {
                    $('.dt-paging').appendTo('.table-pagination');
                    $('.dt-info').appendTo('.table-info');
                    $('.dt-length label').appendTo('.table-size');
                }, 100);
                Main.Tooltip();
            }
        });

        $(_el).parents(".card").find("[name='search']").on('keyup', function () {
            DataTable.search(this.value).draw();
        });

        return DataTable;
    },

    Main.DataTable = function (_el, _option) 
    {
        $.fn.dataTable.ext.errMode = 'none';
        var DataTable = $(_el).DataTable({
            ajax: {
                "url": $(_el).data("url"),
                "type": "POST",
                "headers": {
                    'X-CSRF-TOKEN': VARIABLES.csrf
                },
                "data": function (d) {
                    return $.extend({}, d, Main.DataTable_Filter());
                }
            },
            language: Main.DataTable_Language(),
            searchDelay: 500,
            processing: true,
            serverSide: true,
            stateSave: true,
            destroy: true,
            order: _option.order!=undefined?_option.order:false,
            columns: _option.columns!=undefined?_option.columns:false,
            columnDefs: _option.columnDefs!=undefined?_option.columnDefs:false,
            lengthMenu: _option.lengthMenu!=undefined?_option.lengthMenu:false,
            layout: {
                bottomEnd: "paging"
            },
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            language: {
                paginate: { first: '', last: '', previous: '', next: '' },
                lengthMenu: "Show _MENU_ per page",
                zeroRecords: "No matching records found.",
                infoEmpty: "No records available"
            },
            initComplete: (settings, json) => {
                setTimeout(function(){
                    $('.dt-paging').appendTo('.table-pagination');
                    $('.dt-info').appendTo('.table-info');
                    $('.dt-length label').appendTo('.table-size');
                }, 100);
                Main.Tooltip();
            }
        });

        $(document).on("change", "[name*='datatable_filter']", function(){
            DataTable.ajax.reload();
        });

        $(document).on("keyup", "[name*='datatable_filter']", function(){
            DataTable.ajax.reload();
        });

        return DataTable;
    },

    Main.DataTable_Filter = function()
    {
        var Filters = {};
        $("[name*='datatable_filter']").map(function(){
            var filter_name = $(this).attr("name");
            var matches = filter_name.match(/\[(.*?)\]/);
            if (matches) {
                var filter_name = matches[1];
                Filters[filter_name] = $(this).val();
            }
        }).get();

        return Filters;
    },

    Main.DataTable_Reload = function (_el) 
    {
        $(_el).DataTable().ajax.reload();
        Main.Uncheckbox(_el);
    },

    Main.DataTable_Language = function() {
        var langMap = {
            'af': 'Afrikaans',
            'sq': 'Albanian',
            'am': 'Amharic',
            'ar': 'Arabic',
            'hy': 'Armenian',
            'az': 'Azerbaijan',
            'eu': 'Basque',
            'be': 'Belarusian',
            'bn': 'Bangla',
            'bs': 'Bosnian',
            'bg': 'Bulgarian',
            'ca': 'Catalan',
            'zh': 'Chinese',
            'zh-cn': 'Chinese',
            'zh-tw': 'Chinese-traditional',
            'hr': 'Croatian',
            'cs': 'Czech',
            'da': 'Danish',
            'nl': 'Dutch',
            'en': 'English',
            'et': 'Estonian',
            'fa': 'Farsi',
            'fi': 'Finnish',
            'fr': 'French',
            'gl': 'Galician',
            'ka': 'Georgian',
            'de': 'German',
            'el': 'Greek',
            'gu': 'Gujarati',
            'he': 'Hebrew',
            'hi': 'Hindi',
            'hu': 'Hungarian',
            'is': 'Icelandic',
            'id': 'Indonesian',
            'id-alt': 'Indonesian-Alternative',
            'ga': 'Irish',
            'it': 'Italian',
            'ja': 'Japanese',
            'kk': 'Kazakh',
            'km': 'Khmer',
            'ko': 'Korean',
            'ky': 'Kyrgyz',
            'lv': 'Latvian',
            'lt': 'Lithuanian',
            'mk': 'Macedonian',
            'ms': 'Malay',
            'ml': 'Malayalam',
            'mn': 'Mongolian',
            'no': 'Norwegian',
            'nb': 'Norwegian-Bokmal',
            'nn': 'Norwegian-Nynorsk',
            'pl': 'Polish',
            'pt': 'Portuguese',
            'pt-br': 'Portuguese-Brasil',
            'ro': 'Romanian',
            'ru': 'Russian',
            'sr': 'Serbian',
            'si': 'Sinhala',
            'sk': 'Slovak',
            'sl': 'Slovenian',
            'es': 'Spanish',
            'sw': 'Swahili',
            'sv': 'Swedish',
            'ta': 'Tamil',
            'te': 'Telugu',
            'th': 'Thai',
            'tr': 'Turkish',
            'uk': 'Ukrainian',
            'ur': 'Urdu',
            'uz': 'Uzbek',
            'vi': 'Vietnamese',
            'cy': 'Welsh'
        };
        var lang = (VARIABLES.lang || 'en').toLowerCase().replace('_', '-');
        var file = langMap[lang] || langMap[lang.split('-')[0]] || 'English';
        return {
            url: VARIABLES.theme_asset + "/plugins/datatables/lang/" + file + '.json'
        };
    },

    Main.Tinymce = function(_el){
        if( $(".textarea_editor").length > 0 || $(_el).length > 0 ){

            if (_el == undefined) { _el = '.textarea_editor'; }

            tinymce.init({
                selector: _el,
                plugins: 'advlist autolink lists link image charmap preview anchor pagebreak',
                toolbar_mode: 'floating',
                menubar: false,
                statusbar: false,
                setup: (editor) => {
                    editor.on('init', (e) => {
                        $(_el).each(function(){
                            var $class = $(this).attr("class");
                            $(this).next().addClass($class);
                        });
                    });
                }
            });
        }
    },

    Main.codeMirror = function(_el) {
        var selector = _el ? _el : '.input-code';
        if (selector instanceof jQuery) selector = selector.get(0);
        var $targets = (typeof selector === 'string') ? $(selector) : $(selector);
        $targets.each(function() {
            if ($(this).data('codemirror')) return;
            var editor = CodeMirror.fromTextArea(this, {
                mode: 'htmlmixed',
                lineNumbers: true,
                theme: 'dracula',
                lineWrapping: true,
            });
            $(this).closest('form').on('submit', function() {
                editor.save();
            });
            $(this).data('codemirror', editor);
            window._codemirror_editors.push(editor);
        });
    },

    /*
    * Emoji
     */
    Main.Emoji = function(element){
        //Emoji texterea
        if(element == undefined){
            element = "input-emoji";
        }

        if($('.' + element).length > 0){
            $('.' + element).emojioneArea({
                hideSource: true,
                useSprite: false,
                pickerPosition    : "bottom",
                filtersPosition   : "top"
            });

            
        }
    },

    Main.ConfirmDialog = function(message, callback) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-sm btn-success btn-outline",
                cancelButton: "btn btn-sm btn-danger btn-outline"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            //title: "Are you sure?",
            text: message,
            icon: false,
            showCancelButton: true,
            //confirmButtonText: "Yes, delete it!",
            //cancelButtonText: "No, cancel!",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                callback(true);
            }else{
                callback(false);
            }
        });
    },

    /*
    * Form Action
    */
    Main.clearForm = function($this){
        $this.find("input").val('');
        $this.find("textarea").val('');
        tinymce.get('textarea_editor').setContent('');
    }

    Main.actionItem = function()
    {
        $(document).on('click', ".actionItem", function(event) {
            event.preventDefault();    
            var that   = $(this);
            var action = that.attr("href");
            var id     = that.data("id");
            var type   = that.data("type");
            var filter = that.data("filter");
            var data   = new FormData();
            if(id != undefined) data.append("id", id);
            if(type != undefined) data.append("type", type);
            if(filter != undefined) data.append("filter", filter);
            Main.ajaxPost(that, action, data, null);
            return false;
        });
    },

    Main.actionChange = function()
    {
        $(document).on('change', ".actionChange", function(event) {
            event.preventDefault();    
            var that   = $(this);
            var action = that.data("url");
            var id     = that.data("id");
            var type   = that.data("type");
            var value  = that.val();
            var data   = new FormData();
            if(id != undefined) data.append("id", id);
            if(type != undefined) data.append("type", type);
            if(value != undefined) data.append("value", value);
            Main.ajaxPost(that, action, data, null);
            return false;
        });
    },

    Main.actionMultiItem = function()
    {
        $(document).on('click', ".actionMultiItem", function(event) {
            event.preventDefault();    
            var that     = $(this);
            var form = that.data("form");
            if(form == undefined){
                form     = that.closest("form");
            }else{
                form = $(form);
            }
            var action   = that.attr("href");
            var params   = that.data("params");
            var data   = new FormData(form[0]);

            if(form.length > 0){
                var formData = that.serializeArray();
                if(formData.length > 0){
                    for (var i = 0; i < formData.length; i++) {
                        data.append(formData[i].name, formData[i].value);
                    }
                }
            }

            Main.ajaxPost(that, action, data, null);
            return false;
        });
    },

    Main.actionForm = function()
    {
        $(document).on('submit', ".actionForm", function(event) {
            event.preventDefault();    
            var that   = $(this);
            var action = that.attr("action");
            var data   = new FormData(that[0]);
            Main.ajaxPost(that, action, data, null);
        });
    },

    Main.ajaxPost = function(that, action, data, _function, pass_confirm)
    {
        that_tmp = that;
        action_tmp = action;
        data_tmp = data;
        _function_tmp = _function;

        var popup          = that.data("popup");
        var offcanvas      = that.data("offcanvas");
        var confirm        = that.data("confirm");
        var transfer       = that.data("transfer");
        var type_message   = that.data("type-message");
        var rediect        = that.data("redirect");
        var content        = that.data("content");
        var append_content = that.data("append-content");
        var callback       = that.data("callback");
        var history_url    = that.data("history");
        var loading        = that.data("loading");
        var call_after     = that.data("call-after");
        var call_before    = that.data("call-before");
        var call_success   = that.data("call-success");
        var remove         = that.data("remove");
        var activeClass    = that.data("active");

        if(confirm != "" && confirm != undefined && pass_confirm == undefined){
            Main.ConfirmDialog(confirm, function(s){
                if(s){
                    Main.ajaxPost(that_tmp, action_tmp, data_tmp, _function_tmp, true);
                }
            });

            return false;
        }

        if(history_url != undefined){
            history.pushState(null, '', history_url);
        }

        if(!that.hasClass("disabled")){
            if(loading == undefined || loading == 1){
                Main.overplay();
            }

            //Call Before
            if(call_before != undefined){
                eval(call_before);
            }

            that.addClass("disabled");

            $.ajax({
                url: action,
                type: 'post',
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                headers: {
                    'X-CSRF-TOKEN': VARIABLES.csrf
                },
                //dataType: 'json',
                success: function (result) {
                    Main.Tooltip();

                    //Parse Json
                    if(!Main.isObject(result)){
                        try {
                            result = JSON.parse(result);
                        } catch (e) {}
                    }

                    //Run function
                    if(_function != null){
                        _function.apply(this, [result]);
                    }

                    //Callback function
                    if(result.callback != undefined){
                        $("body").append(result.callback);
                    }

                    //Callback
                    if(callback != undefined){
                        var fn = window[callback];
                        if (typeof fn === "function") fn(result);
                    }

                    //Using for update
                    if(transfer != undefined){
                        that.removeClass("tag-success tag-danger").addClass(result.tag).text(result.text);
                    }

                    //Add content
                    if(content != undefined || append_content != undefined ||  result.data != undefined){
                        if(append_content != undefined){
                            $("."+append_content).hide();
                            $("."+append_content).append(result.data);
                            setTimeout(function(){
                                $("."+append_content).show(100);
                            }, 50);
                        }else{
                            $("."+content).html(result.data);
                        }
                    }

                    //Call After
                    if(call_after != undefined){
                        eval(call_after);
                    }

                    //Call Success
                    if(call_success != undefined && result.status == 1){
                        eval(call_success);
                    }

                    //Remove Element
                    if(remove != undefined && result.status == 1){
                        that.parents('.'+remove).remove();
                    }

                    //Call Success
                    if(result.redirect != undefined){
                        Main.redirect(result.redirect, true);
                    }

                    if(activeClass != undefined){
                        $(that).siblings().removeClass(activeClass);
                        $(that).addClass(activeClass);
                    }

                    //Hide Loading
                    Main.overplay(true);
                    that.removeClass("disabled");

                    //Redirect
                    Main.redirect(rediect, result.status);

                    //Message
                    if(result.status != undefined){

                        if(result.error_type == 2){
                            Main.showMsgs(result.errors);
                        }else if(result.error_type == 3){
                            Main.showAlertMsgs(that, result.errors, result.class);
                        }else{
                            switch(type_message){
                                case "text":
                                    if (result.errors && typeof result.errors === "object") {
                                        Object.values(result.errors).forEach(function(errArr) {
                                            errArr.forEach(function(errorMsg) {
                                                Main.showNotify(result.title || '', errorMsg, 'error'); // hoặc result.status nếu status là 'error'
                                            });
                                        });
                                    } else {
                                        Main.showNotify(result.title, result.message, result.status);
                                    }
                                    break;

                                default:
                                    if (result.errors && typeof result.errors === "object") {
                                        Object.values(result.errors).forEach(function(errArr) {
                                            errArr.forEach(function(errorMsg) {
                                                Main.showNotify(result.title || '', errorMsg, 'error'); // hoặc result.status nếu status là 'error'
                                            });
                                        });
                                    } else {
                                        Main.showNotify(result.title, result.message, result.status);
                                    }
                                    break;
                            }
                        }
                    }


                    if(popup != undefined){
                        $("body").append(result.data);
                        $('#'+popup).modal('show').on('hidden.bs.modal', function (e) {
                            $(this).remove();
                        });
                    }

                    if(offcanvas != undefined){
                        $("#"+offcanvas).remove();
                        $("body").append(result.data);
                        var offcanvasElement = document.getElementById(offcanvas);
                        var bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);
                        bsOffcanvas.show();

                        $("#"+offcanvas).on('hide.bs.offcanvas', function (e) {
                            $("#"+offcanvas).remove();
                        });
                    }
                },
                error: function (xhr, textStatus, errorThrown) {
                        Main.overplay(true);
                        that.removeClass("disabled");
                        if (xhr.status === 422) {
                        // Parse JSON response
                        let response = xhr.responseJSON;
                        if (response.errors && typeof response.errors === "object") {
                            for (let key in response.errors) {
                                let msgs = response.errors[key];
                                if (Array.isArray(msgs)) {
                                    msgs.forEach(msg => {
                                        Main.showNotify('', msg, 'error');
                                    });
                                } else if (typeof msgs === 'string') {
                                    Main.showNotify('', msgs, 'error');
                                }
                            }
                        } else if (response.message) {
                            Main.showNotify('', response.message, 'error');
                        }
                    } else {
                        let response = xhr.responseJSON;
                        if (response.message) {
                            Main.showNotify('', response.message, 'error');
                        }else{
                            Main.showNotify("", xhr.status + " " + errorThrown, 0);
                        }
                    }
                }
            });
        }

        return false;
    },

    Main.ajaxPages = function(){
        if( $(".ajax-pages").length > 0 ){
            var that = $(".ajax-pages");
            var url = that.attr('data-url');
            var filter = $(".ajax-pages-filter");
            var loading = that.attr('data-loading');
            var class_result = that.attr('data-resp');
            var call_before = that.attr("data-call-before");
            var call_after = that.attr("data-call-after");
            var call_success = that.attr("data-call-success");
            var per_page = that.attr("data-per-page");
            var current_page = that.attr("data-current-page");
            var total_items = that.attr("data-total-items");

            if(current_page == undefined || Number.isNaN(current_page)){
                current_page = 1;
                loading = 0;
                that.attr('data-page', 0);
                that.attr('data-loading', 0);
            }

            var data = { 
                current_page: current_page, 
                per_page: per_page, 
                total_items: total_items 
            };

            if( filter.length > 0 ){
                filter.each( function( index, value ) {
                    var name = $(this).attr("name");
                    var value = $(this).val();
                    data[name] = value;
                } );
            }

            if(loading == undefined || loading == 1){
                Main.overplay();
            }

            $.ajax({
                url: url,
                type: 'POST',
                cache: false,
                data: data,
                headers: {
                    'X-CSRF-TOKEN': VARIABLES.csrf
                },
                dataType: 'JSON',
                error: function (response, textStatus, errorThrown) {
                    Main.overplay(true);

                    response = response.responseJSON;

                    if (response.errors && typeof response.errors === "object") {
                        for (let key in response.errors) {
                            let msgs = response.errors[key];
                            if (Array.isArray(msgs)) {
                                msgs.forEach(msg => {
                                    Main.showNotify('', msg, 'error');
                                });
                            } else if (typeof msgs === 'string') {
                                Main.showNotify('', msgs, 'error');
                            }
                        }
                    } else if (response.message) {
                        Main.showNotify('', response.message, 'error');
                    }
                },
                beforeSend: function() {
                    //Call Before
                    if(call_before != undefined){
                        eval(call_before);
                    }
                }
            }).done(function(result) {
               $(class_result).html( result.data );

                //Call After
                if(call_after != undefined){
                    eval(call_after);
                }

                //Call Success
                if(call_success != undefined && result.status == 1){
                    eval(call_success);
                }

                if( $(".paginationjs").length == 0 || total_items != result.total_items ){
                    that.attr("data-total-items", result.total_items );
                    total_items = result.total_items;
                }

                Main.overplay(true);
            });
        }
    },

    Main.ajaxPageActions = function(){
        var timeout;
        $(document).on("change", ".ajax-pages-filter", function(){
            clearTimeout(timeout);
            timeout = setTimeout(function(){
                Main.ajaxPages();
            }, 500);
        });

        $(document).on("keyup", ".ajax-pages-filter", function(){
            clearTimeout(timeout);
            timeout = setTimeout(function(){
                Main.ajaxPages();
            }, 500);
        });

        $(".ajax-pages-search").keyup(function(e) {
            clearTimeout(timeout);
            timeout = setTimeout(function(){
                e.preventDefault();
                Main.ajaxPages();
            }, 500);
            return false;   
        });

        $(".ajax-pages-search").keydown(function(e) {
            if(e.which == 13) {
                return false;
            }   
        });
    },

    Main.ajaxScrollActions = function(scroll_no){
        var runAjaxScroll;

        if (scroll_no == undefined){
            scroll_no = ''
        }else{
            scroll_no = '-' + scroll_no;
        }

        clearTimeout( runAjaxScroll );
        $(document).on("change", ".ajax-scroll-filter" + scroll_no, function(){
            runAjaxScroll = setTimeout(function(){
                Main.ajaxScroll(true, ScrollNo);
            }, 500);
        });

        $(document).on("keyup", ".ajax-scroll-filter" + scroll_no, function(){
            clearTimeout( runAjaxScroll );
            runAjaxScroll = setTimeout(function(){
                Main.ajaxScroll(true, ScrollNo);
            }, 1500);
        });
    },

    Main.ajaxScrollTopActions = function(scroll_no){
        var runAjaxScroll;

        if (scroll_no == undefined){
            scroll_no = ''
        }else{
            scroll_no = '-' + scroll_no;
        }

        clearTimeout( runAjaxScroll );
        $(document).on("change", ".ajax-scroll-top-filter" + scroll_no, function(){
            runAjaxScroll = setTimeout(function(){
                Main.ajaxScrollTop(true, ScrollNo);
            }, 500);
        });

        $(document).on("keyup", ".ajax-scroll-top-filter" + scroll_no, function(){
            clearTimeout( runAjaxScroll );
            runAjaxScroll = setTimeout(function(){
                Main.ajaxScrollTop(true, ScrollNo);
            }, 1500);
        });
    },

    /**
     * dataScroll
     *
     * Binds a scroll event to an element (or the document) that triggers an AJAX call
     * when the user scrolls near the bottom. The "scroll_no" parameter is used for
     * cases where there are multiple scrollable elements identified by a suffix.
     *
     * @param {Number} scroll_no Optional identifier for multiple scroll containers.
     */
    Main.dataScroll = function(scroll_no)
    {
        // Save the scroll container identifier
        ScrollNo = scroll_no;
        
        // Determine the index suffix (if scroll_no is undefined or 0, use empty string)
        var index = (scroll_no == undefined || scroll_no == 0) ? "" : "-" + scroll_no;
        
        // Get the element that should handle the scroll event
        var that = $('.ajax-scroll' + index);
        
        // Get the scroll container reference (e.g., document, or a specific div)
        var scrollDiv = that.attr('data-scroll');
        
        if (that.length > 0) {
            // Assign the proper scrollBar based on whether data-scroll is set to 'document'
            var scrollBar = (scrollDiv == 'document') ? $(document) : $(scrollDiv);
            
            // Bind the scroll event needed to detect when the user reaches the bottom
            scrollBar.bind('scroll', function() {
                // If scrolling the document
                if (scrollDiv == 'document') {
                    // Trigger resize event (may be for re-calculating dimensions)
                    $(window).trigger('resize');
                    // Check if the window scrolled near bottom (80px from bottom)
                    if ($(window).scrollTop() + $(window).height() + 80 >= $(document).height()) {
                        // Load more content via AJAX call (scroll down behavior)
                        Main.ajaxScroll(false, scroll_no);
                    }
                }
                else {
                    // For scrollable container other than the document
                    var _scrollPadding = 80; // The padding threshold from bottom
                    var _scrollTop = scrollBar.scrollTop();   // Current scroll position
                    var _divHeight = scrollBar.height();       // Container height
                    var _scrollHeight = scrollBar.get(0).scrollHeight; // Full scroll height

                    // Trigger window resize event (for possible layout recalculations)
                    $(window).trigger('resize');
                    // If the user has scrolled near the bottom within the threshold, call ajaxScroll
                    if (_scrollTop + _divHeight + _scrollPadding >= _scrollHeight) {
                        Main.ajaxScroll(false, scroll_no);
                    }
                }
            });
        }
    };

    /**
     * ajaxScroll
     *
     * Performs an AJAX call to load additional content (e.g., messages) when the user
     * scrolls near the bottom of the element. It uses a 'page' parameter for pagination,
     * handles filters, and updates the UI based on the results.
     *
     * @param {Boolean} reset_page If true, resets the pagination to the beginning.
     * @param {Number} scroll_no Optional identifier for multiple scroll containers.
     */
    Main.ajaxScroll = function(reset_page, scroll_no)
    {
        // Save the current scroll container identifier
        ScrollNo = scroll_no;
        
        // Determine the index suffix based on scroll_no
        var index = "";
        if (scroll_no != undefined && scroll_no != 0 && scroll_no != '')
            index = "-" + scroll_no;

        // Get the target element that holds the AJAX response
        var that = $('.ajax-scroll' + index);
        // URL to fetch results from (set in the HTML element's data attribute)
        var url = that.attr('data-url');
        // Optional target container to update the loaded data (if specified)
        var class_result = that.attr('data-resp');
        // Extra filters (if any) defined in elements with class "ajax-scroll-filter" with the same index
        var filter = $(".ajax-scroll-filter" + index);
        // Get the current page from the element's data-page attribute
        var page = parseInt(that.attr('data-page'));
        // Check if currently loading to avoid duplicate requests
        var loading = that.attr('data-loading');
        // Optional callback functions defined as data attributes
        var call_after = that.data("call-after");
        var call_success = that.data("call-success");

        // If reset_page is true, reset the page number and loading flag
        if (reset_page) {
            page = 0;
            loading = 0;
        }

        // Abort if the element does not exist
        if (that.length == 0) return false;
        // Abort if already loading content
        if (loading != undefined && loading != 0) return false;

        // If page is not defined or is NaN, initialize page and loading flag
        if(page == undefined || Number.isNaN(page))
        {
            page = 0;
            loading = 0;
            that.attr('data-page', 0);
            that.attr('data-loading', 0);
        }

        // Prepare data to send, including the current page
        var data = { page: page };

        // Collect data from filter elements (if any) and include in request data
        if (filter.length > 0)
        {
            filter.each(function(index, value) {
                // Check for checkbox or radio inputs
                if ($(this).attr("type") == "checkbox" || $(this).attr("type") == "radio") {
                    if ($(this).is(':checked')) {
                        var name = $(this).attr("name");
                        var value = $(this).val();
                        data[name] = value;
                    }
                }
                else {
                    var name = $(this).attr("name");
                    var value = $(this).val();
                    data[name] = value;
                }
            });
        }

        // Proceed only if the element is not disabled
        if (!that.hasClass("disabled")) {
            that.addClass("disabled");
            // Show loading indicator
            $('.ajax-scroll-loading').removeClass("d-none");
            that.attr('data-loading', 1);

            // Initiate the AJAX POST request
            $.ajax({
                url: url,
                type: 'POST',
                cache: false,
                data: data,
                headers: {
                    'X-CSRF-TOKEN': VARIABLES.csrf
                },
                dataType: 'JSON',
                error: function (xhr, textStatus, errorThrown) {
                    // On error, perform an overlay (if any), remove the disabled state, hide loading indicators, and show notification
                    Main.overplay(true);
                    that.removeClass("disabled");
                    $('.ajax-scroll-loading').addClass("d-none");
                    Main.showNotify("", xhr.status + " " + errorThrown, 0);
                }
            }).done(function(result) {
                // Once the AJAX request is done, remove the disabled state and hide the loading indicator.
                that.removeClass("disabled");
                $('.ajax-scroll-loading').addClass("d-none");

                // if result status indicates success (status==1)
                if (result.status == 1)
                {
                    // If it's the first page, replace content; otherwise, append to current content.
                    if (page == 0) {
                        if (class_result != undefined) {
                            $(class_result).html(result.data);
                        }
                        else {
                            that.html(result.data);
                        }
                    }
                    else {
                        if (class_result != undefined) {
                            $(class_result).append(result.data);
                        }
                        else {
                            that.append(result.data);
                        }
                    }
                }
                
                // If the result data is non-empty, set loading flag to 0 (ready for new loads)
                if (result.data != undefined && result.data.trim() != '') {
                    that.attr('data-loading', 0);
                }
                
                // Evaluate any callback function defined in data-call-after attribute
                if (call_after != undefined) {
                    eval(call_after);
                }

                // Evaluate any callback function defined in data-call-success attribute (only if success)
                if (call_success != undefined && result.status == 1) {
                    eval(call_success);
                }

                // Increment the page number for the next AJAX request
                that.attr('data-page', page + 1);
            });
        }
    },

    // -------------------------------------
    // Additional Functions for Scrolling TO TOP
    // -------------------------------------

    /**
     * Binds the scroll event to load more messages when the user scrolls to the top.
     * When the scroll bar is near the top (within 80px), an Ajax request to load previous messages is triggered.
     *
     * @param {Number} scroll_no - Optional identifier used if you have multiple scrollable elements.
     */
    Main.dataScrollTop = function(scroll_no) {
        ScrollNo = scroll_no;
        var index = (scroll_no === undefined || scroll_no == 0) ? "" : "-" + scroll_no;
        var that = $('.ajax-scroll-top' + index);
        var scrollDiv = that.attr('data-scroll');
        if (that.length > 0) {
            var scrollBar = (scrollDiv == 'document') ? $(document) : $(scrollDiv);
            scrollBar.bind('scroll', function() {
                if (scrollDiv == 'document') {
                    $(window).trigger('resize');
                    // When window scroll position reaches near top (within 80px)
                    if ($(window).scrollTop() <= 80) {
                        Main.ajaxScrollTop(false, scroll_no);
                    }
                } else {
                    var _scrollPadding = 80;
                    var _scrollTop = scrollBar.scrollTop();
                    if (_scrollTop <= _scrollPadding) {
                        Main.ajaxScrollTop(false, scroll_no);
                    }
                }
            });
        }
    };

    /**
     * Ajax function to load previous messages (older messages) when scrolling to the top.
     * This function works similarly to ajaxScroll but uses separate parameters and DOM attributes.
     *
     * Assumes the element has custom attributes:
     *     - data-url-top : the URL to load previous messages.
     *     - data-page-top : current page for top scrolling.
     *     - data-loading-top : loading flag for top scrolling.
     *
     * @param {boolean} reset_page - Whether to reset the pagination for top scrolling.
     * @param {Number} scroll_no - Optional identifier if multiple scroll elements exist.
     */
    Main.ajaxScrollTop = function(reset_page, scroll_no) {
        ScrollNo = scroll_no;
        var index = (scroll_no !== undefined && scroll_no != 0) ? "-" + scroll_no : "";
        var that = $('.ajax-scroll-top' + index);
        var url = that.attr('data-url-top'); // Use a separate URL for loading older messages.
        var class_result = that.attr('data-resp');
        var filter = $(".ajax-scroll-filter" + index);
        var page = parseInt(that.attr('data-page-top'));
        var loading = that.attr('data-loading-top');
        var call_after = that.data("call-after");
        var call_success = that.data("call-success");

        if (reset_page) {
            page = 0;
            loading = 0;
        }
        if (that.length == 0) return false;
        if (loading != undefined && loading != 0) return false;
        if (page == undefined || Number.isNaN(page)) {
            page = 0;
            loading = 0;
            that.attr('data-page-top', 0);
            that.attr('data-loading-top', 0);
        }
        var data = { page: page };
        if (filter.length > 0) {
            filter.each(function(index, value) {
                if ($(this).attr("type") == "checkbox" || $(this).attr("type") == "radio") {
                    if ($(this).is(':checked')) {
                        var name = $(this).attr("name");
                        var value = $(this).val();
                        data[name] = value;
                    }
                } else {
                    var name = $(this).attr("name");
                    var value = $(this).val();
                    data[name] = value;
                }
            });
        }
        if (!that.hasClass("disabled")) {
            that.addClass("disabled");
            $('.ajax-scroll-loading-top').removeClass("d-none");
            that.attr('data-loading-top', 1);

            // Determine the scroll container.
            var scrollDiv = that.attr('data-scroll');
            var scrollBar;
            if (scrollDiv === 'document') {
                // For the document, we use $(document) for height measurement,
                // but use $('html, body') for scrolling.
                scrollBar = $(document);
            } else {
                scrollBar = $(scrollDiv);
            }
            
            // Save the current scroll height before new content is prepended.
            var prevHeight = scrollBar.get(0).scrollHeight;

            $.ajax({
                url: url,
                type: 'POST',
                cache: false,
                data: data,
                headers: {
                    'X-CSRF-TOKEN': VARIABLES.csrf
                },
                dataType: 'JSON',
                error: function(xhr, textStatus, errorThrown) {
                    Main.overplay(true);
                    that.removeClass("disabled");
                    $('.ajax-scroll-loading-top').addClass("d-none");
                    Main.showNotify("", xhr.status + " " + errorThrown, 0);
                }
            }).done(function(result) {
                that.removeClass("disabled");
                $('.ajax-scroll-loading-top').addClass("d-none");

                if (result.status == 1) {
                    // Prepend older messages
                    if (page == 0) {
                        if (class_result != undefined) {
                            $(class_result).html(result.data);
                        } else {
                            that.html(result.data);
                        }
                    } else {
                        if (class_result != undefined) {
                            $(class_result).prepend(result.data);
                        } else {
                            that.prepend(result.data);
                        }
                    }
                }
                if (result.data != undefined && result.data.trim() != '') {
                    that.attr('data-loading-top', 0);
                }
                if (call_after != undefined) {
                    eval(call_after);
                }
                if (call_success != undefined && result.status == 1) {
                    eval(call_success);
                }
                that.attr('data-page-top', page + 1);

                // -----------------------------------------------------------
                // AUTO SCROLL TO BOTTOM AFTER LOADING OLDER MESSAGES
                // -----------------------------------------------------------
                // Determine if the scroll container is the document or another element.
                var scrollDiv = that.attr('data-scroll');
                if (page == 0) {
                    if (scrollDiv == 'document') {
                        // Animate scrolling the window to bottom.
                        $('html, body').animate({ scrollTop: $(document).height() }, 0);
                    } else {
                        // Animate scrolling the container to its full height.
                        $(scrollDiv).animate({ scrollTop: $(scrollDiv).get(0).scrollHeight }, 0);
                    }
                } else {
                    var scrollDiv = that.attr('data-scroll');
                    // For subsequent loads, calculate the new scroll height.
                    var newHeight = scrollBar.get(0).scrollHeight;
                    // newBlockHeight is the height of the newly prepended block.
                    var newBlockHeight = newHeight - prevHeight;
                    // Adjust the container's scroll to maintain the viewing position at the bottom 
                    // of the newly loaded block.
                    if (scrollDiv == 'document') {
                        var currentScroll = $('html, body').scrollTop();
                        $('html, body').animate({ scrollTop: currentScroll + newBlockHeight }, 0);
                    }else{
                        $(scrollDiv).animate({ scrollTop: scrollBar.scrollTop() + newBlockHeight }, 0);
                    }
                    


                }
            });
        }
    },

    Main.showAlertMsgs = function(that, errors, add_class){
        var el_errors = that.find(".msg-errors");
        var list_errors = "";

        el_errors.html('');

        $.each(errors, function( name, error ) {
            list_errors += '<li class="d-flex align-items-center gap-4">  <span>&#8226;</span> <span>'+error+'</span></li>';
        });

        var html = `<div class="`+add_class+`">
                        <ul>
                            `+list_errors+`
                        </ul>
                    </div>`;

        el_errors.html(html);
    },

    Main.showMsgs = function(errors) {
    // Clear all previous error messages and styles
    $("span[data-error-field]").remove();
    $(".border-danger").removeClass("border-danger");

    $.each(errors, function(name, error) {
        const $field = $("[name='" + name + "']");
        const type = $field.attr("type");

        if (type === "checkbox" || type === "radio") {
            const $wrapper = $field.first().closest("div.d-flex, .radio-group, .checkbox-group");
            $wrapper.find(".form-check").addClass("border-danger");

            if ($wrapper.next("span[data-error-field='" + name + "']").length === 0) {
                $wrapper.after(
                    "<span class='fs-12 text-danger d-block mt-1' data-error-field='" + name + "'>" + error + "</span>"
                );
            }

            $field.off(".removeError").on("change.removeError", function () {
                $wrapper.find(".form-check").removeClass("border-danger");
                $wrapper.next("span[data-error-field='" + name + "']").remove();
                $field.off(".removeError");
            });

        } else {
            let $inputGroup = $field.closest('.input-group');
            if ($inputGroup.length) {
                $field.addClass("border-danger");
                if ($inputGroup.next("span[data-error-field='" + name + "']").length === 0) {
                    $inputGroup.after(
                        "<span class='fs-12 text-danger' data-error-field='" + name + "'>" + error + "</span>"
                    );
                }
                $field.off(".removeError").on("input.removeError change.removeError", function () {
                    $(this).removeClass("border-danger");
                    $inputGroup.next("span[data-error-field='" + name + "']").remove();
                    $(this).off(".removeError");
                });
            } else {
                if ($field.hasClass("form-control")) {
                    $field.addClass("border-danger").after(
                        "<span class='fs-12 text-danger' data-error-field='" + name + "'>" + error + "</span>"
                    );
                } else {
                    $field.parent().addClass("border-danger").after(
                        "<span class='fs-12 text-danger' data-error-field='" + name + "'>" + error + "</span>"
                    );
                }
                $field.off(".removeError").on("input.removeError change.removeError", function () {
                    $(this).removeClass("border-danger");
                    $(this).parent().removeClass("border-danger");
                    $("span[data-error-field='" + name + "']").remove();
                    $(this).off(".removeError");
                });
            }
        }
    });
};

    Main.showNotify = function(_title, _message, _type){
        if(_message != undefined && _message != ""){
            switch(_type){
                case 1:
                    var backgroundColor = "#d3f3df";
                    var messageColor = "#22c55e";
                    var addClass = "border-success-subtle";
                    break;

                case 2:
                    var backgroundColor = "#fdefcc";
                    var messageColor = "#f6b100";
                    var addClass = "border-warning-subtle";
                    break;

                default:
                    var backgroundColor = "#fcdada";
                    var messageColor = "#ef4444";
                    var addClass = "border-danger-subtle";
                    break;
            }

            iziToast.show({
                theme: 'light',
                icon: 'fa-thin fa-bells',
                title: _title!=undefined?_title:'',
                class: 'border-1 '+addClass+' text-danger',
                titleColor: messageColor,
                messageColor: messageColor,
                iconColor: messageColor,
                position: 'bottomCenter',
                message: _message,
                backgroundColor: backgroundColor,
                progressBarColor: 'rgb(255, 255, 255, 0.5)',
                progressBar: false
            });
        }
    },

    Main.overplay = function(status){
        if(status == undefined){
            $(".loading").show();
        }else{
            $(".loading").hide();
        }
    };

    Main.callbacks = function(_function){
        $("body").append(_function);
    },

    Main.redirect = function(_rediect, _status){
        if(_rediect != undefined && _status == true){
            setTimeout(function(){
                window.location.assign(_rediect);
            }, 1500);
        }
    },

    Main.mixColor = function(hexColor, percentage){
      
        const r = parseInt(hexColor.slice(1, 3), 16); // r = 102
        const g = parseInt(hexColor.slice(3, 5), 16); // g = 51
        const b = parseInt(hexColor.slice(5, 7), 16); // b = 153

        const tintR = Math.round(Math.min(255, r + (255 - r) * percentage)); // 117
        const tintG = Math.round(Math.min(255, g + (255 - g) * percentage)); // 71
        const tintB = Math.round(Math.min(255, b + (255 - b) * percentage)); // 163

       
        const shadeR = Math.round(Math.max(0, r - r * percentage)); // 92
        const shadeG = Math.round(Math.max(0, g - g * percentage)); // 46
        const shadeB = Math.round(Math.max(0, b - b * percentage)); // 138

        return {
            tint: {
                r: tintR,
                g: tintG,
                b: tintB,
                hex:
                    '#' +
                    [tintR, tintG, tintB]
                        .map(x => x.toString(16).padStart(2, '0'))
                        .join(''),
            },
            shade: {
                r: shadeR,
                g: shadeG,
                b: shadeB,
                hex:
                    '#' +
                    [shadeR, shadeG, shadeB]
                        .map(x => x.toString(16).padStart(2, '0'))
                        .join(''),
            },
        };
    },

    Main.Calendar = function(calendarEl, option)
    {
        var merge = window._.merge;
        if(calendarEl !== undefined){
            var options = merge({
                    themeSystem: 'bootstrap5',
                    initialView: 'dayGridMonth',
                    editable: true,
                    locale: VARIABLES.lang,
                    direction: document.querySelector('html').getAttribute('dir'),
                    headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    month: 'Month',
                    week: 'Week',
                    day: 'Day'
                    }
                }, option);

            var calendar = new window.FullCalendar.Calendar(calendarEl, options);
            calendar.render();
            return calendar;
        }
    },

    Main.getScrollbarWidth = function(){
        return window.innerWidth - document.documentElement.clientWidth;
    },

    Main.isObject = function(value) {
        return value && typeof value === 'object' && value.constructor === Object;
    },

    Main.text2img = function(text, color = "675dff") {
        if (!text) text = "No Image";

        // If a color module exists and the provided color is not "rand" or "random",
        // override the color with the value from module("color")
        if (typeof module === "function" && module("color") && color !== "rand" && color !== "random") {
          color = module("color");
        }

        // If the color is "rand" or "random", choose randomly from the list.
        if (color === "random" || color === "rand") {
          const colors = [
            "#E74645",
            "#675dff",
            "#FB7756",
            "#FACD60",
            "#12492F",
            "#F7A400",
            "#58B368"
          ];
          // Select a random color from the array.
          const randIndex = Math.floor(Math.random() * colors.length);
          color = colors[randIndex];
        }
        
        // Remove any '#' characters from the color.
        color = color.replace(/#/g, "");

        // Remove unwanted characters from the text.
        // The order mirrors the PHP code.
        text = text.replace(/&/g, "")
                   .replace(/&amp;/g, "")
                   .replace(/=/g, "")
                   .replace(/&quot/g, "")
                   .replace(/"/g, "")
                   .replace(/'/g, "")
                   .replace(/~/g, "")
                   .replace(/ /g, "");
        
        // Decode the text. If text is not URL-encoded, decodeURIComponent may throw,
        // so we wrap it in a try/catch.
        let decodedText;
        try {
          decodedText = decodeURIComponent(text);
        } catch (e) {
          decodedText = text;
        }
        
        // Construct and return the URL.
        return "https://ui-avatars.com/api/?name=" +
               decodedText +
               "&background=" +
               color +
               "&color=fff&font-size=0.5&rounded=false&format=png";
    }

    Main.broadCast = function(options, callback){
        var PUSHER_URL = "https://js.pusher.com/8.2.0/pusher.min.js";
        switch(options.type)
        {

            case "pusher":

                var pusher;
                ((document, url) => {
                    const script = document.createElement("script");
                    script.src = url;
                    script.onload = async () => {
                        if (!window.pusher) {
                            //return;
                        }

                        // Initialize Pusher
                        pusher = new Pusher(options.app_key, {
                            cluster: options.cluster,
                            encrypted: true
                        });


                        // Subscribe to the 'comments' channel
                        var channel = pusher.subscribe(options.channel);
                        channel.bind(options.event, function(data){
                            callback(data);
                        });


                    };
                    document.body.appendChild(script);
                })(document, PUSHER_URL);

                break;
        }
    },

    Main.Chart = function(type, data, container, additionalOptions = {}) {
        let chart;

        Highcharts.setOptions({
            chart: {
                style: {
                    fontFamily: 'Inter, Roboto, sans-serif'
                }
            },
            exporting: {
                enabled: false
            },
            colors: ['#675dff', '#5a4cd6', '#837eff', '#9f91ff', '#bcb0ff'],
            tooltip: {
                shared: true,
                useHTML: true,
                backgroundColor: '#ffffff',
                borderColor: '#ddd',
                borderRadius: 8,
                shadow: true,
                style: {
                    padding: '0',
                    color: '#333',
                    fontSize: '12px',
                    fontFamily: 'Inter, sans-serif',
                    boxShadow: false
                },
                formatter: function () {
                    if (this.series.chart.options.chart.type === 'pie') {
                        const formattedValue = this.point.y.toLocaleString();
                        const label = this.point.name || ' ';
                        return `<div class="d-flex gap-8 justify-content-between align-items-center" style="padding: 4px 12px;">
                            <span class="fs-12"><span style="color: ${this.point.color};">●</span> ${label}:</span>
                            <span class="fs-12 fw-6">${formattedValue}</span>
                        </div>`;
                    } else {
                        const categories = additionalOptions.categories || [];
                        let date = '';
                        if (categories.length > 0 && this.x !== undefined) {
                            date = `<div style="
                                font-weight: bold;
                                padding: 8px 12px;
                                border-bottom: 1px solid #eee;
                                margin-bottom: 8px;
                            ">${categories[this.x]}</div>`;
                        } else {
                            date = `<div style="
                                font-weight: bold;
                                padding: 8px 12px;
                                border-bottom: 1px solid #eee;
                                margin-bottom: 8px;
                            ">${this.key || 'No Label'}</div>`;
                        }
                        let lines = '';
                        if (this.points && Array.isArray(this.points)) {
                            lines = this.points.map(point => {
                                const formattedValue = point.y.toLocaleString();
                                return `<div class="d-flex gap-8 justify-content-between px-2 p-y-1">
                                    <span class="fs-12 me-3"><span style="color: ${point.color};">●</span> ${point.series.name}</span>
                                    <span class="fs-12 fw-6">${formattedValue}</span>
                                </div>`;
                            }).join('');
                        }
                        return date + lines;
                    }
                }
            }
        });

        const defaultTitle = (additionalOptions.title && typeof additionalOptions.title === 'object')
            ? additionalOptions.title
            : {
                text: additionalOptions.title || `${type.charAt(0).toUpperCase() + type.slice(1)} Chart`,
                style: { color: 'transparent', fontSize: '12px', },
                margin: 30
            };

        switch (type) {
            case "map":
                chart = Highcharts.mapChart(container, {
                    credits: { enabled: false },
                    chart: { map: 'custom/world', spacingTop: 25, spacingRight: 15, marginRight: 15 },
                    title: defaultTitle,
                    ...additionalOptions,
                    series: [{
                        mapData: Highcharts.maps['custom/world'],
                        joinBy: ['iso-a2', 'code'],
                        data: data,
                        states: { hover: { color: '#BADA55' } },
                        dataLabels: { enabled: false }
                    }]
                });
                break;

            case "pie":
                chart = Highcharts.chart(container, {
                    credits: { enabled: false },
                    chart: { type: type, spacingTop: 25, spacingRight: 15, marginRight: 15 },
                    title: defaultTitle,
                    ...additionalOptions,
                    series: [{
                        name: 'Value',
                        colorByPoint: true,
                        data: data
                    }]
                });
                break;

            case "line":
            case "spline":
            case "areaspline":
            case "area":
            case "column":
            case "bar": 
            case "mix": {
                const cleanOptions = Object.assign({}, additionalOptions);
                delete cleanOptions.xAxis;

                const userXAxis = additionalOptions.xAxis || {};
                const defaultXAxis = Object.assign({
                    lineColor: '#e5e7eb',
                    lineWidth: 1,
                    gridLineWidth: 0,
                }, userXAxis);

                const userYAxis = additionalOptions.yAxis || {};
                const defaultYAxis = Object.assign({
                    gridLineWidth: 1,
                    gridLineColor: '#e5e7eb',
                    gridLineDashStyle: 'Dash',
                    title: { text: cleanOptions.yAxisTitle || ' ' }
                }, userYAxis);

                const chartType = (Array.isArray(data) && data.some(s => s.type)) ? undefined : type;

                chart = Highcharts.chart(container, Object.assign({
                    credits: { enabled: false },
                    chart: {
                        type: chartType, 
                        spacingTop: 25,
                        spacingRight: 15,
                        marginRight: 15
                    },
                    title: defaultTitle,
                    xAxis: defaultXAxis,
                    yAxis: defaultYAxis,
                    series: data
                }, cleanOptions));

                break;
            }
            
            default:
                console.warn(`Unsupported chart type: ${type}`);
                break;
        }

        window.ChartInstances[container] = chart;
        return chart;
    },

    Main.exportCharts = function() {
        $(document).off("click", ".exportPDF").on("click", ".exportPDF", function(e) {
            e.preventDefault();

            const action = $(this).attr("href");
            const chartIds = $(".export-chart").map(function() {
                const id = $(this).attr("id");
                return id ? id : null;
            }).get();

            if (chartIds.length === 0) {
                Main.showNotify('', "No valid charts to export.", 0);
                return;
            }

            const convertChartPromises = chartIds.map(id => {
                return new Promise((resolve) => {
                    const chart = window.ChartInstances?.[id];
                    if (!chart || typeof chart.getSVG !== "function") {
                        console.warn(`Chart with ID '${id}' is either invalid or not initialized.`);
                        resolve(null);
                        return;
                    }

                    const svg = chart.getSVG();
                    const cleanedSVG = svg.replace(/<text[^>]*>Chart title<\/text>/gi, '');
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const img = new Image();

                    img.onload = function() {
                        canvas.width = img.width;
                        canvas.height = img.height;
                        ctx.drawImage(img, 0, 0);
                        resolve({ id: id, base64: canvas.toDataURL("image/png") });
                    };

                    const svgBlob = new Blob([cleanedSVG], { type: 'image/svg+xml;charset=utf-8' });
                    img.src = URL.createObjectURL(svgBlob);
                });
            });

            Promise.all(convertChartPromises).then(results => {
                const validCharts = results.filter(item => item !== null);
                if (validCharts.length === 0) {
                    Main.showNotify('', "No valid charts to export.", 0);
                    return;
                }
                Main.sendChart(action, validCharts);
            });
        });
    },

    Main.sendChart = function(url, images){
        Main.overplay();
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ charts: images })
        }).then(response => response.blob())
          .then(blob => {
                Main.overplay(true);
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = name;
                document.body.appendChild(a);
                a.click();
                a.remove();
          });
    };
});

Main.init();