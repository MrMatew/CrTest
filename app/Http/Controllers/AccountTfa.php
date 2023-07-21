<?php

namespace App\Http\Controllers;

use App\Entity\TfaProvider;
use App\Entity\User;
use App\Entity\UserTfa;
use App\TfaProvider\ProviderInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class AccountTfa extends Controller
{
    /**
     * Выводим список подключенных юзером 2фа методов
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Application
     */
    public function index(Request $request)
    {
        // Просим юзера повторно ввести пароль перед доступом к 2фа настройкам
        //$this->assertPasswordVerified();

        /** @var User $user */
        $user = $request->user();

        /** @var TfaProvider[] $providers */
        $providers = TfaProvider::all()->get();

        $viewParams = [
            'user'      => $user,
            'providers' => $providers,
        ];

        return view('account.tfa.index', $viewParams);
    }

    /**
     * Включение конкретного 2фа метода
     *
     * @param Request $request
     * @param         $providerId
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Application|RedirectResponse|Redirector
     */
    public function enableMethod(Request $request, $providerId)
    {
        // Просим юзера повторно ввести пароль перед доступом к 2фа настройкам
        //$this->assertPasswordVerified();

        /** @var TfaProvider $provider */
        $provider = TfaProvider::where('provider_id', $providerId)->get();
        if (!$provider || !$provider->canEnable())
        {
            return redirect('account/two-step');
        }

        /** @var User $user */
        $user = $request->getUser();

        /** @var ProviderInterface $handler */
        $handler = $provider->handler;
        if (!$handler->meetsRequirements($user, $error))
        {
            // Возврат ошибки, если юзер не подходит требованиям
            return error($error);
        }

        // Храним данные в сессии
        $sessionKey = 'tfaData_' . $provider->provider_id;
        $session = $request->session();

        $step = $request->input('step');

        if ($step == 'confirm')
        {
            $providerData = $session->get($sessionKey);
            if (!is_array($providerData))
            {
                return redirect('account/two-step');
            }

            if (!$handler->verify($user, $providerData, $request))
            {
                // Подтверждение не удалось
                return error('two_step_verification_value_could_not_be_confirmed');
            }

            // Сохраняем
            $userTfa = new UserTfa();
            $userTfa->user_id = $user->user_id;
            $userTfa->provider_id = $provider->provider_id;
            $userTfa->provider_data = $providerData;
            $userTfa->save();

            $session->remove($sessionKey);

            return redirect('account/two-step');
        }

        $providerData = [];

        // Если 2фа провайдер требует дополнительной настройки - выводим страницу с настройками
        if ($handler->requiresConfig())
        {
            // Если в $providerData есть нужные данные - шаг пропускается
            $result = $handler->handleConfig($provider, $user, $providerData);
            if ($result)
            {
                return $result;
            }
        }

        // Отправка кода
        $triggerData = $handler->trigger($user, $providerData, $request);

        // Сохранение данных в сессии юзера
        $session->put($sessionKey, $providerData);

        $viewParams = [
            'provider'     => $provider,
            'handler'      => $handler,
            'providerData' => $providerData,
            'triggerData'  => $triggerData
        ];

        return view('account.tfa.enable', $viewParams);
    }

    // Далее можно добавить экшны для удаления 2фа методов, их редактирования, и так далее
}
