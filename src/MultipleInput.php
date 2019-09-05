<?php
namespace x51\yii2\classes\fields;

use \yii\helpers\Html;

class MultipleInput extends Base
{
    public $type = 'text';
    protected $count = 5;

    public function setCount($c)
    {
        $this->count = intval($c);
        if ($this->count < 1) {
            $this->count = 1;
        }
    }

    public function getCount()
    {
        return $this->count;
    }

    /**
     * @inheritdoc
     */
    protected function renderInput($formName = '')
    {
        $out = '';
        $options = $this->options;

        for ($i = 0; $i < $this->count; $i++) {
            $id = $this->id() . '_' . $i;
            $name = $formName ? $formName . '[' . $this->name . '_' . $i . ']' : $this->name . '_' . $i;
            $options['id'] = $id;
            if (is_array($this->value)) {
                if (isset($this->value[$i])) {
                    $v = $this->value[$i];
                } else {
                    $v = '';
                }
            } else {
                $v = $this->value;
            }

            $out .= Html::input($this->type, $name, $v, $options);
        }
        return $out;
    } // end renderInput

    /**
     * @inheritdoc
     */
    public function getValueSave()
    {
        return serialize($this->getValue());
    }

    /**
     * @inheritdoc
     */
    public function setValueSave($v)
    {
        try {
            $val = unserialize($v);
        } catch (\Exception $e) {
            $val = [];
        }
        $this->setValue($val);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromArray(array $arData)
    {
        // Из всего массива данных выбирает нужные
        $arResult = [];
        $ifFind = false;
        for ($i = 0; $i < $this->count; $i++) {
            $n = $this->name . '_' . $i;
            if (isset($arData[$n])) {
                $arResult[$i] = $arData[$n];
                $ifFind = true;
            } else {
                $arResult[$i] = '';
            }
        }
        if (!$ifFind) {
            return null;
        }
        return $arResult;
    } // end getValueFromArray

} // end class
