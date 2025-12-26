<?php

use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

if (!function_exists('canAccess')) {
    function canAccess($key)
    {
        if (!Auth::check()) return false;
        try {
            return Gate::allows($key);
        } catch (\Throwable $e) {
            return false;
        }
    } 
}

if (!function_exists('price')) {
    function price($price, $withSymbol = true)
    {
        $currency  = get_option("currency", "USD");
        $symbol    = get_option("currency_symbol", "$");
        $position  = get_option("currency_symbol_postion", "1");

        // Chuyển sang float và giới hạn 2 số thập phân
        $price = is_numeric($price) ? round((float)$price, 2) : $price;

        // Nếu là số, bỏ bớt số 0 dư (vd: 0.10 -> 0.1)
        if (is_numeric($price)) {
            $price = rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.');
        }

        if (!$withSymbol) {
            return $price;
        }

        return $position == "1"
            ? $symbol . $price
            : $price . ' ' . $symbol;
    }
}

if (!function_exists('theme_public_asset')) {
    function theme_public_asset($path)
    {
        $theme = app()->bound('theme') ? app('theme') : '';
        return asset("resources/themes/{$theme}/public/{$path}");
    }
}

if( !function_exists('get_header') ){
    function get_header($path)
    {   
        try {
            $stream_opts = [
                "ssl" => [
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ]
            ]; 

            $headers = get_headers( $path , 1, stream_context_create($stream_opts));
            if(!$headers){
                return false;
            }

            $headers = array_change_key_case($headers, CASE_LOWER);

            return $headers;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('menu_active')) {
    function menu_active($uri) {
        if (is_array($uri)) {
            foreach ($uri as $u) {
                if (request()->is($u) || request()->is($u . '/*')) {
                    return true;
                }
            }
            return false;
        }
        return request()->is($uri) || request()->is($uri . '/*');
    }
}

if (!function_exists('theme_vite')) {
    function theme_vite(string $theme, string $file = 'assets/js/app.js')
    {
        static $isDevServer = null;
        static $devServerUrl = null;

        if (is_null($isDevServer)) {
            $devServerUrl = env('VITE_DEV_URL', 'http://localhost:5173');
            try {
                $context = stream_context_create(['http' => ['timeout' => 0.3]]);
                $headers = @get_headers($devServerUrl, 1, $context);
                $isDevServer = $headers !== false;
            } catch (\Exception $e) {
                $isDevServer = false;
            }
        }

        $filePath = "resources/themes/{$theme}/{$file}";

        if ($isDevServer) {
            $viteClient = '<script type="module" src="' . $devServerUrl . '/@vite/client"></script>';
            $themeAsset = '<script type="module" src="' . $devServerUrl . '/' . $filePath . '"></script>';
            return new HtmlString($viteClient . "\n" . $themeAsset);
        }

        $manifestPath = base_path("resources/themes/{$theme}/public/.vite/manifest.json");
        if (!file_exists($manifestPath)) {
            return new HtmlString("<!-- Vite manifest not found for theme {$theme} -->");
        }
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (!isset($manifest[$filePath])) {
            return new HtmlString("<!-- Asset {$filePath} not found in manifest for theme {$theme} -->");
        }

        $html = '';
        if (!empty($manifest[$filePath]['css'])) {
            foreach ($manifest[$filePath]['css'] as $css) {
                $cssPath = "/resources/themes/{$theme}/public/" . $css;
                $html .= '<link rel="stylesheet" href="' . asset($cssPath) . '">' . PHP_EOL;
            }
        }
        $jsPath = "/resources/themes/{$theme}/public/" . $manifest[$filePath]['file'];
        $html .= '<script type="module" src="' . asset($jsPath) . '"></script>';
        return new HtmlString($html);
    }
}

function watermark($media= "", $team = 1, $account = 1){
    return $media;
}

function unlink_watermark($media){
    return true;
}

if (!function_exists('country_name_to_iso')) {
    function country_name_to_iso(string $name): ?string
    {
        static $nameToIso = null;

        if (!$nameToIso) {
            $path = base_path('vendor/umpirsky/country-list/data/en/country.php');
            $countries = file_exists($path) ? require $path : [];
            $nameToIso = array_flip($countries);
        }

        return isset($nameToIso[$name]) 
            ? strtoupper($nameToIso[$name]) 
            : null;
    }
}

if (!function_exists('getLinkInfo')) {
    function getLinkInfo($url)
    {
        $info = [
            'title' => '',
            'description' => '',
            'image' => theme_asset("img/default.png"),
            'host' => '',
        ];
        $parse_url = @parse_url($url);
        $info['host'] = $parse_url['host'] ?? '';

        if (preg_match('/(youtube\.com|youtu\.be)/i', $url)) {
            $json = get_curl("https://www.youtube.com/oembed?url=" . urlencode($url) . "&format=json");
            if ($json) {
                $data = json_decode($json);
                $info['title'] = $data->title ?? '';
                $info['image'] = $data->thumbnail_url ?? '';
            }
            return $info;
        }

        $html = get_curl($url);
        if (!$html) return $info;
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', mb_detect_encoding($html, mb_list_encodings(), true));
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        libxml_clear_errors();
        $titleTags = $doc->getElementsByTagName('title');
        $info['title'] = $titleTags->length ? trim($titleTags->item(0)->textContent) : '';
        $metas = $doc->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            $name = strtolower($meta->getAttribute('name'));
            $property = strtolower($meta->getAttribute('property'));
            $content = $meta->getAttribute('content');
            if (!$info['description'] && (in_array($name, ['description']) || $property == 'og:description')) {
                $info['description'] = $content;
            }
            if (!$info['image'] && $property == 'og:image') {
                $info['image'] = $content;
            }
        }
        if (!$info['description']) {
            $body = $doc->getElementsByTagName('body');
            if ($body->length) {
                $text = strip_tags($body->item(0)->textContent);
                $info['description'] = mb_substr(trim($text), 0, 250, 'UTF-8') . (mb_strlen($text, 'UTF-8') > 250 ? '...' : '');
            }
        }
        return $info;
    }
}

if (!function_exists("cmp_sidebar")) {
    function cmp_sidebar($a, $b)
    {
        if ($a['tab_id'] == $b['tab_id']) {
            if ($a['position'] == $b['position']) {
                return 0;
            }
            return $a['position'] > $b['position'] ? -1 : 1;
        } else {
            return $a['tab_id'] < $b['tab_id'] ? -1 : 1;
        }
    }
}

if (!function_exists('ex_str')) {
    function ex_str($string, $index = 1, $delimiter = "\\")
    {
        if ($string != "") {
            $string_arr = explode($delimiter, $string);
            if (count($string_arr) == 1) return $string_arr[0];
            if (count($string_arr) - 1 < $index) return $string;
            if (count($string_arr) - 1 >= $index) return $string_arr[$index];
        }
        return $string;
    }
}

if (!function_exists('spintax')) {
    function spintax($str) {
        return preg_replace_callback("/{(.*?)}/", function ($match) {
            $words = explode("|", $match[1]);
            return $words[array_rand($words)];
        }, $str);
    }
}

if (!function_exists("module_url")) {
    function module_url($path = "") 
    {
        $module = request()->module;
        if(!$module) return false;
        return url($module['uri']."/".$path);
    }
}

if (!function_exists("module")) {
    function module($key = "") 
    {
        $module = request()->module;
        if(!$module) return false;
        if(!isset($module[$key])) return $module;
        return $module[$key];
    }
}

if (!function_exists("module_folder_url")) {
    function module_folder_url($path = "", $folder = "resources", $module_name = "") 
    {
        if($module_name == ""){
            $module = request()->module;
            $module_name = $module['module_name'];
        }
        return Module::asset($module_name.'::'). "/". $folder . $path;
    }
}

if (!function_exists('pr')) {
    function pr($data, $type = 0) 
    {
        print '<pre>';
        print_r($data);
        print '</pre>';
        if ($type != 0) exit();
    }
}

if (!function_exists("get_option")) {
    function get_option($name, $default = "")
    {
        if (!app()->bound('options')) {
            $options = [];
            foreach (DB::table('options')->get(['name', 'value']) as $item) {
                $options[$item->name] = $item->value;
            }
            app()->instance('options', $options);
        }

        $options = app('options');
        if (isset($options[$name])) {
            return $options[$name];
        }

        DB::table('options')->insert(['name' => $name, 'value' => $default]);
        $options[$name] = $default;
        app()->instance('options', $options);

        return $default;
    }
}

if (!function_exists("update_option")) {
    function update_option($name, $value)
    {
        DB::table('options')->where("name", $name)->update(
            ['value' => $value]
        );
        if (app()->bound('options')) {
            app()->forgetInstance('options');
        }
    }
}


if (!function_exists('ms')) {
    function ms($array, $type = false)
    {
        $json = new \Illuminate\Http\JsonResponse($array);
        if($type)
            return $json;
        else{
            print_r(json_encode($array));
            exit(0);
        }
    }
}

if (!function_exists("url_admin")) {
    function url_admin($path)
    {
        return url("admin/".$path);
    }
}

if (!function_exists("url_app")) {
    function url_app($path)
    {
        return url("app/".$path);
    }
}

if (!function_exists("changeDateKeepTime")) {
    function changeDateKeepTime($newDate, $timePost, $timezoneString = 'Asia/Ho_Chi_Minh') {
        $tz = new DateTimeZone($timezoneString);
        $cleanedNewDate = preg_replace('/\s\(.+\)$/', '', $newDate);
        $newDateObj = new DateTime($cleanedNewDate, $tz);
        $newDateObj->setTimezone($tz);
        $timePostObj = new DateTime("@" . (int)$timePost);
        $timePostObj->setTimezone($tz);
        $hours   = (int)$timePostObj->format('H');
        $minutes = (int)$timePostObj->format('i');
        $seconds = (int)$timePostObj->format('s');
        $newDateObj->setTime($hours, $minutes, $seconds);
        return $newDateObj->getTimestamp();
    }
}

if (!function_exists("FormatData")) {
    function FormatData($type, $value)
    {
        switch ($type) {
            case 'datetime':
                return datetime_show($value);
            case 'date':
                return date_show($value);
            case 'time':
                return time_show($value);
            case 'time_elapsed':
                return time_elapsed_string($value);
            default:
                return $value;
        }
    }
}

if (!function_exists("rand_string")) {
    function rand_string($length = 10) 
    {
        $char = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $char = str_shuffle($char);
        for($i = 0, $rand = '', $l = strlen($char) - 1; $i < $length; $i ++) 
        {
            $rand .= $char[mt_rand(0, $l)];
        }
        return $rand;
    }
}

if (!function_exists("text2img")) {
    function text2img($text, $color = "rand")
    {
        if(module('color') && $color != "rand" && $color != "random")
        {
            $color = module('color');
        }
        if($color == "random" || $color == "rand")
        {
            $color = generateSinglePastelColor();
        }
        $color = str_replace("#", "", $color);
        $text = str_replace(["&", "&amp;", "=", "&quot", "\"", "'", "~", " "], "", $text);
        return "https://ui-avatars.com/api/?name=".urldecode($text)."&background=".$color."&color=fff&font-size=0.5&rounded=false&format=png";
    }
}

if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false) 
    {
        if (!is_numeric($datetime)) {
            $datetime = strtotime($datetime);
        }
        $datetime = Carbon::createFromTimestamp($datetime);
        $now = Carbon::now();
        $diff = $now->diff($datetime);

        $weeks = floor($diff->d / 7);
        $diff->d -= $weeks * 7;

        $units = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        $string = [];
        foreach ($units as $k => $unit) {
            if ($k === 'w' && $weeks) {
                $string[$k] = sprintf(__('%s ' . $unit . '%s ago'), $weeks, ($weeks > 1 ? 's' : ''));
            } elseif ($k !== 'w' && $diff->$k) {
                $string[$k] = sprintf(__('%s ' . $unit . '%s ago'), $diff->$k, ($diff->$k > 1 ? 's' : ''));
            }
        }
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) : __('Just now');
    }
}

