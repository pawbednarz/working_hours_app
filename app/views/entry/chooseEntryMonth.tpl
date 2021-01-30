{extends file="main.tpl"}
{block name="content"}
    <form method="post" action="{$conf->action_url}{$action}" class="padding-top-1-5 margin-bottom-1">
        <div class="row gtr-uniform">
            <div class="col-2 col-2-xlarge col-3-medium">
                <input class="datepicker" type="text" name="date_from" id="date_from" placeholder="RRRR-MM">
            </div>
            <div class="col-10 col-4-xlarge col-0-medium"></div>
            <div class="col-2 col-12-medium">
                <button type="submit" class="primary fit">Wybierz</button>
            </div>
        </div>
    </form>
    <script src="{$conf->assets_url}js/datepicker/picker.js"></script>
    <script src="{$conf->assets_url}js/datepicker/choose_month.js"></script>
{/block}
