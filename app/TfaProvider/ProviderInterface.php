<?php

namespace App\TfaProvider;

use App\Entity\TfaProvider;
use App\Entity\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

interface ProviderInterface
{
    /**
     * Генерация и отправка кода каким-либо образом
     *
     * @param User    $user
     * @param array   $config
     * @param Request $request
     *
     * @return array
     */
    public function trigger(User $user, array &$config, Request $request): array;

    /**
     * Вывод окна подтверждения, где вводится код
     * У каждого провайдера может быть свой шаблон
     *
     * @param User  $user
     * @param array $config
     * @param array $triggerData
     * @param       $extraData
     *
     * @return View
     */
    public function render(User $user, array $config, array $triggerData, $extraData = []): View;

    /**
     * Верификация ранее сгенерированного кода
     *
     * @param User    $user
     * @param array   $config
     * @param Request $request
     *
     * @return bool
     */
    public function verify(User $user, array &$config, Request $request): bool;

    /**
     * Может ли пользователь включить метод
     * Например, для подключения 2фа через email у него уже должен быть добавлен и подтвержден адрес эл. почты
     *
     * @param User $user
     * @param      $error
     *
     * @return bool
     */
    public function meetsRequirements(User $user, &$error): bool;

    /**
     * Требует ли 2фа метод дополнительной настройки
     *
     * @return bool
     */
    public function requiresConfig(): bool;

    /**
     * Дополнительная настройка 2фа метода при его первом включении
     *
     * @param TfaProvider $provider
     * @param User        $user
     * @param             $config
     *
     * @return View|null
     */
    public function handleConfig(TfaProvider $provider, User $user, &$config): View|null;
}
