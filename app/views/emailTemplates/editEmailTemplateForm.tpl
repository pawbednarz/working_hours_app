{extends file="main.tpl"}
{block name="content"}
    <form method="post" action="{$conf->action_url}{$action}&email_template_uuid={$template["uuid"]}" class="padding-top-1-5 margin-bottom-1">
        <div class="row gtr-uniform">
            <div class="col-6 col-12-xsmall">
                <input type="text" name="template_name" id="template_name" placeholder="Nazwa szablonu" value="{$template["name"]}">
            </div>
            <div class="col-6 col-12-xsmall">
                <input type="text" name="template_subject" id="template_subject" placeholder="Temat" value="{$template["subject"]}">
            </div>
            <div class="col-12 ">
                <textarea name="template_text" id="template_text" placeholder="Treść wiadomości" rows="10">{$template["text"]}</textarea>
            </div>
            <div class="col-2 col-12-medium">
                <button type="submit" class="primary fit">Dodaj</button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-2 col-12-medium">
            <a class="button fit" href="{$conf->action_url}showEmailTemplates">Powrót</a>
        </div>
    </div>
{/block}
