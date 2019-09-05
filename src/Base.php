<?php
namespace x51\yii2\classes\fields;

use \InvalidArgumentException;
use \x51\yii2\classes\fields\events\BeforeRenderFieldEvent;
use \yii\base\Model;
use \Yii;

class Base extends \yii\base\Component
{
    const EVENT_BEFORE_RENDER = 'beforeRenderField';

    public $title = ''; // заголовок поля
    protected $_value = ''; // содержимое поля
    public $name = ''; // имя этого поля
    public $hint = ''; // пояснение
    public $options = []; // дополнительные опции
    public $rules = []; // правила валидации для этого поля
    public $id; // id поля
    public $model; // модель к которой относится поле
    public $attribute; // атрибут в модели
    public $js = '';
    
    //public $form; // если поле привязано к ActiveForm

    public $template = '{title}{input}{hint}';
    public $template_title = '<label class="control-label" for="{id}">{title}</label>';
    public $template_hint = '{hint}';

    public function init()
    {
        $this->options['id'] = $this->id();
    }

    /**
     * возвращает визуальное представление поля
     *
     * @return string
     */
    public function render($formName = '')
    {
        // beforeRender
        $event = new BeforeRenderFieldEvent();
        $event->field = $this;
        $event->formName = $formName;
        $event->title = $this->renderTitle($formName);
        $event->input = $this->renderInput($formName);
        $event->hint = $this->renderHint($formName);
        $event->template = $this->template;
        $event->js = $this->js;
        $event->isValid = true;

        $this->trigger(self::EVENT_BEFORE_RENDER, $event);

        if ($event->isValid) {
            if ($event->js) {
                Yii::$app->view->registerJs($event->js);
            }
            return str_replace([
                '{title}',
                '{input}',
                '{hint}',
            ], [
                $event->title,
                $event->input,
                $event->hint,
            ], $event->template);
        }
        return '';
    } // end render

    /**
     * Возвращает заголовок для вывода
     *
     * @return string
     */
    protected function renderTitle($formName = '')
    {
        $id = $this->id();
        if (!$id) {
            $id = '';
        }
        return str_replace(
            ['{title}', '{id}', '{hint}'],
            [$this->title, $id, $this->hint],
            $this->template_title
        );
    }

    /**
     * Возвращает html для элемента управления
     *
     * @return string
     */
    protected function renderInput($formName = '')
    {
        return '';
    }

    /**
     * Возвращает html для hint
     *
     * @return string
     */
    protected function renderHint($formName = '')
    {
        $id = $this->id();
        if (!$id) {
            $id = '';
        }
        return str_replace(
            ['{title}', '{id}', '{hint}'],
            [$this->title, $id, $this->hint],
            $this->template_hint
        );
    }

    /**
     * Возвращает id элемента
     *
     * @return string
     */
    protected function id()
    {
        if (!empty($this->options['id'])) {
            return $this->options['id'];
        } elseif (!empty($this->id)) {
            return $this->id;
        } else {
            return str_replace(
                ['[', ']'],
                ['-', ''],
                strtolower(trim($this->name))
            ) . rand(1, 999);
        }
        return '';
    }

    /**
     * Возвращает содержимое для сохранения.
     * Например, в value находится массив. Метод должен вернуть сериализованую версию.
     *
     * @return string
     */
    public function getValueSave()
    {
        return $this->getValue();
        // return serialize($this->value);
    }

    /**
     * Записывает сохраненное значение.
     * Например, храним сериализованные данные. Передаем в этот метод и в $this->value должен быть массив
     *
     * @param string $v
     * @return void
     */
    public function setValueSave($v)
    {
        //$this->value = $v;
        //$this->value = unserialize($v);
        $this->setValue($v);
    }

    /**
     * Синхронно обновляет значение поля в привязанной модели (если она есть)
     *
     * @param mixed $v
     * @return void
     */
    public function setValue($v)
    {
        $this->_value = $v;
        if (!empty($this->model) && !empty($this->attribute)) {
            $this->model[$this->attribute] = $this->_value;
        }
    }

    /**
     * Возвращает значение поля (синхронизуря с моделью)
     *
     * @return mixed
     */
    public function getValue()
    {
        if (!empty($this->model) && !empty($this->attribute)) {
            //$this->setValue($this->model[$this->attribute]);
            $this->_value = $this->model[$this->attribute];
        }
        return $this->_value;
    } // end getValue

    /**
     * Выбирает из массива входных данных те, которые относятся к текущему полю
     * Например, входной массив - это POST данные.
     * В простом случае должно вернуться значение поля с именем $this->name.
     * Значение возвращается и не сохраняется в поле.
     *
     * @param array $arData
     * @return mixed
     */
    public function getValueFromArray(array $arData)
    {
        if (isset($arData[$this->name])) {
            return $arData[$this->name];
        }
        return null;
    } // end getValueFromArray

    /**
     * Атрибут в модели
     *
     * @param string $attr
     * @return void
     */
    public function setAttribute($attr)
    {
        if (is_string($attr)) {
            $this->attribute = $attr;
        } else {
            throw new InvalidArgumentException('The attribute must be a string');
        }
    }

    /**
     * Устанавливает модель, к которой привязано это поле.
     * При установке или получении значения этого поля, будет синхронизация с моделью.
     *
     * @param Model $m
     * @return void
     */
    public function setModel(Model $m)
    {
        $this->model = $m;
    }
} // end class
