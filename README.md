yii2-knockout-list
===============

This is a widget that works much the same as the default ListView provided by Yii2 but with rendering done in KnockoutJS so that extending it with javascript/KnockoutJS is a breeze.

```sh
php composer.phar require --prefer-dist damiandennis/yii2-knockout-list
```

This widget requires a few steps to get it functional. Firstly id and dataProvider must be provided in the controller, id is compulsory unlike other yii widgets as it needs an identity and data for the callback.

Example Controller

```php
use damiandennis\knockoutlist\KnockoutList;
class SiteController extends Controller
{
    public function actionIndex()
    {
        $model = new LocationSearch();
        $id = 'my-widget';
        $dataProvider = $model->search(Yii::$app->request->queryParams);
        /* $filter = function($row) {
        *      $newRow = $row->attributes; //This is default, relations etc need to be added here
        *      $newRow['Country'] = $row->country->attributes;
        *      return $newRow;
        *  }
        */
        KnockoutList::queryJsonResponse($id, $dataProvider/*, $filter*/);
        return $this->render('index', [
            'id' => $id, 
            'dataProvider' => $dataProvider,
            //'filter' => $filter,
        ]);
    }
}
```

Example Index
```php
use damiandennis\knockoutlist\KnockoutList;
echo KnockoutList::widget([
    'id' => $id, // The id from the controller
    'dataProvider' => $dataProvider, //The dataProvider from the controller
    'templates' => [ // These can be copied from the src/widgets/views
        //'summary' This is the summary of the page
        'items' => '_items' //If this is not included the default is the primary key.
        //'pager' This is the pager of the page.
        //'sorter' This is the sorter layout.
        //'empty' This is the empty result layout.
    ],
    //'layout' => '{summary}{items}{pager}{empty}', The layout rendering order.
    //'filter' => $filter, //The filter from the controller
    /**
     * Whether to bind this ko to the render section of html, this is mainly if you want to extend this as a bigger      * part of a project. If bindings are applied they are wrapped with no bind so they do not interfer with      
     * applications that bind to the whole page. Also the view can be access via yii.knockoutlist.listView.{yourid}
     * it is wrapped with a with binding to the id of the widget. data-bind="with: yourid"
     */
    //'applyBindings' => true, 
    /**
     * This provides the ability to extend the default models as they are in a private namespace to prevent
     * interference with other KnockoutList widgets on the same page. ListView is the pagination and the outer
     * section. ItemModel is the individual rows.
     */
    'extendModels' => "
        ko.utils.extend(ItemModel.prototype, {
            extend: function() {
                this.hello = ko.observable(this.loadedData.name);
                this.clickMe = function() {
                    alert('hello');
                };
            }
        });
        ko.utils.extend(ListView.prototype, {
            extend: function() {
                this.name2 = 'hello';
            }
        });
    ",
    //'noScriptText' => "This section requires Javascript to be enabled.", // When JS is disabled.
    //'async' => false, // Loads query after page load.
    //'autoObservables' => true, // All data passed through to ItemModel with be initialised with an observable.
    //'usePushState' => false, // This is mainly used for single pages apps that want urls like pajax.
]);
```

Example _items.php

```php
<!-- ko foreach: { data: items, as: 'item' } -->
<div data-bind="attr {'data-key': item.id}">
    <div data-bind="text: item.id"></div>
    <div data-bind="text: item.hello"></div>
    <button data-bind="click: item.clickMe">Click Me</button>
</div>
<!-- /ko -->
```
