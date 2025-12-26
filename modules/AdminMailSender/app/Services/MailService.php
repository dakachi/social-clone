<?php
namespace Modules\AdminMailSender\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use App\Models\User;

class MailService
{
    protected static $templates = [];

    public static function sendByTemplate($templateId, $to, $data = [], $subject = null, $options = [])
    {
        $template = static::getTemplateById($templateId);
        if (!$template) {
            throw new \Exception("Email template [$templateId] not found.");
        }

        $module = strtolower($template['module']);
        $view = str_replace("/", ".", $template['view']);

        $content = view($module."::".$view, $data)->render();

        if (empty($data['theme'])) {
            $themeSlug = (new static)->getThemeSlug();
            $data['theme'] = $themeSlug;
            $themeLayout = 'adminmailthemes::themes.' . $themeSlug . '.layout';

            $body = view($themeLayout, [
                'subject' => $subject,
                'content' => $content,
            ])->render();
        }else{
            $body = $content;
        }
        
        $mailSubject = $subject ?? ($template['name'] ?? 'Notification');

        return static::send($to, $mailSubject, $body, $options);
    }

    public static function sendMail($toOrUserId, $subject, $html, $variables = [], $options = [], $withTheme = true)
    {
        $to = $toOrUserId;

        if (is_numeric($toOrUserId)) {
            $variables = static::getVariables($toOrUserId);
            if (empty($variables['email'])) {
                return false;
            }
            $to = $variables['email'];
        }

        if (!empty($variables)) {
            $html = static::replaceVariables($html, $variables);
        }

        if ($withTheme) {
            $themeSlug = (new static)->getThemeSlug();
            $themeLayout = 'adminmailthemes::themes.' . $themeSlug . '.layout';
            $body = view($themeLayout, [
                'subject' => $subject,
                'content' => $html,
            ])->render();
        } else {
            $body = $html;
        }

        return static::send($to, $subject, $body, $options);
    }

