<?php
namespace x51\yii2\classes\fields\events;
use \yii\base\Event;

class LoadValueEvent extends Event{
    public $scope;
    public $upload;
    public $model;
    public $attribute;
    public $value;
    public $config;
} // end class