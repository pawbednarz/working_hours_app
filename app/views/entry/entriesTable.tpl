<div class="table-wrapper entries_table">
    <table>
        <thead>
        <tr>
            <th>Dzień</ht>
            <th>Miejsce</th>
            <th>Od</th>
            <th>Do</th>
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
            <td>{($entry["from_date"] == $entry["to_date"]) ? $entry["from_date"] : "NULL XD"}</td>
            <td>{($entry["place"]) ? $entry["place"] : "---"}</td>
            <td>{$entry["from_date"]}</td>
            <td>{($entry["to_date"]) ? $entry["to_date"] : "---"}</td>
            <td>{($entry["hours"]) ? $entry["hours"] : "---"}</td>
            <td>{($entry["was_driver"]) ? "Tak" : "Nie"}</td>
            <td>{($entry["subsistence_allowance"]) ? "Tak" : "Nie"}</td>
            <td>{($entry["day_off"]) ? "Tak" : "Nie"}</td>
            <td class="table-buttons">
                <a href="#" class="fas fa-edit"><span class="label"></span></a>
                <form class="delete-entry-button-form" method="post" action="{$conf->action_root}deleteEntry">
                    <input type="hidden" value="{$entry["uuid"]}" name="entry_uuid">
                    <button class="far fa-trash-alt entry-button"><span class="label"></span></button>
                </form>
            </td>
        </tr>
        {/foreach}
    </table>
    <a href="{$conf->action_root}addEntry" class="button">Dodaj wpis</a>
</div>
