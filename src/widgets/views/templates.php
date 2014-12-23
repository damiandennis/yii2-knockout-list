<?php
/**
 * Created by PhpStorm.
 * User: damian
 * Date: 22/12/14
 * Time: 7:14 PM
 */
?>
<script type="text/html" id="actionColumn">
    <a data-bind="attr: {href: $data.viewUrl, title: $data.viewTitle}">
        <span class="glyphicon glyphicon-eye-open"></span>
    </a>
    <a data-bind="attr: {href: $data.updateUrl, title: $data.updateTitle}">
        <span class="glyphicon glyphicon-pencil"></span>
    </a>
    <a data-bind="attr: {href: $data.deleteUrl, title: $data.deleteTitle}" data-confirm="Are you sure you want to delete this item?">
        <span class="glyphicon glyphicon-trash"></span>
    </a>
</script>