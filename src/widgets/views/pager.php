<?php
/**
 * Created by PhpStorm.
 * User: damian
 * Date: 30/11/14
 * Time: 5:02 PM
 */
?>
<ul class="pagination">
    <li class="prev">
        <a data-bind="attr: { href: prevPage, 'data-page': prevPage }, click: changePage">&laquo;</a>
    </li>
    <!-- ko foreach: pages -->
    <li data-bind=" attr: {class: $data.pageUrl == $parent.selfPage() ? 'active' : null}">
        <a data-bind="text: $data.pageNo, attr: { href: $data.pageUrl, 'data-page': $data.pageNo }, click: $parent.changePage"></a>
    </li>
    <!-- /ko -->
    <li class="next">
        <a data-bind="attr: { href: nextPage, 'data-page': nextPage }, click: changePage">&raquo;</a>
    </li>
</ul>