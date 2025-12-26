var AIPublishing = new (function () {
    var AIPublishing = this;

    this.init = function () {
        // Attach events for actions
        $(document).on("click", ".addSpecificTimes", AIPublishing.addSpecificTime);
        $(document).on("click", ".listPostByTimes .remove:not(.disabled)", AIPublishing.removeSpecificTime);

        // Check initial state
        AIPublishing.updateRemoveButtonState();
    };

    // Add a new time slot
    this.addSpecificTime = function () {
        var item = $(".tempPostByTimes").find(".input-group");
        var c = item.clone();
        c.find("input").attr("name", "time_posts[]").addClass("onlytime").val("");
        $(".listPostByTimes").append(c);

        // Call the function to handle DateTime
        Main.DateTime();

        // Update the state of the remove button
        AIPublishing.updateRemoveButtonState();

        return false;
    };

    // Remove a specific time slot
    this.removeSpecificTime = function () {
        $(this).parents(".input-group").remove();

        // Update the state of the remove button
        AIPublishing.updateRemoveButtonState();
    };

    // Update the state of the remove buttons
    this.updateRemoveButtonState = function () {
        var removeButtons = $(".listPostByTimes .remove");
        if (removeButtons.length < 2) {
            removeButtons.addClass("disabled");
        } else {
            removeButtons.removeClass("disabled");
        }
    };
})();

// Initialize AIPublishing
AIPublishing.init();