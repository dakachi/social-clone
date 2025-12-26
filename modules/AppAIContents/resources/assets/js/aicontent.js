var AIContent = new (function () 
{
    var AIContent = this;

    this.init = function( options ) 
    {
        $(document).on("click", ".openAICate", function(){
            $(".ai-form,.ai-result").addClass("d-none");
            $(".ai-cate").removeClass("d-none");
        });

        $(document).on("click", ".closeAICate, .closeAIResult", function(){
            $(".ai-cate,.ai-result").addClass("d-none");
            $(".ai-form").removeClass("d-none");
        });
    },

    this.openResult = function(){
        $(".ai-form,.ai-cate").addClass("d-none");
        $(".ai-result").removeClass("d-none");
    };
});

AIContent.init();