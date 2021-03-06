{extends file="main.tpl"}
{block name="content"}
<div class="table-wrapper entries_table">
    <table>
        <thead>
        <tr>
            <th>Imię</th>
            <th>Nazwisko</th>
            <th>Email</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach $recipients as $recipient}
            <tr>
                <td>{$recipient["first_name"]}</td>
                <td>{$recipient["last_name"]}</td>
                <td>{$recipient["email"]}</td>
                <td class="table-buttons">
                    <a href="{$conf->action_url}editRecipient&recipient_uuid={$recipient["uuid"]}" class="fas fa-edit"><span class="label"></span></a>
                    <form class="delete-entry-button-form" method="post" action="{$conf->action_url}deleteRecipient">
                        <input type="hidden" value="{$recipient["uuid"]}" name="recipient_uuid">
                        <button class="far fa-trash-alt entry-button"><span class="label"></span></button>
                    </form>
                </td>
            </tr>
        {/foreach}
    </table>
    <a href="{$conf->action_url}addRecipient" class="button">Dodaj odbiorcę</a>
</div>
{/block}
