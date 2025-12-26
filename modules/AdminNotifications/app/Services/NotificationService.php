<?php

namespace Modules\AdminNotifications\Services;

use Modules\AdminNotifications\Models\Notification;
use Modules\AdminNotifications\Models\NotificationManual;

class NotificationService
{
    public function sendAuto(int $userId, string $message, ?string $url = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'source'  => 'auto',
            'message' => $message,
            'url'     => $url,
            'type'    => 'news',
        ]);
    }

    public function sendManual(array $userIds, string $title, string $message, ?string $url = null, ?int $adminId = null): NotificationManual
    {
        $manual = NotificationManual::create([
            'title'      => $title,
            'message'    => $message,
            'url'        => $url,
            'type'       => 'news',
            'created_by' => $adminId ?? (auth('admin')->check() ? auth('admin')->id() : null),
        ]);

        $notifications = [];

        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'source'  => 'manual',
                'mid'     => $manual->id,
                'url'     => $url,
                'type'    => 'news',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Notification::insert($notifications);

        return $manual;
    }

    public function getLatest(int $userId, int $limit = 20)
    {
        return Notification::with('manual')
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->where('source', 'auto')
                  ->orWhere(function ($q2) {
                      $q2->where('source', 'manual')
                         ->whereHas('manual');
                  });
            })
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function markAsRead(int $userId, int $id): bool
    {
        return Notification::where('user_id', $userId)
            ->where('id', $id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]) > 0;
    }

    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function archiveAll(int $userId): int
    {
        return Notification::where('user_id', $userId)->delete();
    }

    public function countUnread(int $userId): int
    {
        return Notification::where('user_id', $userId)->whereNull('read_at')->count();
    }
}
