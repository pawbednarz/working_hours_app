<div class="table-wrapper entries_table">
    <table>
        <thead>
        <tr>
            <th>Nazwa szablonu</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach $templates as $template}
            <tr>
                <td>{$template["name"]}</td>
                <td class="table-buttons">
                    <a href="#" class="fas fa-edit"><span class="label"></span></a>
                    <form class="delete-entry-button-form" method="post" action="{$conf->action_url}deleteEmailTemplate">
                        <input type="hidden" value="{$template["uuid"]}" name="email_template_uuid">
                        <button class="far fa-trash-alt entry-button"><span class="label"></span></button>
                    </form>
                </td>
            </tr>
        {/foreach}
    </table>
    <a href="{$conf->action_url}addEmailTemplate" class="button">Dodaj szablon</a>
</div>
