<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BBBClient
{
    protected string $baseUrl;
    protected string $secret;

    public function __construct()
    {
        $this->baseUrl = config('services.bbb.url');
        $this->secret = config('services.bbb.secret');
    }

    protected function checksum(string $callName, string $queryString): string
    {
        return sha1($callName . $queryString . $this->secret);
    }

    /**
     * ساخت جلسه با محدودیت‌های پلن
     *
     * options:
     *   - maxParticipants (int)
     *   - maxCameras (int) → 0 = فقط مدیر، -1 = نامحدود، >0 = محدود
     */
    public function createMeeting(string $meetingId, string $meetingName, array $options = []): array
    {
        $maxParticipants = $options['maxParticipants'] ?? 100;
        $maxCameras = $options['maxCameras'] ?? 5;  // پیش‌فرض ۵ دوربین

        $params = [
            'meetingID' => $meetingId,
            'name' => $meetingName,
            'record' => 'false',
            'duration' => '0',
            'maxParticipants' => $maxParticipants,
            'autoStartRecording' => 'false',
            'allowStartStopRecording' => 'false',
            'endWhenNoModerator' => 'false',
            'endWhenNoModeratorDelayInMinutes' => '0',
            'meetingExpireIfNoUserJoinedInMinutes' => '525600',
            'meetingExpireWhenLastUserLeftInMinutes' => '525600',
            'muteOnStart' => 'false',
            'welcome' => 'به کلاس خوش آمدید',
        ];

        // ⭐ پارامتر کلیدی: محدودیت دقیق تعداد دوربین
        if ($maxCameras > 0) {
            $params['meetingCameraCap'] = $maxCameras;
        }

        $query = http_build_query($params);
        $checksum = $this->checksum('create', $query);

        $url = "{$this->baseUrl}api/create?{$query}&checksum={$checksum}";
        Log::info('BBB Create URL: ' . $url);

        $response = Http::timeout(30)->connectTimeout(15)->get($url);
        Log::info('BBB Create Response: ' . $response->body());

        return (array) simplexml_load_string($response->body());
    }
    public function getJoinUrl(string $meetingId, string $fullName, string $role = 'MODERATOR', ?string $bbbUserId = null): string
    {
        $params = [
            'meetingID' => $meetingId,
            'fullName'  => $fullName,
            'role'      => $role,
        ];

        $params['userID'] = $bbbUserId ?: md5($meetingId . $fullName . $role . microtime());

        $query = http_build_query($params);
        $checksum = $this->checksum('join', $query);

        return "{$this->baseUrl}api/join?" . $query . "&checksum={$checksum}";
    }

    public function endMeeting(string $meetingId): array
    {
        $params = ['meetingID' => $meetingId];
        $query = http_build_query($params);
        $checksum = $this->checksum('end', $query);

        $url = "{$this->baseUrl}api/end?{$query}&checksum={$checksum}";

        try {
            $response = Http::timeout(10)->connectTimeout(5)->get($url);
            return (array) simplexml_load_string($response->body());
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getMeetingInfo(string $meetingId): ?array
    {
        $params = ['meetingID' => $meetingId];
        $query = http_build_query($params);
        $checksum = $this->checksum('getMeetingInfo', $query);
        $url = "{$this->baseUrl}api/getMeetingInfo?{$query}&checksum={$checksum}";

        try {
            $response = Http::timeout(10)->connectTimeout(5)->get($url);
            $data = (array) simplexml_load_string($response->body());

            if (($data['returncode'] ?? '') !== 'SUCCESS') {
                return null;
            }

            return $data;
        } catch (\Throwable $e) {
            Log::warning("BBB getMeetingInfo failed for {$meetingId}: {$e->getMessage()}");
            return null;
        }
    }

    public function generateMeetingId(string $prefix, int $klassId, int $day, int $bellId): string
    {
        return sprintf(
            '%s-%d-%d-%d-%s',
            $prefix,
            $klassId,
            $day,
            $bellId,
            substr(md5(uniqid('', true)), 0, 8)
        );
    }
}
