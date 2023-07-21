<?php

namespace App\TfaProvider;

use App\Entity\TfaProvider;
use App\Entity\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class Email implements ProviderInterface
{
    /**
     * @param User    $user
     * @param array   $config
     * @param Request $request
     *
     * @return array
     */
    public function trigger(User $user, array &$config, Request $request): array
    {
        // ToDo: Заменить на метод создания рандомного кода
        $code = '123456';

        $config['code'] = $code;
        $config['codeGenerated'] = time();
        $config['ip'] = $request->ip();

        // ToDo: Send mail to user

        return [];
    }

    /**
     * @param User  $user
     * @param array $config
     * @param array $triggerData
     * @param       $extraData
     *
     * @return View
     */
    public function render(User $user, array $config, array $triggerData, $extraData = []): View
    {
        $viewParams = [
            'user'      => $user,
            'config'    => $config,
            'extraData' => $extraData
        ];

        return view('account.tfa.render_email', $viewParams);
    }

    /**
     * Проверка кода на валидность.
     * Код действует 15 минут, это значение прописано в коде ниже
     *
     * @param User    $user
     * @param array   $config
     * @param Request $request
     *
     * @return bool
     */
    public function verify(User $user, array &$config, Request $request): bool
    {
        if (empty($config['code']) || empty($config['codeGenerated']))
        {
            return false;
        }

        if (time() - $config['codeGenerated'] > 900)
        {
            return false;
        }

        $code = $request->input('code');
        $code = preg_replace('/[^0-9]/', '', $code);

        if (!hash_equals($config['code'], $code))
        {
            return false;
        }

        unset($config['code']);
        unset($config['codeGenerated']);

        return true;
    }

    /**
     * @param User $user
     * @param      $error
     *
     * @return bool
     */
    public function meetsRequirements(User $user, &$error): bool
    {
        if (!$user->email || $user->user_state != 'valid')
        {
            $error = 'you_must_have_valid_email_account_confirmed';

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function requiresConfig(): bool
    {
        return false;
    }

    /**
     * @param TfaProvider $provider
     * @param User        $user
     * @param             $config
     *
     * @return View|null
     */
    public function handleConfig(TfaProvider $provider, User $user, &$config): ?View
    {
        return null;
    }
}
