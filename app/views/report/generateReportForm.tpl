{extends file="main.tpl"}
{block name="content"}
    <form method="post" action="{$conf->action_url}{$action}" class="padding-top-1-5 margin-bottom-1">
        <div class="row gtr-uniform">
            <div class="col-12 col-12-xsmall">
                Wybierz okres, dla którego chcesz wygenerować raport
            </div>
            <div class="col-1 report-label">
                Od:
            </div>
            <div class="col-3 col-4-xlarge col-6-medium">
                <input class="datepicker" type="text" name="date_from" id="date_from" placeholder="Data od">
            </div>
            <div class="col-8 col-7-xlarge col-5-medium"></div>
            <div class="col-1 report-label">
                Do:
            </div>
            <div class="col-3 col-4-xlarge col-6-medium">
                <input class="datepicker" type="text" name="date_to" id="date_to" placeholder="Data do">
            </div>
            <div class="col-8 col-7-xlarge col-5-medium"></div>
            <div class="col-2 col-12-medium">
                <button type="submit" class="primary fit">Wygeneruj</button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-2 col-12-medium">
            <a class="button fit" href="{$conf->action_url}showReports">Powrót</a>
        </div>
    </div>
    <script src="{$conf->assets_url}js/datepicker/datepicker.min.js"></script>
    <script src="{$conf->assets_url}js/datepicker/generate_report.js"></script>
{/block}
