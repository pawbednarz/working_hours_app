{extends file="mainWithoutMenu.tpl"}
{block name="content"}
<div class="table-wrapper entries_table">
    <table>
        <thead>
        <tr>
            <th>Imię</th>
            <th>Nazwisko</th>
            <th>Email</th>
            <th>Rola</th>
            <th>Aktywny</th>
        </tr>
        </thead>
        <tbody>
        {foreach $users as $user}
            <tr>
                <td>{$user["first_name"]}</td>
                <td>{$user["last_name"]}</td>
                <td>{$user["email"]}</td>
                <td>{$user["role"]}</td>
                <td>{($user["is_active"]) ? "Tak" : "Nie"}</td>
                <td class="table-buttons">
                    <a href="{$conf->action_url}editUser&user_uuid={$user["uuid"]}" class="fas fa-edit"><span class="label"></span></a>
                </td>
            </tr>
        {/foreach}
    </table>
    <div class="row">
        <div class="col-2 col-12-medium">
            <a href="{$conf->action_url}addUser" class="button primary fit">Dodaj użytkownika</a>
        </div>
        <div class="col-10"></div>
        <div class="col-2 col-12-medium">
            <a class="button fit margin-top-1" href="{$conf->action_url}logout">Wyloguj</a>
        </div>
        <div class="col-10"></div>
    </div>
</div>
{/block}
