{extends file="main.tpl"}
{block name="content"}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vanillajs-datepicker@1.1.1/dist/css/datepicker.min.css">
<form method="post" action="{$conf->action_url}{$action}" class="padding-top-1-5 margin-bottom-1">
    <div class="row gtr-uniform">
        <div class="col-12 col-12-xsmall">
            <input type="text" name="place" id="place" placeholder="Miejsce">
        </div>
            <div class="col-2 col-4-xlarge col-6-medium">
                <input class="datepicker" type="text" name="date_from" id="date_from" placeholder="Data od">
            </div>
            <div class="col-1 col-2-xlarge col-3-medium">
                <input class="hour_picker" type="number" name="time_from_hour" id="time_from_hour" min="0" max="23" placeholder="G">
            </div>
            <div class="col-1 col-2-xlarge col-3-medium">
                <input class="hour_picker" type="number" name="time_from_minute" id="time_from_minute" step="10" min="0" max="59" placeholder="M">
            </div>
            <div class="col-7 col-4-xlarge col-0-medium"></div>
            <div class="col-2 col-4-xlarge col-6-medium">
                <input class="datepicker" type="text" name="date_to" id="date_to" placeholder="Data do">
            </div>
            <div class="col-1 col-2-xlarge col-3-medium">
                <input class="hour_picker" type="text" name="time_to_hour" id="time_to_hour" min="0" max="23" placeholder="G">
            </div>
            <div class="col-1 col-2-xlarge col-3-medium">
                <input class="hour_picker" type="text" name="time_to_minute" id="time_to_minute" step="10" min="0" max="59" placeholder="M">
            </div>
        <div class="col-7 col-4-xlarge col-0-medium"></div>
        <div class="col-12 col-12-small">
            <input type="checkbox" id="driver" name="driver" value="true">
            <label for="driver">Kierowca</label>
        </div>
        <div class="col-12 col-12-small">
            <input type="checkbox" id="subsistence_allowance" name="subsistence_allowance" value="false">
            <label for="subsistence_allowance">Dieta</label>
        </div>
        <div class="col-12 col-12-small">
            <input type="checkbox" id="day_off" name="day_off" value="false">
            <label for="day_off">Dzień wolny</label>
        </div>
        <div class="col-2 col-12-medium">
            <button type="submit" class="primary fit">Dodaj</button>
        </div>
    </div>
</form>
<div class="row">
    <div class="col-2 col-12-medium">
        <a class="button fit" href="{$conf->action_url}dashboard">Powrót</a>
    </div>
</div>
<script src="{$conf->assets_url}js/datepicker/datepicker.min.js"></script>
<script src="{$conf->assets_url}js/datepicker/add_entry_form.js"></script>
{/block}