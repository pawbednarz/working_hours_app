<form method="post" action="{$conf->action_url}{$action}" class="padding-top-1-5 margin-bottom-1">
    <div class="row">
        <div class="col-3 col-0-small"></div>
        <div class="col-6 col-12-small row gtr-uniform">
            <div class="col-6 col-12-medium">
                <input type="text" name="first_name" id="first_name" placeholder="Imię" autocomplete="off">
            </div>
            <div class="col-6 col-12-medium">
                <input type="text" name="last_name" id="last_name" placeholder="Nazwisko" autocomplete="off">
            </div>
            <div class="col-6 col-12-medium">
                <input type="email" name="email" id="email" placeholder="Email" autocomplete="off">
            </div>
            <div class="col-6 col-12-medium">
                <select name="role">
                    <option value="user">Użytkownik</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div class="col-6 col-12-medium">
                <input type="password" name="password" id="password" placeholder="Hasło">
            </div>
            <div class="col-6 col-12-medium">
                <input type="password" name="password_repeat" id="password_repeat" placeholder="Powtórz hasło">
            </div>
            <div class="col-6 col-12-medium">
                <button type="submit" class="primary fit">Dodaj</button>
            </div>
            <div class="col-6 col-12-medium">
                <a class="button fit" href="{$conf->action_url}adminDashboard">Powrót</a>
            </div>
        </div>
        <div class="col-3 col-0-small"></div>
    </div>
</form>

