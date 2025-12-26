<?php
use Carbon\Carbon;

if (!function_exists('date_short')) {
    function date_short($data) {
        if (empty($data)) return false;
        if (!is_numeric($data)) $data = strtotime($data);
        $date = date("M j", $data);
        $months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
        foreach ($months as $mon) {
            $date = str_replace($mon, __($mon), $date);
        }
        return $date;
    }
}

if (!function_exists('getDefaultDateFormat')) {
    function getDefaultDateFormat() {
        return 'd/m/Y';
    }
}

if (!function_exists('getDefaultDateTimeFormat')) {
    function getDefaultDateTimeFormat() {
        return 'd/m/Y H:i';
    }
}

if (!function_exists('getDateTimeFormats')) {
    function getDateTimeFormats($current = null) {
        $formats = [
            'd/m/Y H:i',
            'm/d/Y h:i A',
            'Y-m-d H:i',
            'd-m-Y H:i',
            'M d Y H:i',
            'd M Y H:i',
            'j/n/Y H:i',
        ];
        $now = $current ? Carbon::parse($current) : Carbon::now();
        $result = [];
        foreach ($formats as $format) {
            $result[$format] = $now->format($format);
        }
        return $result;
    }
}

if (!function_exists('getDateFormats')) {
    function getDateFormats($current = null) {
        $formats = [
            'd/m/Y',
            'm/d/Y',
            'Y-m-d',
            'd-m-Y',
            'M d Y',
            'd M Y',
            'j/n/Y',
        ];
        $now = $current ? Carbon::parse($current) : Carbon::now();
        $result = [];
        foreach ($formats as $format) {
            $result[$format] = $now->format($format);
        }
        return $result;
    }
}

if (!function_exists('date_sql')) {
    function date_sql($data) {
        if ($data === '' || $data === null) return null;
        if ($data == -1) return -1;
        $formats = [
            get_option('format_date', 'd/m/Y'),
            'Y-m-d','d-m-Y','m/d/Y','d/m/Y','j/n/Y','M d Y','d M Y'
        ];
        foreach ($formats as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $data);
                if ($dt instanceof Carbon) {
                    return $dt->format('Y-m-d');
                }
            } catch (\Exception $e) { continue; }
        }
        try {
            $dt = Carbon::parse($data);
            return $dt->format('Y-m-d');
        } catch (\Exception $e) { return null; }
    }
}

if (!function_exists('datetime_sql')) {
    function datetime_sql($data) {
        if ($data === '' || $data === null) return null;
        if ($data == -1) return -1;
        $formats = [
            get_option('format_datetime', 'd/m/Y H:i'),
            'Y-m-d H:i',
            'd/m/Y H:i',
            'm/d/Y h:i A',
            'd-m-Y H:i',
            'M d Y H:i',
            'd M Y H:i',
            'j/n/Y H:i',
            'd/m/Y','d-m-Y','Y-m-d'
        ];
        foreach ($formats as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $data);
                if ($dt instanceof Carbon) {
                    return $dt->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) { continue; }
        }
        try {
            $dt = Carbon::parse($data);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Exception $e) { return null; }
    }
}

if (!function_exists('timestamp_sql')) {
    function timestamp_sql($data) {
        if ($data === '' || $data === null) return null;
        $format = get_option('format_datetime', 'd/m/Y H:i');
        if (in_array($format, ['d/m/Y H:i', 'd/m/Y g:i A', 'd/m/Y'])) {
            $dataFixed = str_replace("/", "-", $data);
            $ts = strtotime($dataFixed);
            if ($ts !== false) return $ts;
        }
        $formats = [
            $format,
            'Y-m-d H:i','d/m/Y H:i','m/d/Y h:i A','d-m-Y H:i',
            'M d Y H:i','d M Y H:i','j/n/Y H:i',
            'd/m/Y h:i A','d-m-Y h:i A','Y-m-d h:i A','m/d/Y H:i',
            'd/m/Y','d-m-Y','Y-m-d'
        ];
        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $data);
                if ($dt instanceof Carbon) {
                    return $dt->timestamp;
                }
            } catch (\Exception $e) { continue; }
        }
        try {
            $dt = Carbon::parse($data);
            return $dt->timestamp;
        } catch (\Exception $e) { return null; }
    }
}

