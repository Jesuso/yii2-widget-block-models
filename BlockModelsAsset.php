<?php
namespace jesuso\blockmodels;

class BlockModelsAsset extends \yii\web\AssetBundle
{
    const BM_ASSET = 'BM_ASSET';

    public $js = self::BM_ASSET;
    public $css = self::BM_ASSET;
    public $sourcePath;
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\jui\JuiAsset',
    ];

    public function init()
    {
        parent::init();

        $this->sourcePath = __DIR__ . '/assets';

        $this->setupAssets('css', ['css/blockmodels']);
        $this->setupAssets('js', ['js/blockmodels']);
    }

    protected function setupAssets($type, $files = [])
    {
        if ($this->$type === self::BM_ASSET) {
            $srcFiles = [];
            $minFiles = [];
            foreach ($files as $file) {
                $srcFiles[] = "{$file}.{$type}";
                $minFiles[] = "{$file}.min.{$type}";
            }
            $this->$type = YII_DEBUG ? $srcFiles : $minFiles;
        }
    }
}
