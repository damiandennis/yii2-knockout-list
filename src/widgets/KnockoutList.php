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
    public $applyBindings = true;
    public $filter;
    public $noScriptText = "This section requires Javascript to be enabled.";

    /**
     * Initializes the view.
     */
    public function init()
    {
        foreach ($this->templates as $key => $template) {
            if (is_string($template)) {
                $this->templates[$key] = $this->getView()->render($template);
            } else {
                $this->templates[$key] = call_user_func($template);
            }
        }
        $templates = [
            'summary' => $this->render('summary'),
            'items' => $this->render('items'),
            'pager' => $this->render('pager'),
            'sorter' => $this->render('sorter'),
            'empty' => 'Nothing found.',
        ];

        $this->templates = array_merge($templates, $this->templates);

        if ($this->dataProvider === null) {
            throw new InvalidConfigException('The "dataProvider" property must be set.');
        }
        if ($this->emptyText === null) {
            $this->emptyText = Yii::t('yii', 'No results found.');
        }
        if ($this->filter === null || !is_object($this->filter)) {
            $this->filter = function ($model) {
                return $model->attributes;
            };
        }
    }

    public function run()
    {
        $content = preg_replace_callback("/{\\w+}/", function ($matches) {
            $content = $this->renderSection($matches[0]);
            return $content === false ? $matches[0] : $content;
        }, $this->layout);

        $data = self::getData($this->id, $this->dataProvider, $this->filter);

        $data['applyBindings'] = $this->applyBindings;

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

        if ($this->applyBindings) {
            echo "<!-- ko stopBinding: true -->";
        }
        echo '<div id="'.$this->id.'" style="display: none" data-bind="visible: true">';
        echo $content;
        echo "</div>";
        echo "
            <noscript>
                <p>{$this->noScriptText}</p>
            </noscript>
        ";
        if ($this->applyBindings) {
            echo "<!-- /ko -->";
        }
    }

    protected static function getData($id, $dataProvider, $filter)
    {
        $data = [
            'id' => $id
        ];

        $data['count'] = $dataProvider->getCount();

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $data['totalCount'] = $dataProvider->getTotalCount();
            $data['begin'] = $pagination->getPage() * $pagination->pageSize + 1;
            $data['end'] = $data['begin'] + $data['count'] - 1;
            if ($data['begin'] > $data['end']) {
                $data['begin'] = $data['end'];
            }
            $data['page'] = $pagination->getPage() + 1;
            $data['pageCount'] = $pagination->pageCount;
        } else {
            $data['totalCount'] = $dataProvider->getTotalCount();
            $data['pageCount'] = 1;
            $data['page'] = 1;
            $data['begin'] = $data['page'] = $data['pageCount'] = 1;
            $data['end'] = $data['totalCount'] = $data['count'];
        }

        $pagination = $dataProvider->getPagination();
        if ($pagination !== false) {
            $data['pageParam'] = $pagination->pageParam;
            $data['pageSizeParam'] = $pagination->pageSizeParam;
            $data['pageSizeLimit'] = $pagination->pageSizeLimit;
            $data['pageLinks'] = $pagination->getLinks();

            $currentPage = $pagination->getPage();
            $pageCount = $pagination->getPageCount();

            $maxButtonCount = 10;

            $beginPage = max(0, $currentPage - (int) ($maxButtonCount / 2));
            if (($endPage = $beginPage + $maxButtonCount - 1) >= $pageCount) {
                $endPage = $pageCount - 1;
                $beginPage = max(0, $endPage - $maxButtonCount + 1);
            }

            for ($i=$beginPage; $i<=$endPage; $i++) {
                $data['pages'][] = [
                    'pageNo' => $i+1,
                    'pageUrl' => $pagination->createUrl($i)
                ];
            }
        }

        foreach ($dataProvider->getModels() as $model) {
            $data['items'][] = $filter ? $filter($model) : $model->attributes;
        }

        return $data;
    }

    public static function queryJsonResponse($id, $response, $filter = null)
    {
        if (isset($_GET['ajax']) && $_GET['ajax'] == $id) {
            header('Content-type: application/json');
            $data = self::getData($id, $response, $filter);
            echo Json::encode($data);
            Yii::$app->end();
        }
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
