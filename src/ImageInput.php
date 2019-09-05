<?php
namespace x51\yii2\classes\fields;
use \x51\yii2\classes\Image;
use yii\base\DynamicModel;
use \Yii;

class ImageInput extends FileInput
{
    public $maxImageWidth = 1000;

    /**
    * @inheritdoc
    */
    public function getValueSave()
    {
        Yii::debug('ImageInput save');
        Yii::debug(print_r($this->value, true));

        if ($this->value instanceof \yii\web\UploadedFile) {
            // текущее значение - это загруженный файл
            // валидация на картинку
            $model = DynamicModel::validateData(
                ['file' => $this->value],
                [
                    ['file', 'image'],
                ]
            );            

            if ($model->hasErrors()) {
                // валидация завершилась с ошибкой - это возможно не картинка
                Yii::debug('File validate error: not image');       
                $this->value = '';
            } else {
                $dir = $this->getMoveToDir();
                $relDir = $this->getRelDir();

                $ver = 0;
                do {
                    $next = false;
                    $filename = $this->value->baseName . ($ver > 0 ? $ver : '') . '.' . $this->value->extension;
                    $fullFilename = $dir . $filename;

                    if (file_exists($fullFilename)) {
                        $ver++;
                        $next = true;
                    }
                } while ($next);
                $this->value->saveAs($fullFilename);
                // тест на максимальную ширину картинки
                
                if ($this->maxImageWidth) {
                    $sizes = getimagesize($fullFilename);
                    if ($sizes[0] > $this->maxImageWidth) { // уменьшить размер изображения
                        $ratio = $sizes[0] / $this->maxImageWidth;
                        $height = $sizes[1] * $ratio;
                        Image::imageFileResize($fullFilename, $fullFilename, $this->maxImageWidth, 0);
                    }
                }

                if ($relDir) {
                    $this->setValue($relDir . $filename);
                } else {
                    $this->setValue($filename);
                }
            }
        }
        return $this->getValue();
    } // end getValueSave

    /**
 * @inheritdoc
 */

    protected function renderCurrentValue() {
        $output = '';
        $value = $this->getValue();

        if ($value) {
            $output .= '<div class="current-value image"><img src="' . $value . '"></div>';
        }
        return $output;
    }

    
} // end class