    public static function send($to, $subject, $body, $options = [])
    {
        try {
            \Mail::send([], [], function ($message) use ($to, $subject, $body, $options) {
                $message->to($to)
                    ->subject($subject)
                    ->html($body);

                if (!empty($options['cc'])) $message->cc($options['cc']);
                if (!empty($options['bcc'])) $message->bcc($options['bcc']);
                if (!empty($options['attachments'])) {
                    foreach ($options['attachments'] as $file) {
                        $message->attach($file);
                    }
                }
            });

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function sendTestMail($to)
    {
        $subject = __('Test Email');
        $body = '<p>' . __('This is a test email.') . '</p>';

        return static::send($to, $subject, $body);
    }

    public static function getTemplateById($templateId)
    {
        foreach (self::$templates as $moduleTemplates) {
            foreach ($moduleTemplates as $template) {
                if (isset($template['id']) && $template['id'] == $templateId) {
                    return $template;
                }
            }
        }
        return null;
    }

    public static function replaceVariables($html, $variables = [])
    {
        return preg_replace_callback('/\[(\w+)\]/', function ($matches) use ($variables) {
            $key = $matches[1];
            return isset($variables[$key]) ? $variables[$key] : $matches[0];
        }, $html);
    }

    public static function getVariables($userId)
    {
        $user = User::with('plan')->find($userId);
        if (!$user) return [];

        $plan = $user->plan ?? null;

        return [
            'username'        => $user->username ?? '',
            'fullname'        => $user->fullname ?? '',
            'email'           => $user->email ?? '',
            'avatar'          => $user->avatar ?? '',
            'expiration_date' => $user->expiration_date ?? '',
            'plan_name'       => $plan->name ?? '',
            'plan_desc'       => $plan->desc ?? '',
            'plan_price'      => $plan->price ?? '',
            'plan_type'       => $plan->type ?? '',
            'plan_trial_day'  => $plan->trial_day ?? '',
        ];
    }

    public function getMailVariables($params = [])
    {
        $vars = [];

        // ==== USER ====
        if (!empty($params['user'])) {
            $user = $params['user'];
            $vars['fullname'] = $user->name ?? $user['name'] ?? '';
            $vars['email'] = $user->email ?? $user['email'] ?? '';
            $vars['first_name'] = $user->first_name ?? $user['first_name'] ?? '';
            $vars['last_name'] = $user->last_name ?? $user['last_name'] ?? '';
            $vars['username'] = $user->username ?? $user['username'] ?? '';
            $vars['phone'] = $user->phone ?? $user['phone'] ?? '';
            $vars['address'] = $user->address ?? $user['address'] ?? '';
            $vars['avatar_url'] = $user->avatar_url ?? $user['avatar_url'] ?? '';
            $vars['user_id'] = $user->id ?? $user['id'] ?? '';
        }

        // ==== TEAM ====
        if (!empty($params['team'])) {
            $team = $params['team'];
            $vars['team_name'] = $team->name ?? $team['name'] ?? '';
            $vars['team_id'] = $team->id ?? $team['id'] ?? '';
            $vars['team_role'] = $params['team_role'] ?? '';
        }

        // ==== PLAN ====
        if (!empty($params['plan'])) {
            $plan = $params['plan'];
            $vars['plan_name'] = $plan->name ?? $plan['name'] ?? '';
            $vars['plan_id'] = $plan->id ?? $plan['id'] ?? '';
            $vars['plan_price'] = $plan->price ?? $plan['price'] ?? '';
            $vars['plan_currency'] = $plan->currency ?? $plan['currency'] ?? '';
            $vars['plan_duration'] = $plan->duration ?? $plan['duration'] ?? '';
            $vars['plan_type'] = $plan->type ?? $plan['type'] ?? '';
            $vars['plan_start_date'] = $plan->start_date ?? $plan['start_date'] ?? '';
            $vars['plan_expiration_date'] = $plan->expiration_date ?? $plan['expiration_date'] ?? '';
        }

        // ==== ORDER / INVOICE ====
        if (!empty($params['order'])) {
            $order = $params['order'];
            $vars['order_id'] = $order->id ?? $order['id'] ?? '';
            $vars['order_amount'] = $order->amount ?? $order['amount'] ?? '';
            $vars['order_currency'] = $order->currency ?? $order['currency'] ?? '';
            $vars['order_status'] = $order->status ?? $order['status'] ?? '';
            $vars['order_date'] = $order->created_at ?? $order['created_at'] ?? '';
            $vars['order_payment_method'] = $order->payment_method ?? $order['payment_method'] ?? '';
            $vars['invoice_url'] = $order->invoice_url ?? $order['invoice_url'] ?? '';
        }

        // ==== SYSTEM / APP ====
        $vars['site_name'] = config('app.name');
        $vars['site_url'] = config('app.url');
        $vars['support_email'] = config('mail.from.address');
        $vars['support_phone'] = config('app.support_phone', '');

        // ==== ACTION LINK (FORGOT, VERIFY, ... ) ====
        if (!empty($params['verify_url'])) $vars['verify_url'] = $params['verify_url'];
        if (!empty($params['reset_url'])) $vars['reset_url'] = $params['reset_url'];
        if (!empty($params['login_url'])) $vars['login_url'] = $params['login_url'];

        // ==== AFFILIATE ====
        if (!empty($params['affiliate'])) {
            $affiliate = $params['affiliate'];
            $vars['affiliate_code'] = $affiliate->code ?? $affiliate['code'] ?? '';
            $vars['affiliate_link'] = $affiliate->link ?? $affiliate['link'] ?? '';
            $vars['affiliate_balance'] = $affiliate->balance ?? $affiliate['balance'] ?? '';
        }

        // ==== CUSTOM FIELDS ====
        if (!empty($params['custom']) && is_array($params['custom'])) {
            foreach ($params['custom'] as $k => $v) {
                $vars[$k] = $v;
            }
        }

        return $vars;
    }

    /**
     * Register one or multiple email templates for a module.
     *
     * @param string $moduleId     The module ID (e.g., 'AdminUsers')
     * @param array $templates     A template array or an array of templates
     * @return void
     */
    public static function addTemplate($moduleId, $templates)
    {
        // Ensure an array exists for the module
        if (!isset(self::$templates[$moduleId])) {
            self::$templates[$moduleId] = [];
        }

        // If a single template is passed, wrap it in an array for unified handling
        if (!isset($templates[0])) {
            $templates = [$templates];
        }

        foreach ($templates as $template) {
            // Prevent duplicate views within the same module
            $exists = false;
            foreach (self::$templates[$moduleId] as $tpl) {
                if (isset($tpl['view']) && $tpl['view'] === $template['view']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                self::$templates[$moduleId][] = $template;
            }
        }
    }

    /**
     * Get all registered templates, or templates for a specific module.
     *
     * @param string|null $moduleId
     * @return array
     */
    public static function getTemplates($moduleId = null)
    {
        if ($moduleId) return self::$templates[$moduleId] ?? [];
        return self::$templates;
    }

    public function getThemeSlug()
    {
        return get_option('mail_themes', 'modern-pro');
    }

    public function getThemesPath()
    {
        $module = \Module::find('AdminMailThemes');
        $theme_path = $module->getPath();
        return $theme_path . '/resources/views/themes';
    }

    public function getTheme()
    {
        $slug = $this->getThemeSlug();
        $themesPath = $this->getThemesPath();
        $themePath = $themesPath . '/' . $slug;
        $info = [];

        $jsonPath = $themePath . '/theme.json';
        if (File::exists($jsonPath)) {
            $info = json_decode(File::get($jsonPath), true);
        }

        return [
            'slug'      => $slug,
            'info'      => $info,
            'path'      => $themePath,
            'json'      => $jsonPath,
        ];
    }
}