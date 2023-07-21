<?php

namespace App\Http\Controllers;

use App\Entity\Option;
use App\Entity\TfaProvider;
use App\Entity\User;
use App\Entity\UserOptionTfa;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class AccountOptions extends Controller
{
    /**
     * Здесь же проверка на то, что юзер авторизован и имеет все права, и так далее...
     * В принципе, почти все это выносится в файл роутингов в middleware
     */
    public function __construct()
    {
        /*
         * Check if user->is_valid && user->hasPermission('viewOptions')
         */
        //$this->authorize('viewOptions');
    }

    /**
     * Возвращает список всех доступных опций, у которых можно изменить настройки 2fa подтверждения
     * + возвращает массив уже заданных юзером настроек
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Application
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Option[] $options */
        $options = Option::where('is_active', true)
            ->get();

        $userChoices = $user->Options;

        $viewParams = [
            'options'     => $options,
            'userChoices' => $userChoices
        ];

        return view('account.options.index', $viewParams);
    }

    /**
     * POST запрос для изменения 2фа провайдера конкретной настройки
     * У настройки может быть только один способ (Например, или SMS, или Email)
     *
     * @param Request $request
     * @param         $optionId
     *
     * @return \Illuminate\Contracts\Foundation\Application|Application|RedirectResponse|Redirector
     */
    public function changeTfaProvider(Request $request, $optionId)
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Option $option */
        $option = Option::where('option_id', $optionId)
            ->get();
        if (!$option)
        {
            return error('invalid_option');
        }

        $providerId = $request->input('provider_id');
        if (!in_array($providerId, $option->allowed_tfa_providers))
        {
            return error('tfa_provider_not_allowed_for_this_option');
        }

        /** @var TfaProvider $provider */
        $provider = TfaProvider::where('provider_id', $providerId)->get();
        if (!$provider || !$provider->canEnable())
        {
            return error('method_can_not_be_enabled');
        }

        /** @var UserOptionTfa $optionTfaProvider */
        $optionTfaProvider = UserOptionTfa::where('user_id', $user->user_id)
            ->where('option_id', $optionId)
            ->get();
        if (!$optionTfaProvider)
        {
            $optionTfaProvider = new UserOptionTfa();
            $optionTfaProvider->user_id = $user->user_id;
            $optionTfaProvider->option_id = $optionId;
        }

        $optionTfaProvider->tfa_provider = $providerId;
        $optionTfaProvider->save();

        return redirect('account/options');
    }
}
