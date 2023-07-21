<?php

namespace App\Entity;

/**
 * COLUMNS
 * @property int|null             $user_id
 * @property string               $email
 * @property int                  $dob        Дата рождения
 * @property int                  $language_id
 * @property string               $timezone
 * @property int                  $registration_date
 * @property string               $user_state
 *
 * RELATIONS
 * @property UserTfa[]|null       $TwoFactors Список настроенных юзером 2фа методов
 * @property UserOptionTfa[]|null $Options    Список выбранных пользователем опций (методов 2фа) для настроек
 */
class User implements EntityInterface
{
    public function isValid(): bool
    {
        return $this->user_state == 'valid';
    }

    public function getStructure(Structure $structure): Structure
    {
        $structure->table = 'user';
        $structure->primaryKey = 'user_id';
        $structure->columns = [
            'user_id'           => ['type' => 'int(10)', 'autoIncrement' => true, 'nullable' => true],
            'email'             => ['type' => 'varchar(120)', 'maxLength' => 120],
            'dob'               => ['type' => 'int(10)', 'default' => 0],
            'language_id'       => ['type' => 'int(10)', 'default' => 0],
            'timezone'          => ['type' => 'varchar(50)', 'maxLength' => 50, 'default' => 'Europe/London'],
            'registration_date' => ['type' => 'int(10', 'default' => time()],
            'user_state'        => ['type' => 'enum', 'default' => 'valid', 'allowedValues' => ['valid', 'disabled']]
        ];
        $structure->relations = [
            'TwoFactors' => [
                'entity'     => '\App\Entity\UserTfa',
                'type'       => 'TO_MANY',
                'conditions' => 'user_id',
                'primary'    => true
            ],
            'Options'    => [
                'entity'     => '\App\Entity\UserOptionTfa',
                'type'       => 'TO_MANY',
                'conditions' => 'user_id',
                'primary'    => true
            ]
        ];

        return $structure;
    }
}