if (!function_exists("id_arr")) {
    function id_arr($ids)
    {
        $id_arr = [];
        if(is_array($ids))
        {
            $id_arr = array_filter($ids);
        }
        else if( is_string($ids) )
        {
            $id_arr[] = $ids;
        }
        return $id_arr;
    }
}

if (!function_exists('tz_list')) {
    function tz_list() {
        $zones_array = [];
        $timestamp = time();
        foreach(timezone_identifiers_list() as $zone) {
            $dtz = new DateTimeZone($zone);
            $dt = new DateTime("now", $dtz);
            $offset = $dtz->getOffset($dt);
            $hours = floor($offset / 3600);
            $minutes = abs(floor(($offset % 3600) / 60));
            $sign = $hours < 0 ? '-' : '+';
            $formatted_offset = sprintf("%s%02d:%02d", $sign, abs($hours), $minutes);
            $zones_array[] = [
                'zone' => $zone,
                'label' => '(UTC ' . $formatted_offset . ") " . $zone,
                'sort' => $offset,
            ];
        }
        usort($zones_array, function($a, $b) {
            return $a['sort'] <=> $b['sort'];
        });
        $timezones = [];
        foreach ($zones_array as $value) {
            $timezones[$value['zone']] = $value['label'];
        }
        return $timezones;
    }
}

