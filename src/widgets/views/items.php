<?php
/**
 * Created by PhpStorm.
 * User: damian
 * Date: 30/11/14
 * Time: 5:02 PM
 */
?>
<!-- ko foreach: { data: items, as: 'item' } -->
<div data-bind="attr {'data-key': item.id}">
    <div data-bind="text: item.id"></div>
</div>
<!-- /ko -->
