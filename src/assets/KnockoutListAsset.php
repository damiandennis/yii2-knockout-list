<?php
/**
 * User: Damian
 * Date: 19/05/14
 * Time: 6:05 AM
 */

namespace damiandennis\knockoutlist;
use yii\web\AssetBundle;

class KnockoutListAsset extends AssetBundle
{

    public function init()
    {
        $this->setSourcePath(__DIR__ . '/../../assets/js');
        $this->setupAssets('js', ['knockout-list']);
        parent::init();
    }
}