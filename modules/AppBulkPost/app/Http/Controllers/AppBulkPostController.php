<?php

namespace Modules\AppBulkPost\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use League\Csv\Reader;
use Publishing;
use Channels;
use Media;
use URLShortener;

class AppBulkPostController extends Controller
{

    public function index()
    {
        return view('appbulkpost::index');
    }

    /**
     * Download the given file.
     *
     * @param string $filename The name of the file to download.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download_template()
    {
        $module = \Module::find('AppBulkPost');
        $filePath = $module->getExtraPath('resources/assets/bulk_template.csv');

        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->download($filePath);
    }

    /**
     * Save bulk posting data from CSV.
     *
     * Retrieves input from the request, validates required fields,
     * processes a CSV file from a URL or local path,
     * creates post data based on CSV rows and accounts, validates each post,
     * and finally inserts the valid posts into the database.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $dataError    = [];
        $dataSuccess  = [];
        $postErrors   = 0;
        $postSuccess  = 0;

        // Get team and input values.
        $teamId         = $request->team_id;
        $accounts       = $request->input('accounts');
        $csv_file       = $request->input('file');
        $campaign       = (int)$request->input('campaign')?? 0;
        $labels         = $request->input('campaign') ?? [];
        $csv_file       = $request->input('file');
        $delay          = (int)$request->input('delay', 0);
        $options        = $request->input('options');
        $url_shorten    = (int)$request->input('url_shorten');


        // Update team delay setting.
        //update_team_data("bulk_delay", $delay);

        // Validate required input.
        if (empty($accounts)) {
            return response()->json([
                'status'  => 'error',
                'message' => __('Please select at least a profile'),
            ]);
        }

        $listAccounts = Channels::list($accounts, [
            "status" => 1
        ]);

        if (empty($listAccounts)) {
            return response()->json([
                'status'  => 'error',
                'message' => __('The accounts you selected are paused or inactive. Please log in again and try.'),
            ]);
        }

        if (empty($csv_file)) {
            return response()->json([
                "status"  => "error",
                "message" => __('Please select a bulk template CSV file for upload.')
            ]);
        }

        // Process CSV file – we use the first media file.
        $csv = $csv_file;
        $headers = get_header(Media::url($csv));
        $headers = array_change_key_case($headers, CASE_LOWER);
        if (!isset($headers['content-type'])) {
            return response()->json([
                "status"  => "error",
                "message" => __("The system was unable to determine the file type.")
            ]);
        }

        if ($headers['content-type'] != "text/csv" && $headers['content-type'] != "application/octet-stream" && $headers['content-type'] != "text/plain") {
            return response()->json([
                "status"  => "error",
                "message" => __("Please select a bulk template CSV file for upload.")
            ]);
        }

        // Read the CSV content into a string
        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ]);
        $csvContent = file_get_contents($csv, false, $context);
        if ($csvContent === false) {
            return response()->json([
                "status"  => "error",
                "message" => __("Unable to retrieve CSV file content.")
            ]);
        }
        
        // Create a temporary stream that is seekable
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csvContent);
        rewind($stream);
        
        $csvReader = Reader::createFromStream($stream, 'r');
        $csvRows = iterator_to_array($csvReader->getIterator());
        $countDelay = 0;

        foreach ($csvRows as $rowIndex => $row) {
            // Skip header row or rows that do not have exactly 8 columns.
            if ($rowIndex === 0 || count($row) !== 8) {
                continue;
            }
            
            // Unpack values from the row.
            list($caption, $year, $month, $day, $hour, $minute, $media, $link) = $row;
            $caption = trim($caption);
            
            // Format date components to ensure two-digit formatting.
            $month   = sprintf("%02d", $month);
            $day     = sprintf("%02d", $day);
            $hour    = sprintf("%02d", $hour);
            $minute  = sprintf("%02d", $minute);
            
            // Create a datetime string from the CSV row.
            $dateStr = "{$year}-{$month}-{$day} {$hour}:{$minute}:00";
            $timestampDate = strtotime($dateStr);
            
            // Determine the scheduled posting time.
            if ($timestampDate > time()) {
                $timePost = $timestampDate;
            } else {
                $timePost = time() + $delay * $countDelay * 60;
                $countDelay++;
            }
            
            // Optionally check if the link is a valid URL (not used further, so kept only for reference)
            $checkLink = filter_var($link, FILTER_VALIDATE_URL) ? true : false;
            
            // Determine post type based on provided media and link.
            $type = "text";
            if (!empty($media)) {
                $type = "media";
            } elseif (!empty($link)) {
                $type = "link";
            }
            
            // Prepare the post data payload.
            $postData = [
                "caption"         => $caption,
                "link"            => $checkLink?$link:"",
                "medias"          => (!empty($media)) ? [$media] : null,
                "options"         => $options,
            ];

            // Build a base data template (common for all accounts).
            $dataTemplateBase = [
                "team_id"          => $teamId,
                "function"         => "post",
                "type"             => $type,
                "data"             => json_encode($postData),
                "time_post"        => $timePost,
                "delay"            => $delay,
                "repost_frequency" => 0,
                "repost_until"     => null,
                "result"           => "",
                "changed"          => time(),
                "created"          => time(),
            ];

            // For every account in the list, prepare and validate individual post entries.
            foreach ($listAccounts as $account) {
                // Use the incoming 'ids' field from the request or generate one via a helper.
                $id_secure = rand_string();
                $dataTemplate = $dataTemplateBase;
                $dataTemplate['id_secure']      = $id_secure;
                $dataTemplate['account_id']     = $account->id;
                $dataTemplate['social_network'] = $account->social_network;
                $dataTemplate['category']       = $account->category;
                $dataTemplate['api_type']       = $account->login_type;
                $dataTemplate['module']         = $account->module;
                $dataTemplate['campaign']       = $campaign;
                $dataTemplate['labels']         = json_encode($labels);
                $dataTemplate['account']        = $account; // Temporary for validation

                // Validate the post data using the post model's validator.
                $validator = Publishing::validate([(object)$dataTemplate]);
                if ($validator['status'] === "error") {
                    $postErrors++;
                    $dataTemplate['status'] = 5;
                    $dataTemplate['result'] = $validator['message'];
                    $dataError[] = (object)$dataTemplate;
                } else {
                    $postSuccess++;
                    $dataTemplate['status'] = 3;
                    // Remove the temporary 'account' key before insertion.
                    unset($dataTemplate['account']);
                    $dataSuccess[] = (object)$dataTemplate;
                }
            }
        }

        if (!empty($dataSuccess)) {
            foreach ($dataSuccess as $key => $value) {
                if($url_shorten && get_option("url_shorteners_platform", 0)){
                    $postData = json_decode($value->data, true);
                    $postData['link'] = URLShortener::shorten($link);
                    $postData['caption'] = URLShortener::shortenUrlsInContent($caption);
                    $dataSuccess[$key]->data = json_encode($postData);
                }
            }

            Publishing::post($dataSuccess);
        }

        $message = __("You're scheduling :posts posts to :accounts social accounts.", [
            'posts' => $postSuccess,
            'accounts' => count($listAccounts)
        ]);

        return response()->json([
            "status"  => ($postSuccess !== 0) ? 1 : 0,
            "message" => $message
        ]);
    }

}
