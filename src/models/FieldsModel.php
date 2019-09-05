<?php
namespace x51\yii2\classes\fields\models;

use \x51\yii2\classes\fields\events\LoadValueEvent;
use \x51\yii2\classes\fields\events\SaveValuesEvent;
use \x51\yii2\classes\fields\events\RenderFormEvent;
use \Yii;

/**
 * Модель с динамическим полями, заданных массивом
 * ```php
 * ['field1'=>[
 *  'class'=>'\x51\yii2\classes\fields\InputText',
 *  'name'=>'formfield1',
 *  'title'=>'Текстовое поле',
 *  'value'=>'значение',
 *  'hint'=>'пояснение',
 *  'rules' => [
 *      ['required']
 *  ],
 * или
 *  'rules' => ['image', 'extensions' => 'png, jpg, gif'] 
 * 
 * ]
 * ]
 * ```
 */
class FieldsModel extends \yii\base\DynamicModel
{
    const EVENT_BEFORE_SAVE = 'beforeSaveFieldsModel';
    const EVENT_BEFORE_LOAD_FIELD = 'beforeLoadFieldsModel';
    const EVENT_AFTER_LOAD = 'afterLoadFieldsModel';
    const EVENT_BEFORE_RENDER = 'beforeRenderFormFieldsModel';
    const EVENT_AFTER_RENDER = 'afterRenderFormFieldsModel';

    protected $defClass = '\x51\yii2\classes\fields\Input';
    protected $fields;
    protected $rules = [];
    protected $funcSave = null; // функция для сохранения данных -> function (array $arData) {} -> $arData массив, где ключ имя поля, значение - значение подготовленное к сохоанению

    public function __construct(array $fields, array $rules = [], array $config = [])
    {
        $this->fields = $fields;
        $this->rules = $rules;
        $arResult = [];
        foreach ($this->fields as $field => $arFieldParam) {
            if (is_array($arFieldParam)) {
                $arResult[$field] = !empty($arFieldParam['value']) ? $arFieldParam['value'] : '';
            } elseif (is_object($arFieldParam) && $arFieldParam instanceof \x51\yii2\classes\fields\Base) {
                $arResult[$field] = $arFieldParam->value;
            } else {
                $arResult[$field] = $arFieldParam;
            }
        }
        parent::__construct($arResult, $config);
    } // end construct