if (!function_exists('tz_list_number')){
    function tz_list_number($timezone) {
        $zones_array = array();
        $timestamp = time();
        foreach(timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['time'] = '(UTC ' . date('P', $timestamp).") ".$zone;
            $zones_array[$key]['sort'] = date('P', $timestamp);
        }
        usort($zones_array, function($a, $b) {
            return strcmp($a["sort"], $b["sort"]);
        });
        $timezones = array();
        foreach ($zones_array as $value) {
            $timezones[$value['zone']] = $value['sort'];
        }
        return $timezones[$timezone];
    }
}

if (!function_exists('data')) {
    function data($data, $field, $type = '', $value = '', $default_value = -1, $class = 'active'){
        if(!empty($data)){
            if(is_array($data)){
                if(isset($data[$field])){
                    switch ($type) {
                        case 'checkbox':
                        case 'radio':
                            if($data[$field] == $value || $data[$field] == $default_value){
                                return 'checked';
                            }
                            break;
                        case 'select':
                            if($data[$field] == $value || $data[$field] == $default_value){
                                return 'selected';
                            }
                            break;
                        case 'class':
                            if($data[$field] == $value || $data[$field] == $default_value){
                                return $class;
                            }
                            break;
                        default:
                            return $data[$field];
                    }
                }
            }else{
                if(isset($data->$field)){
                    switch ($type) {
                        case 'checkbox':
                        case 'radio':
                            if($data->$field == $value){
                                return 'checked';
                            }
                            break;
                        case 'select':
                            if($data->$field == $value){
                                return 'selected';
                            }
                            break;
                        case 'class':
                            if($data->$field == $value){
                                return $class;
                            }
                            break;
                        default:
                            return $data->$field;
                    }
                }
            }
        }else{
            switch ($type) {
                case 'checkbox':
                case 'radio':
                    if($value == $default_value){
                        return 'checked';
                    }
                    break;
                case 'select':
                    if($value == $default_value){
                        return 'selected';
                    }
                    break;
                case 'class':
                    if($value == $default_value){
                        return $class;
                    }
                    break;
                default:
                    return "";
            }
        }
        return false;
    }
}

