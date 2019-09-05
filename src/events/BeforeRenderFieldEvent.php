<?php
namespace x51\yii2\classes\fields\events;

use \yii\base\Event;

class BeforeRenderFieldEvent extends Event
{
    public $field;
    public $formName = '';
    public $title;
    public $input;
    public $hint;
    public $template;
    public $js = '';
    public $isValid = true;
} // end class
