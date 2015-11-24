<?php
namespace jesuso\blockmodels;

use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use kartik\icons\Icon;

class BlockModels extends \yii\base\Widget
{
    public $dataProvider;
    public $columns;

    public function init()
    {
        parent::init();
        // Load kartic icons
        Icon::map(Yii::$app->view);

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
        $content = '<ul class="blockmodels">';

        foreach ($models as $index => $model) {
            $key = $keys[$index];
            $rows[] = $this->renderModel($model, $key, $index);
        }

        $content .= implode("\n", $rows);
        $content .= '</ul>';

        return $content;
    }

    private function renderModel($model, $key, $index)
    {
        ob_start();
        echo'<li class="blockmodel">';
        $form = ActiveForm::begin();

        // Header
        echo '<div class="row">';
        echo Icon::show('arrows', ['class' => 'handle fa-2x']);
        echo Icon::show('times', ['class' => 'delete fa-2x']);
        echo '</div>';

        // Attributes
        echo '<div><table><tbody>';
        foreach ($model->attributes as $key => $value)
        {
            if (in_array($key, $this->columns)) {
                echo '<tr>';
                echo $form->field($model, $key);
                //echo '<td>'.Html::activeLabel($model, $key).'</td>';
                //echo '<td>'.Html::activeInput('text', $model, $key).'</td>';
                echo '</tr>';
            }
        }
        echo '</tbody></table></div>';

        ActiveForm::end();
        echo '</li>'; // .blockmodels
        // $result .= "<pre>\n".print_r($model->attributes, true)."\n</pre>";
        return ob_get_clean();
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
