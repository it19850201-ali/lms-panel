<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MoodleClient
{
    protected string $baseUrl;
    protected string $token;

    public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        $this->baseUrl = $baseUrl ?? config('services.moodle.url');
        $this->token = $token ?? config('services.moodle.token');
    }

    protected function call(string $function, array $params = []): array
{
    $response = Http::asForm()->post("{$this->baseUrl}/webservice/rest/server.php", array_merge([
        'wstoken' => $this->token,
        'moodlewsrestformat' => 'json',
        'wsfunction' => $function,
    ], $params));

    $data = $response->json();

    if ($data === null) {
        return [];
    }

    if (isset($data['exception'])) {
        $debug = $data['debuginfo'] ?? '';
        throw new \RuntimeException("Moodle API error: {$data['message']} | Debug: {$debug}");
    }

    return $data;
}
    public function createCourse(string $fullName, string $shortName, int $categoryId = 1): array
    {
        return $this->call('core_course_create_courses', [
            'courses' => [[
                'fullname'  => $fullName,
                'shortname' => $shortName,
                'categoryid' => $categoryId,
            ]],
        ]);
    }

    public function createUser(string $username, string $email, string $firstName, string $lastName, string $password): array
    {
        return $this->call('core_user_create_users', [
            'users' => [[
                'username'  => $username,
                'password'  => $password,
                'firstname' => $firstName,
                'lastname'  => $lastName,
                'email'     => $email,
            ]],
        ]);
    }
    public function getUserByEmail(string $email): ?array
{
    $result = $this->call('core_user_get_users', [
        'criteria' => [
            ['key' => 'email', 'value' => $email],
        ],
    ]);

    return $result['users'][0] ?? null;
}

public function createOrGetUser(string $email, string $firstName, string $lastName): array
{
    $existing = $this->getUserByEmail($email);
    if ($existing) {
        return $existing;
    }

    $username = 'std_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $email)) . '_' . substr(md5($email), 0, 4);

    $result = $this->createUser(
        $username,
        $email,
        $firstName,
        $lastName,
        'Temp' . rand(1000, 9999) . '!'
    );

    return ['id' => $result[0]['id']];
}
    public function updateCourse(int $courseId, string $fullName): array
    {
        return $this->call('core_course_update_courses', [
            'courses' => [[
                'id' => $courseId,
                'fullname' => $fullName,
            ]],
        ]);
    }

    public function deleteCourse(int $courseId): array
    {
        return $this->call('core_course_delete_courses', [
            'courseids' => [$courseId],
        ]);
    }
    public function enrolUser(int $userId, int $courseId, int $roleId = 5): array
    {
        return $this->call('enrol_manual_enrol_users', [
            'enrolments' => [[
                'roleid'   => $roleId,
                'userid'   => $userId,
                'courseid' => $courseId,
            ]],
        ]);
    }
    /**
     * ساخت لینک SSO یکبارمصرف برای ورود خودکار به Moodle
     */
    public function generateSsoLoginUrl(string $email, ?int $courseId = null): ?string
    {
        $ssoToken = config('services.moodle.sso_token');

        if (!$ssoToken) {
            \Log::error('Moodle SSO token is not configured');
            return null;
        }

        // فقط user رو می‌فرستیم (چون WS function چیز دیگه‌ای قبول نمی‌کنه)
        $response = Http::asForm()->post("{$this->baseUrl}/webservice/rest/server.php", [
            'wstoken' => $ssoToken,
            'moodlewsrestformat' => 'json',
            'wsfunction' => 'auth_userkey_request_login_url',
            'user[email]' => $email,
        ]);

        $data = $response->json();

        if (isset($data['exception'])) {
            \Log::error('Moodle SSO error: ' . ($data['message'] ?? 'unknown'), ['data' => $data]);
            return null;
        }

        $loginUrl = $data['loginurl'] ?? null;

        if (!$loginUrl) {
            return null;
        }

        // اضافه کردن wantsurl به لینک برگشتی
        if ($courseId) {
            $wantsurl = '/course/view.php?id=' . $courseId;
            $separator = str_contains($loginUrl, '?') ? '&' : '?';
            $loginUrl .= $separator . 'wantsurl=' . urlencode($wantsurl);
        }

        return $loginUrl;
    }

}
