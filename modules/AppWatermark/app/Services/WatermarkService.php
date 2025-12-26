<?php
namespace Modules\AppWatermark\Services;

use Intervention\Image\Facades\Image;
use Modules\AppChannels\Models\Accounts;
use Modules\AdminUsers\Facades\UserInfo;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\PngEncoder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WatermarkService
{

    public function __construct()
    {
        $this->image = ImageManager::gd(); 
    }

    public function applyAuto($imagePath, $accountId, $teamId = null, $saveTo = null)
    {
        $wmData = \Channels::getDataAccount($accountId, 'watermark', []);
        $mark = $wmData['mark'] ?? null;

        if (empty($mark)) {
            if (!$teamId && $accountId) {
                $account = Accounts::find($accountId);
                $teamId = $account->team_id ?? null;
            }
            if ($teamId) {
                $wmData = UserInfo::getDataTeam('watermark', [], $teamId);
                $mark = $wmData['mark'] ?? null;
            }
        }

        if (empty($mark)) {
            return asset($imagePath); 
        }

        $options = [
            'position' => $wmData['position'] ?? 'rb',
            'size'     => $wmData['size']     ?? 30,
            'opacity'  => $wmData['opacity']  ?? 30,
        ];

        if (!$saveTo) {
            $saveTo = 'watermarked/'.md5($imagePath . $accountId . serialize($options)).'.png';
            $saveTo = storage_path('app/public/'.$saveTo);
        }

        $this->apply($imagePath, $mark, $options, $saveTo);
        return asset('storage/' . str_replace(storage_path('app/public/'), '', $saveTo));
    }

    /**
     * Apply watermark and save to storage/watermarked/
     *
     * @param string $originalPath Absolute path to original image
     * @param string $markPath     Absolute path to watermark image
     * @param array $options       ['position' => 'rb', 'size' => 30, 'opacity' => 30]
     * @return string URL to watermarked image
     */
    public function apply(string $originalPath, string $markPath, array $options = []): string
    {
        $position = $options['position'] ?? 'rb';
        $size     = $options['size'] ?? 30;

        $imageManager = ImageManager::gd();
        $image     = $imageManager->read($originalPath);
        $watermark = $imageManager->read(\Media::path($markPath));

        $scale = max(min($size, 100), 1) / 100;
        $w = intval($image->width() * $scale);
        $h = intval($watermark->height() * $w / $watermark->width());
        $watermark->resize($w, $h);

        $pos = $this->convertPosition($position, $image->width(), $image->height(), $w, $h);
        $image->place($watermark, 'top-left', $pos['x'], $pos['y']);

        $filename = \Str::uuid() . '.png';
        $relativePath = 'watermarked/' . $filename;
        $fullPath = storage_path('app/public/' . $relativePath);

        \Storage::disk('public')->makeDirectory('watermarked');

        // Use positional parameters for compatibility
        $image->encode(new PngEncoder(90, false))->save($fullPath);

        return  \Media::url($relativePath);
    }

    protected function convertPosition($position, $imgW, $imgH, $wmW, $wmH): array
    {
        return match ($position) {
            'lt' => ['x' => 0, 'y' => 0],
            'ct' => ['x' => intval(($imgW - $wmW) / 2), 'y' => 0],
            'rt' => ['x' => $imgW - $wmW, 'y' => 0],
            'lc' => ['x' => 0, 'y' => intval(($imgH - $wmH) / 2)],
            'cc' => ['x' => intval(($imgW - $wmW) / 2), 'y' => intval(($imgH - $wmH) / 2)],
            'rc' => ['x' => $imgW - $wmW, 'y' => intval(($imgH - $wmH) / 2)],
            'lb' => ['x' => 0, 'y' => $imgH - $wmH],
            'cb' => ['x' => intval(($imgW - $wmW) / 2), 'y' => $imgH - $wmH],
            default => ['x' => $imgW - $wmW, 'y' => $imgH - $wmH],
        };
    }

    public function createWatermarkedList(array $mediaUrls, $accountId): array
    {
        $results = [];

        foreach ($mediaUrls as $url) {
            $results[] = $this->createWatermarkedUrl($url, $accountId);
        }

        return $results;
    }

    public function createWatermarkedUrl(string $imageUrl, $accountId): string
    {
        $imageUrl = \Media::url($imageUrl);

        // Skip if not a valid image format
        if (!$this->isValidImageUrl($imageUrl)) {
            return $imageUrl;
        }

        // Get watermark config from account
        $wmData = \Channels::getDataAccount($accountId, 'watermark', []);
        $mark = $wmData['mark'] ?? null;

        // If not found, fallback to team-level watermark config
        if (empty($mark)) {
            $account = \Modules\AppChannels\Models\Accounts::find($accountId);
            $teamId = $account->team_id ?? null;

            if ($teamId) {
                $wmData = \Modules\AdminUsers\Facades\UserInfo::getDataTeam('watermark', [], $teamId);
                $mark = $wmData['mark'] ?? null;
            }
        }

        // If no watermark found, return the original image URL
        if (empty($mark)) {
            return $imageUrl;
        }

        // Default watermark options
        $options = [
            'position' => $wmData['position'] ?? 'rb',
            'size'     => $wmData['size'] ?? 30,
            'opacity'  => $wmData['opacity'] ?? 30,
        ];

        // Ensure the watermark directory exists
        \Storage::disk('public')->makeDirectory('watermarked');

        // Download and store the original image temporarily in storage
        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = uniqid('img_') . '.' . $extension;
        $relativePath = 'watermarked/' . $filename;
        $storagePath = storage_path('app/public/' . $relativePath);

        file_put_contents($storagePath, file_get_contents($imageUrl));

        // Apply watermark and get the final public URL
        $resultUrl = $this->apply($storagePath, $mark, $options);

        // Optionally delete the original file after processing
        @unlink($storagePath);

        return $resultUrl;
    }


    protected function isValidImageUrl(string $url): bool
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }

        $headers = @get_headers($url, 1);
        return is_array($headers) && strpos($headers[0], '200') !== false;
    }

    public function delete($filePath)
    {
        \UploadFile::deleteFileFromServer($filePath);
        return true;
    }
}