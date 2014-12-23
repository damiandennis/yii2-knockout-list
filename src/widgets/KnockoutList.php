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
use yii\helpers\BaseUrl;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;

class KnockoutList extends Widget
{
    public $dataProvider;
    public $templates = [];
    public $layout = '{summary}{items}{pager}{empty}';
    public $applyBindings = true;
    public $filter;
    public $noScriptText = "This section requires Javascript to be enabled.";
    public $extendModels;
    public $async = false;
    public $autoObservables = true;
    public $usePushState = false;

    /**
     * @inheritdoc
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
            'empty' => $this->render('empty'),
        ];

        $this->templates = array_merge($templates, $this->templates);

        if ($this->dataProvider === null) {
            throw new InvalidConfigException('The "dataProvider" property must be set.');
        }

        if ($this->filter === null || !is_object($this->filter)) {
            $this->filter = function ($model) {
                return $model->attributes;
            };
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = preg_replace_callback("/{\\w+}/", function ($matches) {
            $content = $this->renderSection($matches[0]);
            return $content === false ? $matches[0] : $content;
        }, $this->layout);

        if (!$this->async) {
            $data = self::getData($this->id, $this->dataProvider, $this->filter);
        } else {
            $data = [
                'id' => $this->getId(),
                'async' => $this->async,
            ];
        }

        $data['applyBindings'] = $this->applyBindings;
        $data['autoObservables'] = $this->autoObservables;
        $data['usePushState'] = $this->usePushState;

        if ($this->extendModels) {
            $data['extendModels'] = new JsExpression($this->extendModels);
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

        echo $this->render('templates');

        if ($this->applyBindings) {
            echo "<!-- ko stopBinding: true -->";
        } else {
            echo "<!-- ko with: {$this->id} -->";
        }
        echo '<div id="'.$this->id.'" style="display: none" data-bind="visible: true">';
        echo $content;
        echo '</div>';
        echo "
            <noscript>
                <p>{$this->noScriptText}</p>
            </noscript>
        ";
        if ($this->applyBindings) {
            echo "<!-- /ko -->";
        } else {

        }
    }

    /**
     * @param string $id The id of the widget
     * @param \yii\data\BaseDataProvider $dataProvider The dataprovider for the widget
     * @param \Closure $filter The filter to manipulate the data.
     * @return array The data required to create the list and pagination.
     */
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

        $sort = $dataProvider->getSort();

        if (!($sort === false || empty($sort->attributes) || $dataProvider->getCount() <= 0)) {
            $attributes = array_keys($sort->attributes);
            $data['sort'] = [];
            foreach ($attributes as $name) {
                $data['sort'][] = [
                    'name' => $name,
                    'link' => $sort->createUrl($name),
                ];
            }
        }

        $models = $dataProvider->getModels();
        if ($models) {
            $data['labels'] = $models[0]->attributeLabels();
        }
        $controller = Yii::$app->controller->id;

        foreach ($models as $key => $model) {
            $data['items'][$key] = $filter ? $filter($model) : $model->attributes;
            $data['items'][$key]['actions'] = [
                'viewUrl' => BaseUrl::toRoute(["{$controller}/view", 'id' => $model->primaryKey]),
                'viewTitle' => Yii::t('app', 'View'),
                'updateUrl' => BaseUrl::toRoute(["{$controller}/update", 'id' => $model->primaryKey]),
                'updateTitle' => Yii::t('app', 'Update'),
                'deleteUrl' => BaseUrl::toRoute(["{$controller}/delete", 'id' => $model->primaryKey]),
                'deleteTitle' => Yii::t('app', 'Delete'),
            ];
        }

        return $data;
    }

    /**
     * @param string $id The id of the widget
     * @param \yii\data\BaseDataProvider $dataProvider The dataprovider for the widget
     * @param \Closure|null $filter The filter to manipulate the data
     * @throws \yii\base\ExitException
     */
    public static function queryJsonResponse($id, $dataProvider, $filter = null)
    {
        if (isset($_GET['ajax']) && $_GET['ajax'] == $id) {
            header('Content-type: application/json');
            $data = self::getData($id, $dataProvider, $filter);
            echo Json::encode($data);
            Yii::$app->end();
        }
    }

    /**
     * @param string $name The template to render.
     * @return bool|string The html template or false if template does not exist.
     */
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
            case '{empty}':
                return $this->templates['empty'];
            default:
                return false;
        }
    }
}
