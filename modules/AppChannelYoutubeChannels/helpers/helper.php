<?php
if (!function_exists('getYoutubeCategories')) {
    /**
     * Returns an associative array of YouTube categories.
     *
     * Keys represent the category IDs and values are the corresponding category names.
     *
     * @return array
     */
    function getYoutubeCategories()
    {
        return [
            0  => __('Select a category'),
            1  => __('Film & Animation'),
            2  => __('Autos & Vehicles'),
            10 => __('Music'),
            15 => __('Pets & Animals'),
            17 => __('Sports'),
            19 => __('Travel & Events'),
            20 => __('Gaming'),
            22 => __('People & Blogs'),
            23 => __('Comedy'),
            24 => __('Entertainment'),
            25 => __('News & Politics'),
            26 => __('Howto & Style'),
            27 => __('Education'),
            28 => __('Science & Technology'),
            29 => __('Nonprofits & Activism'),
        ];
    }
}