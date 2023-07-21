<?php

namespace App\Http\Controllers;

use App\Entity\Option;
use App\Entity\User;
use App\Entity\UserOptionTfa;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class Account extends Controller
{
    /**
     * Пример экшна с проверкой 2фа кода
     * Например, смена даты рождения юзера
     *
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function changeDob(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Option $option */
        $option = Option::where('option_id', 'account_dob')->get();

        /** @var UserOptionTfa $optionTfa */
        $optionTfa = UserOptionTfa::where('user_id', $user->user_id)
            ->where('option_id', 'account_dob')->get();

        // Если у юзера на настроена 2фа авторизация для настройки, а она требуется, то просим сначала добавить 2фа
        if (!$optionTfa && $option->required_tfa)
        {
            return error('please_setup_tfa_first');
        }

        // Новая дата рождения. Данные валидированы
        $newDob = $request->input('dob');

        if ($optionTfa)
        {
            // Храним данные в сессии
            $sessionKey = 'tfaData_' . $optionTfa->Provider->provider_id;
            $session = $request->session();
            $providerData = [];
            $handler = $optionTfa->Provider->handler;

            // Отправка кода
            $triggerData = $handler->trigger($user, $providerData, $request);

            // Сохранение данных в сессии юзера
            $session->put($sessionKey, $providerData);

            // Здесь данные, которые будут храниться в виде скрытого инпута в форме ввода кода
            // <input type="hidden"...
            // Смотрел как сделано у вас на проекте, и, насколько я понял, параметры передаются с использованием обратимого шифрования
            $extraData = [
                'dob' => $newDob
            ];

            // Вывод окна с просьбой ввода кода
            return $handler->render($user, $providerData, $triggerData, $extraData);
        }

        // Если ввод 2фа не требуется, рероут контроллера
        return $this->changeDobConfirm($request);
    }

    /**
     * Контроллер, в котором при необходимости проверяется 2фа код
     * После всех проверок - запись данных
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|Application|RedirectResponse|Redirector
     */
    public function changeDobConfirm(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Option $option */
        $option = Option::where('option_id', 'account_dob')->get();

        /** @var UserOptionTfa $optionTfa */
        $optionTfa = UserOptionTfa::where('user_id', $user->user_id)
            ->where('option_id', 'account_dob')->get();

        // Если у юзера не настроена 2фа авторизация для настройки, а она требуется, то просим сначала добавить 2фа
        if (!$optionTfa && $option->required_tfa)
        {
            return error('please_setup_tfa_first');
        }

        if ($optionTfa)
        {
            // Храним данные в сессии
            $sessionKey = 'tfaData_' . $optionTfa->Provider->provider_id;
            $session = $request->session();
            $handler = $optionTfa->Provider->handler;

            // Проверка 2фа кода
            $providerData = $session->get($sessionKey);
            if (!is_array($providerData) || !$handler->verify($user, $providerData, $request))
            {
                // Подтверждение не удалось
                return error('two_step_verification_value_could_not_be_confirmed');
            }

            $session->remove($sessionKey);
        }

        $newDob = $request->input('dob');

        // Сохранение данных после проверки
        $user->dob = $newDob;
        $user->save();

        return redirect('/account/');
    }
}
