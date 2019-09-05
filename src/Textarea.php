<?php
namespace x51\yii2\classes\fields;
use \yii\helpers\Html;

class Textarea extends Base
{
    /**
     * @inheritdoc
     */
    protected function renderInput($formName = '')
    {
        $options = $this->options;
        $options['id'] = $this->id();
        $name = $formName ? $formName . '[' . $this->name . ']' : $this->name;
        if (!empty($this->model) && !empty($this->attribute)) {
            $options['maxlength'] = true;
            $options['name'] = $name;
            return Html::activeTextarea($this->model, $this->attribute, $options);           
        }
        return Html::textarea($name, $this->value, $options);
    }
} // end class
