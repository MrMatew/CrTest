<?php

namespace App\Entity;

use App\TfaProvider\ProviderInterface;

/**
 * COLUMNS
 * @property string                 $provider_id
 * @property string                 $provider_class
 * @property int                    $priority
 * @property bool                   $is_active
 * @property array                  $options
 *
 * GETTERS
 * @property ProviderInterface|null $handler
 *
 * RELATIONS
 * @property UserTfa[]|null         $UserEntries
 */
class TfaProvider implements EntityInterface
{
    /**
     * Получение нового экземпляра обработчика
     *
     * @return ProviderInterface|null
     */
    public function getHandler()
    {
        $class = $this->provider_class;
        if (!class_exists($class))
        {
            return null;
        }

        return new $class($this->provider_id);
    }

    /**
     * @param $userId
     *
     * @return bool
     */
    public function isEnabled($userId = null): bool
    {
        return ($userId && $this->UserEntries[$userId]);
    }

    /**
     * @param $userId
     *
     * @return bool
     */
    public function canEnable($userId = null): bool
    {
        $handler = $this->handler;
        if ($handler && $handler->canEnable())
        {
            return !$this->isEnabled($userId);
        }

        return false;
    }

    public function getStructure(Structure $structure): Structure
    {
        $structure->table = 'tfa_provider';
        $structure->primaryKey = 'provider_id';
        $structure->columns = [
            'provider_id'    => ['type' => 'varbinary(25)', 'maxLength' => 25, 'match' => 'alphanumeric', 'required' => true],
            'provider_class' => ['type' => 'varchar(100)', 'maxLength' => 100, 'required' => true],
            'priority'       => ['type' => 'int(10)', 'default' => 100],
            'is_active'      => ['type' => 'tinyint(3)', 'default' => true],
            'options'        => ['type' => 'json', 'default' => []]
        ];
        $structure->getters = [
            'title'       => false,
            'description' => false,
            'handler'     => true
        ];
        $structure->relations = [
            'UserEntries' => [
                'entity'     => '\App\Entity\UserTfa',
                'type'       => 'TO_MANY',
                'conditions' => 'provider_id',
                'key'        => 'user_id'
            ],
        ];

        return $structure;
    }
}
