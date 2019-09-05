<?php
namespace x51\yii2\classes\fields;
use \yii\helpers\Html;

class Input extends Base {
    public $type = 'text';
    
    /**
     * @inheritdoc
     */
    protected function renderInput($formName = '') {
        $options = $this->options;
        $options['id'] = $this->id();
        $name = $formName ? $formName.'['.$this->name.']' : $this->name;
        if (!empty($this->model) && !empty($this->attribute)) {
            $options['name'] = $name;
            return Html::activeInput($this->type, $this->model, $this->attribute, $options);
        }
        return Html::input($this->type, $name, $this->value, $options);
    }
} // end class