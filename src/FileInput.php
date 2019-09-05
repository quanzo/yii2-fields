<?php
namespace x51\yii2\classes\fields;

use \Yii;
use \yii\helpers\Html;
use \yii\web\UploadedFile;

class FileInput extends Base
{
    protected $_moveToDir = 'upload/';
    /*public $useDateDir = true;
    public $useUserIdDir = true;*/

    /**
     * @inheritdoc
     */
    protected function renderInput($formName = '')
    {
        
        Yii::debug($this->value, "renderInput");
        Yii::debug($this->getValue(), "renderInput");
        //Yii::debug($this->getValueSave(), "renderInput");


        $output = '';
        $options = $this->options;
        $options['id'] = $this->id();
        
        //$name = $formName ? $formName . '[' . $this->name . $multiple . ']' : $this->name . $multiple;
        $name = $this->name;     

        if (!empty($this->model) && !empty($this->attribute)) {
            $options['name'] = $name;
            $output .= Html::activeFileInput($this->model, $this->attribute, $options);
        } else {
            $output .= Html::fileInput($name, $this->value, $options);
        }
        $output .= $this->renderCurrentValue();
        return $output;
    } // end renderInput

    /**
     * Формирует отображение текущего значения
     *
     * @return string
     */
    protected function renderCurrentValue() {
        $output = '';
        $value = $this->getValue();

        if ($value) {
            $output .= '<div class="current-value">' . $value . '</div>';
        }
        return $output;
    }

    /**
     * Возвращает полный путь к папке, в которую следует поместить файлы
     *
     * @return string
     */
    public function getMoveToDir()
    {
        if (is_callable($this->_moveToDir)) {
            $f = $this->_moveToDir;
            $dir = $f();
        } else {
            $dir = $this->_moveToDir;
        }
        if (!$dir) {
            $dir = $_SERVER['DOCUMENT_ROOT'];
        } else {
            if (substr($dir, 0, 1) != '/') { // задан относительный путь
                $dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $dir;
            }
        }
        if (substr($dir, strlen($dir) - 1, 1) != '/') {
            $dir .= '/';
        }
        return $dir;
    }

    public function setMoveToDir($dir)
    {
        $this->_moveToDir = $dir;
    }

    /**
     * Возвращает путь к файлу относительно корня сайта (если относительный путь есть)
     *
     * @return void
     */
    public function getRelDir()
    {
        if (is_callable($this->_moveToDir)) {
            $f = $this->_moveToDir;
            $dir = $f();
        } else {
            $dir = $this->_moveToDir;
        }
        if (!$dir) {
            return '/';
        } else {
            if (substr($dir, strlen($dir) - 1, 1) != '/') {
                $dir .= '/';
            }
            if (substr($dir, 0, 1) != '/') { // задан относительный путь
                return '/' . $dir;
            }
        }
        return false;
    }

    public function getValueSave()
    {
        $arValue = [];
        $arResult = [];
        $multiple = false;

        if (!empty($options['multiple']) && $options['multiple']) {
            $arValue = $this->value;
            $multiple = true;
        } else {
            $arValue = [$this->value];
        }
        $dir = $this->getMoveToDir();
        $relDir = $this->getRelDir();

        foreach ($arValue as $oneFile) {
            if ($oneFile instanceof \yii\web\UploadedFile) {
                // текущее значение - это загруженный файл
                $ver = 0;
                do {
                    $next = false;
                    $filename = $oneFile->baseName . ($ver > 0 ? $ver : '') . '.' . $oneFile->extension;
                    $fullFilename = $dir . $filename;

                    if (file_exists($fullFilename)) {
                        $ver++;
                        $next = true;
                    }
                } while ($next);
                $oneFile->saveAs($fullFilename);

                if ($relDir) {
                    $arResult[] = $relDir . $filename;
                } else {
                    $arResult[] = $filename;
                }
            } else {
                $arResult[] = $oneFile;
            }
        } // end foreach
        
        if ($multiple) {
            $this->setValue($arResult);
            return serialize($arResult);
        } else {
            $this->setValue(current($arResult));
            return current($arResult);
        }
    }

    /**
     * @inheritdoc
     */
    public function setValueSave($v)
    {
        try {
            $val = unserialize($v);
        } catch (\Exception $e) {
            $val = $v;
        }
        $this->setValue($val);
    }

    /**
     * @inheritdoc
     * Специальный процесс выборки данных
     */
    public function getValueFromArray(array $arData)
    {
        if (!empty($options['multiple']) && $options['multiple']) {
            return UploadedFile::getInstancesByName($this->name);
        } else {
            return UploadedFile::getInstanceByName($this->name);
        }
        return null;
    } // end getValueFromArray

    /*protected function getUploadSubdir()
{
$uploadDirUpd = '';
if ($this->useDateDir) {
$uploadDirUpd = date("Y-m-d");
}
if ($this->useUserIdDir && !empty(Yii::$app->user->identity)) {
if ($uploadDirUpd) {
$uploadDirUpd .= '-';
}
$uploadDirUpd .= Yii::$app->user->id;
}
if ($uploadDirUpd) {
$uploadDirUpd .= '/';
}
return $uploadDirUpd;
}*/

} // end class
