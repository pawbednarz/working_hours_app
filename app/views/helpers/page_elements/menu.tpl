<div id="sidebar">
    <div class="inner">
        <nav class="menu">
            <header class="major">
                <h2>Menu</h2>
            </header>
            <ul>
                <li><a href="{$conf->action_url}dashboard">Strona główna</a></li>
                <li>
                    <span class="opener">Godziny</span>
                    <ul>
                        <li><a href="{$conf->action_url}dashboard">Bieżący miesiąc</a></li>
                        <li><a href="{$conf->action_url}showEntriesForMonth">Wybrany miesiąc</a></li>
                    </ul>
                </li>
                <li>
                    <span class="opener">Emaile</span>
                    <ul>
                        <li><a href="{$conf->action_url}showEmails">Wysłane wiadomości</a></li>
                        <li><a href="{$conf->action_url}sendEmail">Wyślij wiadomość</a></li>
                    </ul>
                </li>
                <li><a href="{$conf->action_url}showReports">Raporty</a></li>
                <li>
                    <span class="opener">Konfiguracja Email</span>
                    <ul>
                        <li><a href="{$conf->action_url}showRecipients">Odbiorcy</a></li>
                        <li><a href="{$conf->action_url}showEmailTemplates">Szablony wiadomości</a></li>
                    </ul>
                </li>
                <li><a href="{$conf->action_url}logout">Wyloguj</a></li>
            </ul>
        </nav>
    </div>
</div>
