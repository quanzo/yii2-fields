<?php
namespace x51\yii2\classes\fields;
use \yii\helpers\Html;

class TextInput extends Input{
    public $type = 'text';
    /**
     * @inheritdoc
     */
    protected function renderInput($formName = '') {
        $options = $this->options;
        $options['id'] = $this->id();
        $name = $formName ? $formName.'['.$this->name.']' : $this->name;
        if (!empty($this->model) && !empty($this->attribute)) {
            $options['maxlength'] = true;
            $options['name'] = $name;
            return Html::activeTextInput($this->model, $this->attribute, $options);
        }
        return Html::textInput($name, $this->value, $options);
    }
} // end class