{extends file="main.tpl"}
{block name="content"}
    <div class="table-wrapper entries_table">
        <table>
            <thead>
            <tr>
                <th>Data utworzenia</th>
                <th>Nazwa</th>
                <th>Okres</th>
            </tr>
            </thead>
            <tbody>
            {foreach $reports as $report}
                <tr>
                    <td>{$report["creation_date"]|substr:0:16}</td>
                    <td>{$report["filename"]}</td>
                    <td>{$report["from_date"]|substr:0:10} - {$report["to_date"]|substr:0:10}</td>
                    <td class="table-buttons">
                        <a href="{$conf->action_url}downloadReport&report_uuid={$report["uuid"]}" class="fas fa-arrow-circle-down"><span class="label"></span></a>
                        <form class="delete-entry-button-form" method="post" action="{$conf->action_url}deleteReport">
                            <input type="hidden" value="{$report["uuid"]}" name="report_uuid">
                            <button class="far fa-trash-alt entry-button"><span class="label"></span></button>
                        </form>
                    </td>
                </tr>
            {/foreach}
        </table>
        <a href="{$conf->action_url}generateReport" class="button">Wygeneruj nowy raport</a>
    </div>
{/block}
