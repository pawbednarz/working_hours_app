{extends file="main.tpl"}
{block name="content"}
    <form method="post" action="{$conf->action_url}{$action}" class="padding-top-1-5 margin-bottom-1">
        <div class="row gtr-uniform">
            <div class="col-2 col-4-small input-label">
                Data wysłania:
            </div>
            <div class="col-10 col-8-small">
                <input type="text" name="sent_date" id="sent_date" value="{$email["sent_date"]|substr:0:16}" disabled>
            </div>
            <div class="col-2 col-4-small input-label">
                Temat wiadomości:
            </div>
            <div class="col-10 col-8-small">
                <input type="text" name="subject" id="subject" value="{$email["subject"]}" disabled>
            </div>
            <div class="col-2 col-4-small input-label"">
                Odbiorca:
            </div>
            <div class="col-10 col-8-small">
                <input type="text" name="recipient" id="recipient" value="{$email["recipient"]["first_name"]} {$email["recipient"]["last_name"]} ({$email["recipient"]["email"]})" disabled>
            </div>
            <div class="col-2 col-4-small input-label">
                Tekst wiadomości:
            </div>
            <div class="col-10 col-8-small">
                <textarea name="text" id="text" rows="5" disabled>{$email["text"]}</textarea>
            </div>
            <div class="col-2 col-4-small input-label">
                Załączony raport:
            </div>
            <div class="col-10 col-8-small">
                <input type="text" name="report_filename" id="report_filename" value="{$email["report"]["filename"]}" disabled>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-2 col-12-medium">
            <a class="button fit" href="{$conf->action_url}showEmails">Powrót</a>
        </div>
    </div>
{/block}
