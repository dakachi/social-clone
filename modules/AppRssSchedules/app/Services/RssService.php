<?php
namespace Modules\AppRssSchedules\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class RssService
{

    /**
     * Fetch and parse RSS/Atom feed, return all info.
     *
     * @param string $url
     * @param int $timeout
     * @return array
     * @throws Exception
     */
    public function fetchRSS(string $url, int $timeout = 10): array
    {
        try {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception(__('The RSS URL is invalid.'));
            }

            $response = Http::timeout($timeout)
                ->accept('application/rss+xml, application/xml, text/xml, */*')
                ->get($url);

            if (!$response->successful()) {
                throw new Exception(__('Failed to fetch RSS feed. (HTTP :status)', ['status' => $response->status()]));
            }

            $body = trim($response->body());
            if (!$body) {
                throw new Exception(__('The RSS feed is empty.'));
            }

            libxml_use_internal_errors(true);
            $rss = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($rss === false) {
                libxml_clear_errors();
                throw new Exception(__('Invalid RSS feed format.'));
            }

            // Feed metadata
            $feedTitle       = (string)($rss->channel->title ?? $rss->title ?? '');
            $feedDesc        = (string)($rss->channel->description ?? $rss->subtitle ?? '');
            $feedLink        = (string)($rss->channel->link ?? ($rss->link['href'] ?? ''));
            $feedLanguage    = (string)($rss->channel->language ?? '');
            $feedImage       = isset($rss->channel->image->url) ? (string)$rss->channel->image->url : '';
            $feedLastBuildStr= (string)($rss->channel->{'lastBuildDate'} ?? '');
            $feedLastBuild   = $feedLastBuildStr ? strtotime($feedLastBuildStr) : 0;
            $feedCopyright   = (string)($rss->channel->copyright ?? '');
            $feedGenerator   = (string)($rss->channel->generator ?? $rss->generator ?? '');

            // Parse items
            $items = [];
            if (isset($rss->channel->item)) {
                foreach ($rss->channel->item as $item) {
                    $items[] = $this->normalizeRssItem($item);
                }
            } elseif (isset($rss->entry)) {
                foreach ($rss->entry as $item) {
                    $items[] = $this->normalizeAtomItem($item);
                }
            }

            return [
                'feed_title'            => $feedTitle,
                'feed_description'      => $feedDesc,
                'feed_link'             => $feedLink,
                'feed_language'         => $feedLanguage,
                'feed_image'            => $feedImage,
                'feed_last_build_str'   => $feedLastBuildStr,
                'feed_last_build'       => $feedLastBuild,
                'feed_copyright'        => $feedCopyright,
                'feed_generator'        => $feedGenerator,
                'items'                 => $items
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function normalizeRssItem($item): array
    {
        // Lấy title với xử lý lỗi UTF-8
        $title = (string)($item->title ?? '');
        if (!mb_detect_encoding($title, 'UTF-8', true)) {
            $title = mb_convert_encoding($title, 'UTF-8', 'auto');
        }

        // Lấy mô tả gốc (để lấy ảnh)
        $descHtml = (string)($item->description ?? $item->summary ?? '');
        if (!mb_detect_encoding($descHtml, 'UTF-8', true)) {
            $descHtml = mb_convert_encoding($descHtml, 'UTF-8', 'auto');
        }

        // Lấy ảnh
        $image = '';
        if (!empty($item->enclosure) && !empty($item->enclosure['url'])) {
            $image = (string)$item->enclosure['url'];
        } elseif (!empty($item->{'media:content'}) && !empty($item->{'media:content'}['url'])) {
            $image = (string)$item->{'media:content'}['url'];
        }
        // Nếu chưa có, tìm ảnh trong mô tả HTML
        if (!$image && stripos($descHtml, '<img') !== false) {
            if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $descHtml, $m)) {
                $image = $m[1];
            }
        }

        return [
            'title'   => $title,
            'desc'    => $this->cleanDescription($descHtml),
            'created' => $this->parseDate($item->pubDate ?? $item->published ?? ''),
            'link'    => (string)($item->link ?? ''),
            'author'  => (string)($item->author ?? ($item->children('dc', true)->creator ?? '')),
            'image'   => $image,
        ];
    }

    private function normalizeAtomItem($item): array
    {
        // Lấy link
        $link = '';
        if (!empty($item->link)) {
            foreach ($item->link as $lnk) {
                $attrs = $lnk->attributes();
                if (empty($attrs['rel']) || $attrs['rel'] == 'alternate') {
                    $link = (string)$attrs['href'];
                    break;
                }
            }
        }
        // Lấy ảnh
        $image = '';
        if (!empty($item->children('media', true)->content)) {
            $image = (string)$item->children('media', true)->content->attributes()->url;
        }

        return [
            'title'   => (string)($item->title ?? ''),
            'desc'    => $this->cleanDescription($item->summary ?? $item->content ?? ''),
            'created' => $this->parseDate($item->published ?? $item->updated ?? ''),
            'link'    => $link,
            'author'  => (string)($item->author->name ?? ''),
            'image'   => $image,
        ];
    }

    private function parseDate($dateStr): int
    {
        if (empty($dateStr)) return 0;
        $timestamp = strtotime((string)$dateStr);
        return $timestamp ?: 0;
    }

    /**
     * Clean and normalize description content to plain text.
     *
     * @param string $desc
     * @return string
     */
    private function cleanDescription($desc)
    {
        // Remove all HTML tags (plain text only)
        $desc = trim(strip_tags((string)$desc));

        // Optional: Shorten to 300 chars for social usage
        if (mb_strlen($desc) > 300) {
            $desc = mb_substr($desc, 0, 297) . '...';
        }
        return $desc;
    }
}
