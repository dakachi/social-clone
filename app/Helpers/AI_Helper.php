<?php
if (!function_exists("tone_of_voices")) {
    function tone_of_voices(){
        $tone_of_voices = [
            "Polite" => __("Polite"),
            "Witty" => __("Witty"),
            "Enthusiastic" => __("Enthusiastic"),
            "Friendly" => __("Friendly"),
            "Informational" => __("Informational"),
            "Funny" => __("Funny"),
            "Formal" => __("Formal"),
            "Informal" => __("Informal"),
            "Humorous" => __("Humorous"),
            "Serious" => __("Serious"),
            "Optimistic" => __("Optimistic"),
            "Motivating" => __("Motivating"),
            "Respectful" => __("Respectful"),
            "Assertive" => __("Assertive"),
            "Conversational" => __("Conversational"),
            "Casual" => __("Casual"),
            "Professional" => __("Professional"),
            "Smart" => __("Smart"),
            "Nostalgic" => __("Nostalgic")
        ];

        return $tone_of_voices;
    }
}

if (!function_exists("ai_creativity")) {
    function ai_creativity(){
        $creativity = [
            "0.25" => __("Economic"),
            "0.5" => __("Average"),
            "0.75" => __("Good"),
            "1" => __("Premium"),
        ];

        return $creativity;
    }
}