"use strict";

var Main = new (function () {
    var Main = this;
    var ScrollNo = 0;
    var that_tmp, action_tmp, data_tmp, _function_tmp;

    Main.init = function () {
        Main.switchTheme();
        Main.Layout();
        Main.actionItem();
        Main.actionMultiItem();
        Main.actionForm();
        Main.initCookiePolicy();

        $(document).on('click', '.btn-accept', function(e) {
            e.preventDefault();
            Main.acceptCookies();
        });

        $(document).on('click', '.btn-decline', function(e) {
            e.preventDefault();
            Main.declineCookies();
        });
    },

    Main.initCookiePolicy = function() {
        var status = localStorage.getItem('cookie_policy');
        if(status === 'accepted' || status === 'denied') {
            $('.cookie-policy-bar').remove();
        }
    };

    Main.acceptCookies = function() {
        localStorage.setItem('cookie_policy', 'accepted');
        $('.cookie-policy-bar').hide();
    };

    Main.declineCookies = function() {
        localStorage.setItem('cookie_policy', 'denied');
        $('.cookie-policy-bar').hide();
    };

    Main.switchTheme = function () {
        var $switch = $('.theme-checkbox');
        function setTheme(theme) {
            if (theme === 'dark') {
                $('html').addClass('dark').attr('data-theme', 'dark');
                $switch.prop('checked', true);
            } else {
                $('html').removeClass('dark').attr('data-theme', 'light');
                $switch.prop('checked', false);
            }
            localStorage.setItem('theme', theme);
        }
        // Init on load
        var saved = localStorage.getItem('theme');
        if (saved === 'dark' || saved === 'light') {
            setTheme(saved);
        } else {
            setTheme(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        }
        // On change any switch
        $switch.on('change', function () {
            setTheme($(this).is(':checked') ? 'dark' : 'light');
        });
    },

    Main.Layout = function () {
        var $header = $("#main-header");

        // Scroll behavior (shadow, bg)
        function handleScroll() {
            if ($(window).scrollTop() > 10) {
                $header.addClass("bg-base-100 shadow");
            } else {
                $header.removeClass("bg-base-100 shadow");
            }
        }
        handleScroll();
        $(window).on("scroll", handleScroll);

        // Tabs with fade animation
        var $tabButtons = $('.lqd-tabs-triggers [data-target]');
        var $tabContents = $('.lqd-tab-content');

        $tabButtons.on('click', function (e) {
            e.preventDefault();
            var $btn = $(this);
            $tabButtons.removeClass('lqd-is-active');
            $btn.addClass('lqd-is-active');

            $tabContents.removeClass('block opacity-100').addClass('hidden opacity-0');

            var targetSelector = $btn.data('target');
            var $target = $(targetSelector);

            if ($target.length) {
                setTimeout(function () {
                    $target.removeClass('hidden').addClass('block');
                    setTimeout(function () {
                        $target.removeClass('opacity-0').addClass('opacity-100');
                    }, 10);
                }, 100);
            }
        });
    },

    /*
    * Form Action
    */
    this.actionItem = function()
    {
        $(document).on('click', ".actionItem", function(event) {
            event.preventDefault();    
            var that    = $(this);
            var action  = that.attr("href");
            var id      = that.data("id");
            var data   = new FormData();
            data.append("id", id);
            Main.ajaxPost(that, action, data, null);
            return false;
        });
    },

    this.actionMultiItem = function()
    {
        $(document).on('click', ".actionMultiItem", function(event) {
            event.preventDefault();    
            var that     = $(this);
            var form     = that.closest("form");
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

    this.actionForm = function()
    {
        $(document).on('submit', ".actionForm", function(event) {
            event.preventDefault();    
            var that   = $(this);
            var action = that.attr("action");
            var data   = new FormData();

            if(that.length > 0){
                var formData = that.serializeArray();
                if(formData.length > 0){
                    for (var i = 0; i < formData.length; i++) {
                        data.append(formData[i].name, formData[i].value);
                    }
                }
            }
            
            Main.ajaxPost(that, action, data, null);
        });
    },

    this.ajaxPost = function(that, action, data, _function, pass_confirm)
    {

        that_tmp = that;
        action_tmp = action;
        data_tmp = data;
        _function_tmp = _function;

        var popup          = that.data("popup");
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
        var type           = that.data("result");
        var activeClass    = that.data("active");
        var object         = false;

        if(type == undefined && popup == undefined ){
            type = 'json';
        }

        if(confirm != undefined && pass_confirm == undefined){
            Main.ConfirmDialog(confirm, function(s){
                if(s){
                    Main.ajaxPost(that_tmp, action_tmp, data_tmp, _function_tmp, true);
                }
            });

            return false;
            //if(!window.confirm(confirm)) return false;
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
                    //Check is object
                    if(typeof result != 'object'){
                        try {
                            result = $.parseJSON(result);
                            object = true;
                        } catch (e) {
                            object = false;
                        }
                    }else{
                        object = true;
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
                        that.removeClass("tag-success tag-error").addClass(result.tag).text(result.text);
                    }

                    //Add content
                    if(content != undefined && object == false){
                        if(append_content != undefined){
                            $("."+content).append(result);
                        }else{
                            $("."+content).html(result);
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

                        if(window.grecaptcha) grecaptcha.reset();
                        if(window.turnstile) turnstile.reset();

                        if(result.error_type == 2){
                            Main.showMsgs(result.errors);
                        }else if(result.error_type == 3){
                            Main.showAlertMsgs(that, result.errors, result.class);
                        }else if(result.error_type == 4){
                            Main.showMsg(that, result.message, result.class);
                            if(result.status == 1){
                                $(".input-msg-error").remove();
                                $(".input-error").removeClass('input-error border-error');
                            }
                        }else{
                            switch(type_message){
                                case "text":
                                    Main.showNotify(result.title, result.message, result.status);
                                    break;

                                default:
                                    Main.showNotify(result.title, result.message, result.status);
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

                },
                error: function (result) {
                    that.removeClass("disabled");
                }
            });
        }

        return false;
    },

    this.showAlertMsgs = function(that, errors, add_class){
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

    this.showMsg = function(that, error, add_class){
        var el_errors = that.find(".msg-error");
        var html = `<div class="`+add_class+`">`+error+`</div>`;
        el_errors.html('');
        el_errors.html(html);
    },

    this.showMsgs = function(errors){
        $.each(errors, function( name, error ) {
            $("span[data-error-field='"+name+"']").remove();
            if($("[name='"+name+"']").addClass("input-error")){
                $("[name='"+name+"']").addClass("border-error").after("<span class='fs-12 text-error input-msg-error' data-error-field='"+name+"'>"+error+"</span>");
            }else{
                $("[name='"+name+"']").parent().addClass("input-error border-error").after("<span class='fs-12 text-error input-msg-error' data-error-field='"+name+"'>"+error+"</span>");
            }
        });
    },

    this.showNotify = function(_title, _message, _type){
        console.log(_title);
        if(_message != undefined && _message != ""){
            switch(_type){
                case 1:
                    var backgroundColor = "#d1f4dd";
                    var messageColor = "#17c653";
                    var addClass = "border-success-subtle";
                    break;

                case 2:
                    var backgroundColor = "#fdefcc";
                    var messageColor = "#f6b100";
                    var addClass = "border-warning-subtle";
                    break;

                default:
                    var backgroundColor = "#fed4de";
                    var messageColor = "#f8285a";
                    var addClass = "border-error-subtle";
                    break;
            }

            iziToast.show({
                theme: 'light',
                icon: 'fa-thin fa-bells',
                title: _title!=undefined?_title:'',
                class: 'border-1 '+addClass+' text-error',
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

    this.overplay = function(status){
        if(status == undefined){
            $(".loading").show();
        }else{
            $(".loading").hide();
        }
    };

    this.callbacks = function(_function){
        $("body").append(_function);
    },

    this.redirect = function(_rediect, _status){
        if(_rediect != undefined && _status == true){
            setTimeout(function(){
                window.location.assign(_rediect);
            }, 1500);
        }
    };



    document.querySelectorAll('.faq-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i');
            
            // Toggle content visibility
            content.classList.toggle('hidden');
            
            // Rotate icon
            icon.classList.toggle('transform');
            icon.classList.toggle('rotate-180');
        });
    });    
})();

Main.init();
