{include file="header.tpl"}
<div id="wrapper">
    <!-- Main -->
    <div id="main">
        <div class="inner">

            {include file="pageHeader.tpl"}
            {include file="usersTable.tpl"}
            {include file="messages.tpl"}
            <div class="row">
                <div class="col-2 col-12-medium">
                    <a class="button fit" href="{$conf->action_url}logout">Wyloguj</a>
                </div>
            </div>
        </div>
    </div>

</div>
{include file="footer.tpl"}
