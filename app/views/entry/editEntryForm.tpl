{extends file="main.tpl"}
{block name="content"}
    <form method="post" action="{$conf->action_url}{$action}&entry_uuid={$entry["uuid"]}" class="padding-top-1-5 margin-bottom-1">
        <div class="row gtr-uniform">
            <div class="col-12 col-12-xsmall">
                <input type="text" name="place" id="place" placeholder="Miejsce" value="{$entry["place"]}">
            </div>
            <div class="col-2 col-4-xlarge col-6-medium">
                <input class="datepicker" type="text" name="date_from" id="date_from" placeholder="Data od" value="{$entry["from_date"]|substr:0:10}">
            </div>
            <div class="col-1 col-2-xlarge col-3-medium">
                <input class="hour_picker" type="text" name="time_from" id="time_from" placeholder="G:M" value="{$entry["from_date"]|substr:11:5}">
            </div>
            <div class="col-9 col-4-xlarge col-0-medium"></div>
            <div class="col-2 col-4-xlarge col-6-medium">
                <input class="datepicker" type="text" name="date_to" id="date_to" placeholder="Data do" value="{$entry["to_date"]|substr:0:10}">
            </div>
            <div class="col-1 col-2-xlarge col-3-medium">
                <input class="hour_picker" type="text" name="time_to" id="time_to" placeholder="G:M" value="{$entry["to_date"]|substr:11:5}">
            </div>
            <div class="col-9 col-4-xlarge col-0-medium"></div>
            <div class="col-12 col-12-small">
                <input type="checkbox" id="driver" name="driver" value="true" {if $entry["was_driver"]}checked{/if}>
                <label for="driver">Kierowca</label>
            </div>
            <div class="col-12 col-12-small">
                <input type="checkbox" id="subsistence_allowance" name="subsistence_allowance" value="true" {if $entry["subsistence_allowance"]}checked{/if}>
                <label for="subsistence_allowance">Dieta</label>
            </div>
            <div class="col-12 col-12-small">
                <input type="checkbox" id="day_off" name="day_off" value="true" {if $entry["day_off"]}checked{/if}>
                <label for="day_off">Dzień wolny</label>
            </div>
            <div class="col-2 col-12-medium">
                <button type="submit" class="primary fit">Zatwierdź</button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-2 col-12-medium">
            <a class="button fit" href="{$conf->action_url}dashboard">Powrót</a>
        </div>
    </div>
    <script src="{$conf->assets_url}js/datepicker/picker.js"></script>
    <script src="{$conf->assets_url}js/datepicker/datepicker.min.js"></script>
    <script src="{$conf->assets_url}js/datepicker/add_entry_form.js"></script>
{/block}