    public function setFuncSave(callable $f)
    {
        $this->funcSave = $f;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $arResult = [];
        if (!empty($this->fields) && is_array($this->fields)) {
            foreach ($this->fields as $field => $arFieldParam) {
                if (is_array($arFieldParam)) {
                    $arResult[$field] = !empty($arFieldParam['title']) ? $arFieldParam['title'] : $field;
                } elseif (is_object($arFieldParam) && $arFieldParam instanceof \x51\yii2\classes\fields\Base) {
                    $arResult[$field] = $arFieldParam->title;
                } else {
                    $arResult[$field] = '';
                }
            }
        }
        return $arResult;
    } // end attributeLabels

    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        $arResult = [];
        if (!empty($this->fields) && is_array($this->fields)) {
            foreach ($this->fields as $field => $arFieldParam) {
                if (is_array($arFieldParam)) {
                    $arResult[$field] = !empty($arFieldParam['hint']) ? $arFieldParam['hint'] : '';
                } elseif (is_object($arFieldParam) && $arFieldParam instanceof \x51\yii2\classes\fields\Base) {
                    $arResult[$field] = $arFieldParam->hint;
                } else {
                    $arResult[$field] = '';
                }
            }
        }
        return $arResult;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $arRules = [];
        // соберем правила из полей
        foreach ($this->fields as $fname => $fconf) {
            if (!empty($fconf['rules']) && is_array($fconf['rules'])) {
                $manyRules = true;
                array_walk($fconf['rules'], function ($val, $key) use (&$manyRules) {
                    if (!is_array($val)) {
                        $manyRules = false;
                    }
                });
                if ($manyRules) {
                    $arCurrRules = &$fconf['rules'];
                } else {
                    $arCurrRules = [ & $fconf['rules']];
                }
                foreach ($arCurrRules as $rule) {
                    $insertName = true;
                    reset($rule);
                    $firstParam = current($rule);
                    $firstParamKey = key($rule);
                    if (!empty($firstParam)) {
                        if (is_array($firstParam)) {
                            $findName = in_array($fconf['name'], $firstParam);
                            $findFieldName = in_array($fname, $firstParam);
                            if ($findName && !$findFieldName) {
                                $rule[$firstParamKey][] = $fname;
                                $insertName = false;
                            } elseif ($findFieldName) {
                                $insertName = false;
                            }
                        } elseif (is_string($firstParam)) {
                            if ($firstParam == $fconf['name']) {
                                $rule[$firstParamKey] = $fname;
                                $insertName = false;
                            } elseif ($firstParam == $fname) {
                                $insertName = false;
                            }
                        }
                    }
                    if ($insertName) {
                        array_unshift($rule, $fname);
                    }
                    $arRules[] = $rule;
                }
            }
        }
        if (empty($arRules)) {
            return $this->rules;
        } else {
            if (!empty($this->rules)) {
                array_push($arRules, ...$this->rules);
            }
            return $arRules;
        }
    }

    /**
     * Возвращает поля в виде ассоциативного, где ключ - имя поля в БД, значение - класс описывающий поле
     *
     * @return array
     */
    public function getFields()
    {
        $arResult = [];
        foreach ($this->fields as $fname => $fconf) {
            $o = $this->getField($fname);
            $arResult[$fname] = $o;
        }
        return $arResult;
    } // end getFields

    /**
     * Возвращает одно поле в виде класса
     *
     * @return array
     */
    public function getField($fname)
    {
        if (!empty($this->fields[$fname])) {
            $fconf = $this->fields[$fname];
            if (empty($fconf['class'])) {
                $fconf['class'] = $this->defClass;
            }
            if (isset($this[$fname])) {
                $fconf['value'] = $this[$fname];
            } else {
                $fconf['value'] = '';
            }
            $fconf['model'] = $this;
            $fconf['attribute'] = $fname;
            return Yii::createObject($fconf);
        }
        return false;
    } // end getField

    /**
     * {@inheritdoc}
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $func = $this->funcSave;
        if (!empty($func) && is_callable($func)) {
            if ($runValidation && !$this->validate($attributeNames)) {
                return false;
            }

            // beforeSave
            $event = new SaveValuesEvent();
            $event->attributes = $this->attributes; // сохраняемые данные
            $event->model = $this; // текущая модель
            $this->trigger(self::EVENT_BEFORE_SAVE, $event);

            $arData = $event->attributes;
            if ($arData) {
                foreach ($arData as $key => &$val) {
                    if ($o = $this->getField($key)) {
                        $o->value = $val;
                        $val = $o->getValueSave();
                        // дополнительная синхронизация данных - необходимо, т.к. в getValueSave может быть изменено основное значение
                        //$this->fields[$key]['value'] = $o->value;
                        //$this[$key] = $o->value;
                    }
                }
                //Yii::debug($this->fields, 'FieldsModel::save');
                return $func($arData);
            }
        }
        return false;
    } // end save

    public function load($data, $formName = null)
    {
        $scope = $formName === null ? $this->formName() : $formName;
        $arData = null;
        if ($scope === '' && !empty($data)) {
            $arData = &$data;
        } elseif (isset($data[$scope])) {
            $arData = &$data[$scope];
        }

        if ($arData) {
            // транслируем данные
            //$arResult = [];
            $arOldData = $this->attributes;
            foreach ($this->fields as $key => &$arCfg) {
                /*if (!empty($arCfg['name']) && isset($arData[$arCfg['name']])) {
                    $formKey = $arCfg['name'];
                } else {
                    $formKey = $key;
                }*/

                $field = $this->getField($key);
                $fieldValue = $field->getValueFromArray($arData);
                
                //Yii::debug($fieldValue, 'FieldsModel');

                //if (isset($arData[$formKey])) {
                if ($fieldValue != null) {
                    // beforeLoad
                    $event = new LoadValueEvent();
                    $event->scope = $scope; // имя формы
                    $event->upload = $arData; // данные из формы
                    $event->model = $this; // эта модель
                    $event->attribute = $key; // имя загружаемого поля
                    $event->value = $fieldValue; // значение загружаемого поля
                    $event->config = $arCfg; // массив с конфигурацией загружаемого поля
                    $this->trigger(self::EVENT_BEFORE_LOAD_FIELD, $event);

                    $key = $event->attribute;
                    
                    $this->$key = $event->value;
                    $arCfg['value'] = $event->value;

                    //$arResult[$key] = $arData[$formKey];
                    //$arResult[$key] = $fieldValue;
                }
            }
            // afterLoad
            $event = new LoadValueEvent();
            $event->scope = $scope; // имя формы
            $event->upload = $arData; // данные из формы
            $event->model = $this; // эта модель
            $event->attribute = false;
            $event->value = false;
            $event->config = false;
            $this->trigger(self::EVENT_AFTER_LOAD, $event);
//Yii::debug($arCfg, "FieldsModel::load");
            //$this->setAttributes($arResult);
            return true;
        }
        return false;
    } // end load

    public function render($formName = '') {
        //Yii::debug($this->fields, 'FieldsModel::render fields');

        $event = new RenderFormEvent();
        $event->out = '';
        $event->fields = $this->getFields();
        $event->formName = $formName;
        $this->trigger(self::EVENT_BEFORE_RENDER, $event);
        $arFields = $event->fields;
        $out = $event->out;

        foreach ($arFields as $f => $o) {
            $out .= $o->render($formName);
        }

        $event->out = $out;
        $event->fields = $arFields;
        $this->trigger(self::EVENT_AFTER_RENDER, $event);
        
        return $event->out;
    } // end render

} // end class
