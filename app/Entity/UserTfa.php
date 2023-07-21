<?php

namespace App\Entity;

/**
 *
 * COLUMNS
 * @property int|null    $user_tfa_id    Идентификатор 2фа юзера
 * @property int         $user_id        Идентификатор юзера
 * @property string      $provider_id    Идентификатор провайдера 2фа
 * @property array       $provider_data  Данные 2фа
 * @property int         $last_used_date Последняя дата использования метода
 *
 * RELATIONS
 * @property User        $User           Релейшн к юзеру
 * @property TfaProvider $Provider       Релейшн к 2фа провайдеру
 */
class UserTfa implements EntityInterface
{
    public function getStructure(Structure $structure): Structure
    {
        $structure->table = 'user_tfa';
        $structure->primaryKey = 'user_tfa_id';
        $structure->columns = [
            'user_tfa_id'    => ['type' => 'int(10)', 'autoIncrement' => true],
            'user_id'        => ['type' => 'int(10)', 'required' => true],
            'provider_id'    => ['type' => 'varbinary(25)', 'maxLength' => 25, 'required' => true],
            'provider_data'  => ['type' => 'json', 'default' => []],
            'last_used_date' => ['type' => 'int(10)', 'default' => time()]
        ];
        $structure->getters = [];
        $structure->relations = [
            'User'     => [
                'entity'     => '\App\Entity\User',
                'type'       => 'TO_ONE',
                'conditions' => 'user_id',
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
