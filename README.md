Fields for Yii2
===============

Why
---

Representation of a form field as a class. For universal configuration of a
model or form as an array of parameters.

\-------------------------------------------

Представление поля формы в виде класса. Для универсальной настройки модели или
формы в виде массива параметров.

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$arFields = [
    'metaTitle' => [
        'class' => '\x51\yii2\classes\fields\Input',
        'title' => 'Title',
        'name' => 'meta_title_field',
        'value' => '',
        'options' => [
            'class' => 'form-control',
        ],
        'rules' => [
            ['required'] // rule without field name
        ],
    ],
    'metaDescription' => [
        'class' => '\x51\yii2\classes\fields\Input',
        'title' => 'Description',
        'name' => 'meta_description_field',
        'value' => '',
        'options' => [
            'class' => 'form-control',
        ],
    ]    
];

$arModelRules = [
    ['metaDescription', 'require'] // rule with field name/names
];

$fieldsModel = new x51\yii2\classes\fields\models\FieldsModel($arFields, $arModelRules);

// set function for save data
$fieldsModel->funcSave(function (array $arData) {/* save model data. arData - array fieldname=>field_value_for_save  */});
// available
if ($fieldsModel->save()) {
...
}

// output form
echo $fieldsModel->render($formName);
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Данные, которые предоставляет модель (поле, заданное классом) и данные для
сохранения, могут быть различными.

Например, поле для ввода нескольких текстовых строк. Предоставляет данные в виде
массива, а данные для сохранения - это сериализованная строка.

\-------------------------------------------

The data that the model provides (the field specified by the class) and the data
to be stored can be different.

For example, a field for entering several text lines. Provides data as an array,
and data to save is a serialized string.

Installation
------------

1.  Copy to the folder with modules and connect *autoload.php*

2.  Or use composer: add to the *require* section of the project
    `"quanzo/yii2-fields": "*"` or `composer require "quanzo/yii2-fields"`

Events
------

### In field

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$arFields = [
    'metaTitle' => [
        'class' => '\x51\yii2\classes\fields\Input',
        'title' => 'Title',
        'name' => 'meta_title_field',
        'value' => '',
        'options' => [
            'class' => 'form-control',
        ],
        'rules' => [
            ['required'] // rule without field name
        ],
        'on '.\x51\yii2\classes\fields\Base::EVENT_BEFORE_RENDER => function ($event) {
    if ($event->field->name == 'meta_title_field') {
        $event->title = '<h2>New title</h2>';
    }
}
    ]
];
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

| id                                                   |                                    | Event class                                              |                                                       |
|------------------------------------------------------|------------------------------------|----------------------------------------------------------|-------------------------------------------------------|
| `\x51\yii2\classes\fields\Base::EVENT_BEFORE_RENDER` | will be called before forming html | `x51\yii2\classes\fields\events\BeforeRenderFieldEvent`  | **Changing data in an event will affect the result.** |

 

### In model

| `x51\yii2\classes\fields\models\FieldsModel::EVENT_BEFORE_SAVE`       | `events\SaveValuesEvent` | Changing property **attributes** in an event will affect the result.                   |
|-----------------------------------------------------------------------|--------------------------|----------------------------------------------------------------------------------------|
| `x51\yii2\classes\fields\models\FieldsModel::EVENT_BEFORE_LOAD_FIELD` | `events\LoadValueEvent`  | Called in \$model-\>load. Called when each data field is processed. Affecting results. |
| `x51\yii2\classes\fields\models\FieldsModel::EVENT_AFTER_LOAD`        | `events\LoadValueEvent`  | Called in \$model-\>load. After the load is complete.                                  |
| `x51\yii2\classes\fields\models\FieldsModel::EVENT_BEFORE_RENDER`     | `events\RenderFormEvent` | Called in \$model-\>render. Affects the result.                                        |
| `x51\yii2\classes\fields\models\FieldsModel::EVENT_AFTER_RENDER`      | `events\RenderFormEvent` | Called in \$model-\>render. Affects the result.                                        |

 

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$fieldsModel->on(FieldsModel::EVENT_BEFORE_RENDER, function ($event) {
    $event->out = '<div class="wrapper">';
});
$fieldsModel->on(FieldsModel::EVENT_AFTER_RENDER, function ($event) {
    $event->out .= '</div>';
});
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
