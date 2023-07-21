<?php

namespace App\Entity;

class Structure
{
    public $table;
    public $primaryKey;
    public $columns = [];
    public $relations = [];
    public $getters = [];
    public $defaultWith = [];
    public $options = [];
}
