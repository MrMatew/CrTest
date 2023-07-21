<?php

namespace App\Entity;

/**
 * COLUMNS
 * @property string $option_id             Идентификатор настройки
 * @property array  $allowed_tfa_providers Список доступных 2fa провайдеров
 * @property array  $default_tfa_providers Список 2fa провайдеров по умолчанию. Может быть пустым
 * @property bool   $required_tfa          Требуется ли 2fa подтверждение для этой настройки. В целом, не факт, что оно
 *           нужно, но можно включать/выключать по необходимости
 */
class Option implements EntityInterface
{
    public function getStructure(Structure $structure): Structure
    {
        $structure->table = 'option';
        $structure->primaryKey = 'option_id';
        $structure->columns = [
            'option_id'             => ['type' => 'varbinary(50)', 'maxLength' => 50, 'required' => true, 'unique' => true],
            'allowed_tfa_providers' => ['type' => 'json', 'required' => true, 'default' => []],
            'default_tfa_providers' => ['type' => 'json', 'required' => true, 'default' => []],
            'required_tfa'          => ['type' => 'tinyint(3)', 'required' => true, 'default' => true]
        ];

        return $structure;
    }
}
