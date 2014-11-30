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
        <a data-bind="attr: {href: prevUrl, data-page: nextUrl}">&laquo;</a>
    </li>
    <!-- ko foreach: rows -->
    <li>
        <a href="/site/test?page=1" data-page="0">1</a>
    </li>
    <!-- /ko -->
    <li class="next">
        <a href="/site/test?page=3" data-page="2">&raquo;</a>
    </li>
</ul>