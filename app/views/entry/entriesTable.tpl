<div class="table-wrapper entries_table">
    <table>
        <thead>
        <tr>
            <th>Dzień</th>
            <th>Miejsce</th>
            <th>Od - Do</th>
            <th>Godziny</th>
            <th>Kierowca</th>
            <th>Dieta</th>
            <th>Wolne</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach $entries as $entry}
        <tr>
            <td>
                {$entry["from_date"]|substr:8:2}
{*                {if $entry["from_date"]|substr:0:10 === $entry["to_date"]|substr:0:10 or $entry["to_date"] == ""}*}
{*                    {$entry["from_date"]|substr:0:10}*}
{*                {elseif $entry["to_date"] != ""}*}
{*                    {$entry["from_date"]|substr:0:10}<br>({$entry["to_date"]|substr:0:10})*}
{*                {/if}*}
            </td>
            <td>{($entry["place"]) ? $entry["place"] : "---"}</td>
            <td>
                {if !$entry["day_off"]}
                    {$entry["from_date"]|substr:11:5} - {$entry["to_date"]|substr:11:5}
                    {if $entry["from_date"]|substr:0:10 lt $entry["to_date"]|substr:0:10}
                        ({$entry["to_date"]|substr:5:5})
                    {/if}
                {else}
                    ---
                {/if}
            </td>
            <td>{($entry["hours"]) ? $entry["hours"] : "---"}</td>
            <td>{($entry["was_driver"]) ? "Tak" : "Nie"}</td>
            <td>{($entry["subsistence_allowance"]) ? "Tak" : "Nie"}</td>
            <td>{($entry["day_off"]) ? "Tak" : "Nie"}</td>
            <td class="table-buttons">
                <a href="#" class="fas fa-edit"><span class="label"></span></a>
                <form class="delete-entry-button-form" method="post" action="{$conf->action_url}deleteEntry">
                    <input type="hidden" value="{$entry["uuid"]}" name="entry_uuid">
                    <button class="far fa-trash-alt entry-button"><span class="label"></span></button>
                </form>
            </td>
        </tr>
        {/foreach}
        <tr>
                <td></td>
                <td></td>
                <td class="align-right">SUMA:</td>
                <td>
                    {assign var="result" value="0"}
                    {foreach $entries as $entry}
                        {assign var="result" value=$result+$entry["hours"]}
                    {/foreach}
                    {$result}
                </td>
                <td></td>
                <td></td>
                <td></td>
        </tr>
    </table>
    <a href="{$conf->action_url}addEntry" class="button">Dodaj wpis</a>
</div>
