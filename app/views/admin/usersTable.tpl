<div class="table-wrapper entries_table">
    <table>
        <thead>
        <tr>
            <th>Imię</th>
            <th>Nazwisko</th>
            <th>Email</th>
            <th>Rola</th>
        </tr>
        </thead>
        <tbody>
        {foreach $users as $user}
            <tr>
                <td>{$user["first_name"]}</td>
                <td>{$user["last_name"]}</td>
                <td>{$user["email"]}</td>
                <td>{$user["role"]}</td>
                <td class="table-buttons">
                    <a href="#" class="fas fa-edit"><span class="label"></span></a>
                    <form class="delete-entry-button-form" method="post" action="{$conf->action_root}deleteUser">
                        <input type="hidden" value="{$user["uuid"]}" name="user_uuid">
                        <button class="far fa-trash-alt entry-button"><span class="label"></span></button>
                    </form>
                </td>
            </tr>
        {/foreach}
    </table>
    <a href="{$conf->action_root}addUser" class="button">Dodaj użytkownika</a>
</div>
