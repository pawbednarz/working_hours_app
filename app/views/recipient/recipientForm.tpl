<form method="post" action="{$conf->action_url}addRecipient" class="padding-top-1-5 margin-bottom-1">
    <div class="row gtr-uniform">
        <div class="col-4 col-12-xsmall">
            <input type="text" name="first_name" id="first_name" placeholder="Imię">
        </div>
        <div class="col-4 col-12-xsmall">
            <input type="text" name="last_name" id="last_name" placeholder="Nazwisko">
        </div>
        <div class="col-4 col-12-xsmall">
            <input type="email" name="email" id="email" placeholder="Email">
        </div>
        <div class="col-2 col-12-medium">
            <button type="submit" class="primary fit">Dodaj</button>
        </div>
    </div>
</form>
<div class="row">
    <div class="col-2 col-12-medium">
        <a class="button fit" href="{$conf->action_url}showRecipients">Powrót</a>
    </div>
</div>
