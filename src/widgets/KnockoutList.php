<?php
/**
 * Created by PhpStorm.
 * User: damian
 * Date: 30/11/14
 * Time: 9:38 AM
 */
namespace damiandennis\knockoutlist;

use yii\base\Widget;

/**
 * Created by PhpStorm.
 * User: damian
 * Date: 6/10/14
 * Time: 8:01 AM
 */
class KnockoutListWidget extends Widget
{
    public $dataProvider;
    public $emptyText;
    public $template = [];

    /**
     * Initializes the view.
     */
    public function init()
    {
        if (!$this->template) {
            $this->template = [
                'summary' => $this->render('summary'),
                'body' => $this->render('body'),
                'pager' => $this->render('pager'),
                'sorter' => $this->render('sorter'),
            ];
        }
        if ($this->dataProvider === null) {
            throw new InvalidConfigException('The "dataProvider" property must be set.');
        }
        if ($this->emptyText === null) {
            $this->emptyText = Yii::t('yii', 'No results found.');
        }
    }

    public function run()
    {

    }
}
