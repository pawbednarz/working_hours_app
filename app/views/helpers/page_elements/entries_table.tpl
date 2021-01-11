<div class="table-wrapper entries_table">
    <table>
        <thead>
        <tr>
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
            <td>{($entry["place"]) ? $entry["place"] : "---"}</td>
            <td>{$entry["from_date"]}</td>
            <td>{($entry["to_date"]) ? $entry["to_date"] : "---"}</td>
            <td>{($entry["hours"]) ? $entry["hours"] : "---"}</td>
            <td>{($entry["was_driver"]) ? "Tak" : "Nie"}</td>
            <td>{($entry["subsistence_allowance"]) ? "Tak" : "Nie"}</td>
            <td>{($entry["day_off"]) ? "Tak" : "Nie"}</td>
            <td class="table-buttons">
                <a href="#" class="fas fa-edit"><span class="label"></span></a>
                <a href="#" class="far fa-trash-alt"><span class="label"></span></a>
            </td>
        </tr>
        {/foreach}
    </table>
    <a href="{$conf->action_root}addEntry" class="button">Dodaj wpis</a>
</div>
