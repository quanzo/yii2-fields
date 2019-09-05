<?php
namespace x51\yii2\classes\fields\events;

use \yii\base\Event;

class RenderFormEvent extends Event
{
    public $fields;
    public $out;
    public $formName;
} // end class
