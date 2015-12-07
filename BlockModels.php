<?php
namespace jesuso\blockmodels;

use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\i18n\Formatter;
use yii\widgets\ActiveForm;
use kartik\icons\Icon;

class BlockModels extends \yii\base\Widget
{
    public $dataProvider;
    public $columns;
    public $id_attribute;
    public $order_by;
    public $formatter;

    private $modelClass;

    public function init()
    {
        parent::init();
        if ($this->formatter == null) {
            $this->formatter = Yii::$app->getFormatter();
        } elseif (is_array($this->formatter)) {
            $this->formatter = Yii::createObject($this->formatter);
        }
        if (!$this->formatter instanceof Formatter) {
            throw new InvalidConfigException('The "formatter" property must be either a Format object or a configuration array.');
        }

        // Load kartic icons
        Icon::map(Yii::$app->view);

        $this->registerAssets();

        if ($this->dataProvider === null) {
            throw new InvalidConfigException('The "dataProvider" property must be set.');
        }

        $this->modelClass = $this->dataProvider->getModels()[0]->className();

        $this->normalizeColumns();
    }

    public function run()
    {
        $models = array_values($this->dataProvider->getModels());
        $keys = $this->dataProvider->getKeys();
        $rows = [];
        $content = '<ul class="blockmodels">';

        foreach ($models as $index => $model) {
            $key = $keys[$index];
            $rows[] = $this->renderModel($model, $key, $index);
        }

        $rows[] = $this->renderNew();

        $content .= implode("\n", $rows);
        $content .= '</ul>';

        return $content;
    }

    private function normalizeColumns()
    {
        // Take one model as base to normalize the columns with its attributes
        $model = $this->dataProvider->getModels()[0];
        $columns = [];

        foreach ($this->columns as $column)
        {
            // Make sure the $columns is a key => value array, if not, then
            // convert it into one so we can work with it.
            if (!is_array($column)) {
                $column = ['attribute' => $column, 'format' => 'text'];
            } else if (!isset($column['format'])) {
                $column['format'] = 'text';
            }

            // First, we make sure that the model actually contains such attribute.
            if (!array_key_exists($column['attribute'], $model->attributes)) {
                throw new InvalidConfigException('The model does not contain such attribute. ' . $column['attribute']);
            }

            array_push($columns, $column);
        }

        $this->columns = $columns;
    }

    private function renderModel($model, $key, $index)
    {
        $result = '<li class="blockmodel">';

        // ActiveForm::begin is directly echoed out, so we have to use output buffer.
        ob_start();
        $form = ActiveForm::begin(['action' => ['update', 'id' => $model->{$this->id_attribute}]]);
        $result .= ob_get_flush();

        // Header
        $result .= '<div class="row">';
        $result .= Html::a(Icon::show('arrows', ['class' => 'drag-btn fa-2x']), null, [
            'title' => Yii::t('yii', 'Move'),
            'aria-label' => Yii::t('yii', 'Move'),
        ]);
        /*
        echo Html::a(Icon::show('pencil', ['class' => 'edit-btn fa-2x']), null, [
            'title' => Yii::t('yii', 'Edit'),
            'aria-label' => Yii::t('yii', 'Edit'),
        ]);
        */
        $result .= Html::a(Icon::show('trash', ['class' => 'delete-btn fa-2x']), ['delete', 'id' => $model->{$this->id_attribute}], [
            'title' => Yii::t('yii', 'Delete'),
            'aria-label' => Yii::t('yii', 'Delete'),
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-method' => 'post',
            'data-pjax' => '0',
        ]);
        $result .= '</div>';

        // Attributes
        $result .= '<div>';
        foreach ($this->columns as $column)
        {
            $options = [];

            // If the user specified an order field and its the current one then
            // we add an order class to the field container for javascript atomation
            // purposes.
            if ($column['attribute'] == $this->order_by) {
                $options['class'] = 'order';
            }

            // TODO. DOCUMENT THIS !!!, turn it into something more similar to GridView so people is familiar with it.
            if (isset($column['closure'])) {
                $result .= $column['closure']($model);
            } elseif (isset($column['widget'])) {
                $result .= $form->field($model, $column['attribute'], ['options' => $options])->widget($column['widget'], ['options' => $column['widget_options']]);
            } else if ($column['format'] == 'image') {
                if (!isset($column['baseUrl'])) {
                    throw new InvalidConfigException('A column of type image must have a baseUrl set');
                }
                // Show the image if there is one already a value in this attribute
                $result .= Html::img($column['baseUrl'].$model->image, ['class' => 'image']);
                $result .= $form->field($model, $column['attribute'], ['options' => $options])->fileInput();
            } else {
                $result .= $form->field($model, $column['attribute'], ['options' => $options]);
            }
            //$result .= '<td>'.Html::activeLabel($model, $key).'</td>';
            //$result .= '<td>'.Html::activeInput('text', $model, $key).'</td>';
        }
        $result .= '</div>';

        // ActiveForm::end is directly echoed out, so we have to use output buffer.
        ob_start();
        ActiveForm::end();
        $result .= ob_get_flush();

        $result .= '</li>'; // .blockmodels
        // $result .= "<pre>\n".print_r($model->attributes, true)."\n</pre>";
        return $result;
    }

    /**
     * Renders a new model placeholde, which once clicked will create a new model
     * TODO. This block should somehow be merged with the renderModel.
     */
    private function renderNew()
    {
        $model = Yii::createObject($this->modelClass);

        $result = '<li class="blockmodel new">';
        $result .= '<div class="new_overlay"></div>';

        // ActiveForm::begin is directly echoed out, so we have to use output buffer.
        ob_start();
        $form = ActiveForm::begin(['action' => ['create'], 'options' => ['data-action-update' => Url::to(['update', 'id' => -1]), 'data-id-attribute' => $this->id_attribute]]);
        $result .= ob_get_flush();

        // Attributes
        $result .= '<div>';
        foreach ($this->columns as $column)
        {
            $options = [];

            // If the user specified an order field and its the current one then
            // we add an order class to the field container for javascript atomation
            // purposes.
            if ($column['attribute'] == $this->order_by) {
                $options['class'] = 'order';
            }

            if (isset($column['widget'])) {
                $result .= $form->field($model, $column['attribute'], ['options' => $options])->widget($column['widget'], ['options' => $column['widget_options']]);
            } else if ($column['format'] == 'image') {
                if (!isset($column['baseUrl'])) {
                    throw new InvalidConfigException('A column of type image must have a baseUrl set');
                }
                // Show the image if there is one already a value in this attribute
                $result .= Html::img($column['baseUrl'].$model->image, ['class' => 'image']);
                $result .= $form->field($model, $column['attribute'], ['options' => $options])->fileInput();
            } else {
                $result .= $form->field($model, $column['attribute'], ['options' => $options]);
            }
            //$result .= '<td>'.Html::activeLabel($model, $key).'</td>';
            //$result .= '<td>'.Html::activeInput('text', $model, $key).'</td>';
        }
        $result .= '</div>';

        // ActiveForm::end is directly echoed out, so we have to use output buffer.
        ob_start();
        ActiveForm::end();
        $result .= ob_get_flush();

        $result .= '</li>'; // .blockmodels.new
        // $result .= "<pre>\n".print_r($model->attributes, true)."\n</pre>";
        return $result;
    }

    /**
    * Registers the needed assets
    */
    public function registerAssets()
    {
        $view = $this->getView();
        BlockModelsAsset::register($view);
    }
}