if (!function_exists("get_curl")) {
    function get_curl($url, $custom_headers = false){
        $user_agent='Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3B48b Safari/419.3';
        if($custom_headers){
            $headers = $custom_headers;
        }else{
            $headers = [
                'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,fr;q=0.8;q=0.6,en;q=0.4,ar;q=0.2',
                'Accept-Encoding: gzip,deflate',
                'Accept-Charset: utf-8;q=0.7,*;q=0.7',
                'cookie:datr=; locale=en_US; sb=; pl=n; lu=gA; c_user=; xs=; act=; presence='
            ]; 
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "GET");
        curl_setopt($ch, CURLOPT_POST, false);     
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_REFERER, url(""));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

if (!function_exists("generateSinglePastelColor")) {
    function generateSinglePastelColor($pastelFactorPercent = 0)
    {
        $p = max(0, min(100, $pastelFactorPercent)) / 100;
        $lower = round($p * 360);
        $red   = rand($lower, 255);
        $green = rand($lower, 255);
        $blue  = rand($lower, 255);
        return sprintf("#%02x%02x%02x", $red, $green, $blue);
    }
}

if (!function_exists("isZeroDecimalCurrency")) {
    function isZeroDecimalCurrency($currency)
    {
        if (!is_string($currency)) return false;
        $zero_decimal_currencies = [
            "BIF", "CLP", "DJF", "GNF", "JPY", "KMF", "KRW",
            "MGA", "PYG", "RWF", "VND", "VUV", "XAF", "XOF", "XPF", 
            "HUF", "TWD"
        ];
        return in_array(strtoupper($currency), $zero_decimal_currencies);
    }
}

if(!function_exists('groupArray')){
    function groupArray($flat) {
        $result = [];
        $map = [];
        foreach ($flat as $item) {
            if (preg_match('/^([a-zA-Z0-9_]+)\.(.+)$/', $item['key'], $m)) {
                $parentKey = $m[1];
                if (!isset($map[$parentKey])) {
                    $map[$parentKey] = ['children' => []];
                }
                $map[$parentKey]['children'][] = $item;
            } else {
                $map[$item['key']] = $item;
            }
        }
        foreach ($map as $key => $value) {
            if (isset($value['children'])) {
                if (isset($map[$key]['label'])) {
                    $item = $map[$key];
                    $item['children'] = $value['children'];
                    $result[] = $item;
                } else {
                    $item = [
                        'key' => $key,
                        'label' => $key,
                        'value' => null,
                        'children' => $value['children'],
                    ];
                    $result[] = $item;
                }
            } else {
                if (!isset($value['children'])) {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
}

include_once "Language_Helper.php";
include_once "AI_Helper.php";
include_once "Date_Helper.php";
