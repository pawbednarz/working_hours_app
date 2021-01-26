{extends file="main.tpl"}
{block name="content"}
    <div class="table-wrapper entries_table">
        <table>
            <thead>
            <tr>
                <th>Data wysłania</th>
                <th>Temat</th>
                <th>Odbiorcy</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach $emails as $email}
                <tr>
                    <td>{$email["sent_date"]}</td>
                    <td>{$email["name"]}</td>
                    <td>
                        {foreach $email["recipient"] as $recipient}
{*                            {$recipient["first_name"] . $recipient["last_name"]}<br>*}
                        {/foreach}</td>
                    <td class="table-buttons">
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