if (!function_exists('php_to_js_date_format')) {
    function php_to_js_date_format($phpFormat, $type = 'date') {
        $replace = [
            'd' => 'dd',
            'j' => 'd',
            'm' => 'mm',
            'n' => 'm',
            'Y' => 'yy',
            'y' => 'y',
            'M' => 'M',
            'F' => 'MM',
        ];
        if ($type === 'time') {
            $replace = [
                'H' => 'HH',
                'h' => 'hh',
                'g' => 'h',
                'i' => 'mm',
                's' => 'ss',
                'A' => 'TT',
                'a' => 'tt',
            ];
        }
        $jsFormat = $phpFormat;
        foreach ($replace as $php => $js) {
            $jsFormat = preg_replace('/(?<![a-zA-Z])' . preg_quote($php, '/') . '(?![a-zA-Z])/', $js, $jsFormat);
        }
        return $jsFormat;
    }
}

if (!function_exists('dateFormatJs')) {
    function dateFormatJs() {
        $format = get_option('format_date', 'd/m/Y');
        return php_to_js_date_format($format, 'date');
    }
}

if (!function_exists('dateTimeFormatJs')) {
    function dateTimeFormatJs() {
        $format = get_option('format_datetime', 'd/m/Y H:i');
        if (preg_match('/^(.*?)[,\s]+([HhgisA:\s]+)$/', $format, $matches)) {
            $datePart = trim($matches[1]);
            $timePart = trim($matches[2]);
        } else {
            $parts = preg_split('/\s+/', $format, 2);
            $datePart = $parts[0];
            $timePart = $parts[1] ?? '';
        }
        return [
            php_to_js_date_format($datePart, 'date'),
            $timePart ? php_to_js_date_format($timePart, 'time') : '',
        ];
    }
}

if (!function_exists('dateTimeFormatsJs')) {
    function dateTimeFormatsJs() {
        $formats = [
            'd/m/Y H:i','m/d/Y h:i A','Y-m-d H:i','d-m-Y H:i','M d Y H:i','d M Y H:i','j/n/Y H:i'
        ];
        $result = [];
        foreach ($formats as $format) {
            $parts = preg_split('/\s+/', $format, 2);
            $dateFormat = php_to_js_date_format($parts[0]);
            $timeFormat = isset($parts[1]) ? php_to_js_date_format($parts[1], 'time') : '';
            $result[$format] = [$dateFormat, $timeFormat];
        }
        return $result;
    }
}

if (!function_exists('dateFormatsJs')) {
    function dateFormatsJs() {
        $formats = [
            'd/m/Y','m/d/Y','Y-m-d','d-m-Y','M d Y','d M Y','j/n/Y'
        ];
        $result = [];
        foreach ($formats as $format) {
            $result[$format] = php_to_js_date_format($format);
        }
        return $result;
    }
}

if (!function_exists('date_show')) {
    function date_show($data) {
        if (empty($data)) return '';

        if (!($data instanceof \Carbon\Carbon) && $data == -1) return -1;

        try {
            $user = auth()->user();
            $tz = ($user && !empty($user->timezone) && in_array($user->timezone, timezone_identifiers_list()))
                ? $user->timezone
                : (in_array(config('app.timezone'), timezone_identifiers_list()) ? config('app.timezone') : 'UTC');

            $dt = is_numeric($data)
                ? Carbon::createFromTimestamp($data, $tz)
                : ($data instanceof \Carbon\Carbon ? $data->copy()->setTimezone($tz) : Carbon::parse($data, $tz));

            $format = get_option('format_date', 'd/m/Y');
            return $dt->format($format);
        } catch (\Exception $e) {
            return '';
        }
    }
}

if (!function_exists('datetime_show')) {
    function datetime_show($data) {
        if (empty($data)) return '';

        if (!($data instanceof \Carbon\Carbon) && $data == -1) return -1;

        try {
            $user = auth()->user();
            $tz = ($user && !empty($user->timezone) && in_array($user->timezone, timezone_identifiers_list()))
                ? $user->timezone
                : (in_array(config('app.timezone'), timezone_identifiers_list()) ? config('app.timezone') : 'UTC');

            $dt = is_numeric($data)
                ? Carbon::createFromTimestamp($data, $tz)
                : ($data instanceof \Carbon\Carbon ? $data->copy()->setTimezone($tz) : Carbon::parse($data, $tz));

            $format = get_option('format_datetime', 'd/m/Y H:i');
            return $dt->format($format);
        } catch (\Exception $e) {
            return '';
        }
    }
}