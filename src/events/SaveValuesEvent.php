<?php
namespace x51\yii2\classes\fields\events;

use \yii\base\Event;

class SaveValuesEvent extends Event
{
    public $attributes;
    public $model;
} // end class
