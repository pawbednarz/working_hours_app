{include file="header.tpl"}
<div id="wrapper">
    <!-- Main -->
    <div id="main">
        <div class="inner">

            {include file="page_header.tpl"}
            <h4 class="description-header">{$description}</h4>
            {include file="entries_table.tpl"}
{*      TODO check out why messages are not working here      *}
            {include file="messages.tpl"}
        </div>
    </div>
    {include file="menu.tpl"}

</div>
{include file="footer.tpl"}
