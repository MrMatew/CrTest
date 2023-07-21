<?php

namespace App\Entity;

/**
 * В данной модели хранится выбранный пользователем способ 2фа подтверждения для конкретной настройки.
 * Ключ в БД - составной
 *
 * COLUMNS
 * @property int         $user_id          Идентификатор пользователя
 * @property string      $option_id        Идентификатор настройки
 * @property string      $tfa_provider     Идентификатор выбранного метода 2фа
 *
 * RELATIONS
 * @property User        $User             Релейшн к юзеру
 * @property Option      $Option           Релейшн к настройке
 * @property TfaProvider $Provider         Релейшн к 2фа провайдеру
 */
class UserOptionTfa implements EntityInterface
{
    public function getStructure(Structure $structure): Structure
    {
        $structure->table = 'user_option_tfa';
        $structure->primaryKey = ['user_id', 'option_id'];
        $structure->columns = [
            'user_id'      => ['type' => 'int(10)', 'required' => true],
            'option_id'    => ['type' => 'varbinary(25)', 'maxLength' => 25, 'required' => true],
            'tfa_provider' => ['type' => 'varbinary(25)', 'default' => '']
        ];
        $structure->getters = [];
        $structure->relations = [
            'User'     => [
                'entity'     => '\App\Entity\User',
                'type'       => 'TO_ONE',
                'conditions' => 'user_id',
                'primary'    => true
            ],
            'Option'   => [
                'entity'     => 'App\Entity\Option',
                'type'       => 'TO_ONE',
                'conditions' => 'provider_id',
                'primary'    => true
            ],
            'Provider' => [
                'entity'     => 'App\Entity\TfaProvider',
                'type'       => 'TO_ONE',
                'conditions' => 'provider_id',
                'primary'    => true
            ],
        ];

        return $structure;
    }
}
