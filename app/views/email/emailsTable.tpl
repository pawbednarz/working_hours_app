{extends file="main.tpl"}
{block name="content"}
    <div class="table-wrapper entries_table">
        <table>
            <thead>
            <tr>
                <th>Data wysłania</th>
                <th>Temat</th>
                <th>Odbiorcy</th>
                <th>Nazwa pliku raportu</th>
                <th>Zakres raportu</th>
            </tr>
            </thead>
            <tbody>
            {foreach $emails as $email}
                <tr>
                    <td>{$email["sent_date"]|substr:0:16}</td>
                    <td>{$email["subject"]}</td>
                    <td>
                        {$email["recipient"]["first_name"]} {$email["recipient"]["last_name"]} ({$email["recipient"]["email"]})
                    </td>
                    <td>
                        {$email["report"]["filename"]}
                    </td>
                    <td>
                        {$email["report"]["from_date"]|substr:0:10} - {$email["report"]["to_date"]|substr:0:10}
                    </td>
                    <td class="table-buttons">
                        <a href="{$conf->action_url}showEmail&email_uuid={$email["uuid"]}" class="fas fa-eye"><span class="label"></span></a>
                        <form class="delete-entry-button-form" method="post" action="{$conf->action_url}deleteEmail">
                            <input type="hidden" value="{$email["uuid"]}" name="email_uuid">
                            <button class="far fa-trash-alt entry-button"><span class="label"></span></button>
                        </form>
                    </td>
                </tr>
            {/foreach}
        </table>
        <a href="{$conf->action_url}sendEmail" class="button">Wyślij nową wiadomość</a>
    </div>
{/block}
