<?php
/**
 * Created by PhpStorm.
 * User: damian
 * Date: 30/11/14
 * Time: 9:38 AM
 */
namespace damiandennis\knockoutlist;

use damiandennis\knockoutjs\KnockoutAsset;
use Yii;
use yii\base\Widget;
use yii\helpers\Json;
use yii\web\View;

/**
 * Created by PhpStorm.
 * User: damian
 * Date: 6/10/14
 * Time: 8:01 AM
 */
class KnockoutList extends Widget
{
    public $dataProvider;
    public $emptyText;
    public $templates = [];
    public $layout = '{summary}{items}{pager}{sorter}';

    /**
     * Initializes the view.
     */
    public function init()
    {
        if (!$this->templates) {
            $this->templates = [
                'summary' => $this->render('summary'),
                'items' => $this->render('items'),
                'pager' => $this->render('pager'),
                'sorter' => $this->render('sorter'),
                'empty' => 'Nothing found.',
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
        $content = preg_replace_callback("/{\\w+}/", function ($matches) {
            $content = $this->renderSection($matches[0]);
            return $content === false ? $matches[0] : $content;
        }, $this->layout);

        $data = [
            'id' => $this->id
        ];

        $data['count'] = $this->dataProvider->getCount();

        if (($pagination = $this->dataProvider->getPagination()) !== false) {
            $data['totalCount'] = $this->dataProvider->getTotalCount();
            $data['begin'] = $pagination->getPage() * $pagination->pageSize + 1;
            $data['end'] = $data['begin'] + $data['count'] - 1;
            if ($data['begin'] > $data['end']) {
                $data['begin'] = $data['end'];
            }
            $data['page'] = $pagination->getPage() + 1;
            $data['pageCount'] = $pagination->pageCount;
        } else {
            $data['totalCount'] = $this->dataProvider->getTotalCount();
            $data['pageCount'] = 1;
            $data['page'] = 1;
            $data['begin'] = $data['page'] = $data['pageCount'] = 1;
            $data['end'] = $data['totalCount'] = $data['count'];
        }

        $view = $this->getView();
        KnockoutAsset::register($view);
        KnockoutListAsset::register($view);
        $data = JSON::encode($data);
        $view->registerJs(
            "$(function() {
                yii.knockoutlist.setup({$data});
            });
            \n",
            View::POS_END
        );
        echo $content;



    }

    public function renderSection($name)
    {
        switch ($name) {
            case '{summary}':
                return $this->templates['summary'];
            case '{items}':
                return $this->templates['items'];
            case '{pager}':
                return $this->templates['pager'];
            case '{sorter}':
                return $this->templates['sorter'];
            default:
                return false;
        }
    }
}
