"use strict";

var Watermark = new (function () 
{
    var Watermark = this;

    this.init = function(options) 
    {
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': VARIABLES.csrf}
        });

        Watermark.Actions();
        setTimeout(function() {
            Watermark.Render();
        }, 50);
    };

    this.Actions = function() {
        $(document).on("click", ".watermark-positions .watermark-position-item", function() {
            $(this).addClass('active').siblings().removeClass('active');
            Watermark.Render();
        });

        $(document).on("input change", ".watermark-size, .watermark-transparent", function() {
            // Update số bên cạnh slider (nếu có)
            $('.size-value').text($('.watermark-size').val());
            $('.opacity-value').text($('.watermark-transparent').val());
            Watermark.Render();
        });

        $(document).on("change", ".form-file-input", function(e) {
            var file = this.files[0];
            if (file) {
                var imgURL = URL.createObjectURL(e.target.files[0]);
                $('.watermark-mask').attr("src", imgURL);
                setTimeout(function() {
                    Watermark.Render();
                }, 50);
            }
        });
    }

    this.Render = function() {
        var $mask = $('.watermark-mask');
        var $active = $(".watermark-positions .watermark-position-item.active");
        var type = $active.length ? $active.data("direction") : "lt";
        var size = $(".watermark-size").val();
        var transparent = $(".watermark-transparent").val();

        $mask.css({top: '', left: '', right: '', bottom: '', marginLeft: '', marginTop: '', width: '', opacity: ''});
        $mask.css("width", (60 + 1.3 * size) + "px");
        $mask.css("opacity", transparent / 100);
        $mask.removeClass("d-none");

        var width = $mask.width();
        var height = $mask.height();

        $(".watermark-position").val(type);

        switch(type){
            case "lt":
                $('.watermark-mask').css({"top": 0, "left": 0, "margin-left": 0, "margin-top": 0});
                break;

            case "ct":
                $('.watermark-mask').css({"top": 0, "left": 50+"%", "margin-left": "-"+width/2+"px", "margin-top": 0});
                break;

            case "rt":
                $('.watermark-mask').css({"top": 0, "right": 0, "left": "inherit", "margin-left": 0, "margin-top": 0});
                break;

            case "lc":
                $('.watermark-mask').css({"top": 50+"%", "left": 0, "margin-left": 0, "margin-top": "-"+height/2+"px"});
                break;

            case "cc":
                $('.watermark-mask').css({"top": 50+"%", "left": 50+"%", "margin-left": "-"+width/2+"px", "margin-top": "-"+height/2+"px"});
                break;

            case "rc":
                $('.watermark-mask').css({"top": 50+"%", "right": 0, "left": "inherit", "margin-left": 0, "margin-top": "-"+height/2+"px"});
                break;

            case "lb":
                $('.watermark-mask').css({"bottom": 0, "left": 0, "top": "inherit", "margin-left": 0});
                break;

            case "cb":
                $('.watermark-mask').css({"bottom": 0, "left": 50+"%", "top": "inherit", "margin-left": -width/2+"px"});
                break;

            case "rb":
                $('.watermark-mask').css({"bottom": 0, "right": 0, "top": "inherit", "left": "inherit", "margin-left": 0});
                break;
        }
    };
})();

$(function() {
    Watermark.init();
});
