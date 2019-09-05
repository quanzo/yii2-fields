<?php
namespace x51\yii2\classes\fields;

use \InvalidArgumentException;
use \yii\base\Model;
use \yii\widgets\ActiveForm;
use \Yii;

class EditorjsInput extends Base
{
    public $moduleEditorjs;
    
    /**
     * @inheritdoc
     */
    protected function renderInput($formName = '') {
        if ($this->moduleEditorjs) {
            $module = Yii::$app->getModule($this->moduleEditorjs);
            ob_start();
            $module->editorjs(
                $this->id(),
                $formName,
                !empty($formName) ? $formName.'['.$this->name.']' : $this->name,
                $this->value,
                $this->options
            );
            $result = ob_get_contents();
            ob_end_clean();
            return $result;
        } else {
            throw new InvalidArgumentException('Not set parameter moduleEditorjs');
        }
    } // end renderInput
} // end class