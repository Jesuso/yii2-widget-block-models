<?php
namespace jesuso\blockmodels;

use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;

class BlockModels extends \yii\base\Widget
{
    public $dataProvider;
    public $columns;

    public function init()
    {
        parent::init();

        $this->registerAssets();

        if ($this->dataProvider === null) {
            throw new InvalidConfigException('The "dataProvider" property must be set.');
        }
    }

    public function run()
    {
        $models = array_values($this->dataProvider->getModels());
        $keys = $this->dataProvider->getKeys();
        $rows = [];

        foreach ($models as $index => $model) {
            $key = $keys[$index];
            $rows[] = $this->renderModel($model, $key, $index);
        }

        return implode("\n", $rows);
    }

    private function renderModel($model, $key, $index)
    {
        $result = '<div class="blockmodels">';

        // Header
        $result .= '<div class="row">';
        $result .= '<i class="fa fa-bars">Drag</i>';
        $result .= '<i class="fa fa-times">Delete</i>';
        $result .= '</div>';

        // Attributes
        foreach ($model->attributes as $key => $value)
        {
            $result .= '<div>';
            $result .= Html::activeLabel($model, $key);
            $result .= Html::activeInput('text', $model, $key);
            $result .= '</div>';
        }

        $result .= '</div>';
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
