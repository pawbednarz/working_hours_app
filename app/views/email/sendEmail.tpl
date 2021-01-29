{extends file="main.tpl"}
{block name="content"}
    <form method="post" action="{$conf->action_url}{$action}" class="padding-top-1-5 margin-bottom-1">
        <div class="row gtr-uniform">
            <div class="col-1 col-2-medium input-label">
                Szablon:
            </div>
            <div class="col-11 col-10-medium">
                <select name="email_template_uuid">
                    {foreach $templates as $template}
                        <option value="{$template["uuid"]}">{$template["name"]}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-1 col-2-medium input-label">
                Odbiorca:
            </div>
            <div class="col-11 col-10-medium">
                <select name="recipient_uuid">
                    {foreach $recipients as $recipient}
                        <option value="{$recipient["uuid"]}">{$recipient["first_name"]} {$recipient["last_name"]}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-1 col-2-medium input-label">
                Raport:
            </div>
            <div class="col-11 col-10-medium">
                <select name="report_uuid">
                    {foreach $reports as $report}
                        <option value="{$report["uuid"]}">{$report["filename"]}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-2 col-12-medium">
                <button type="submit" class="primary fit">Wyślij</button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-2 col-12-medium">
            <a class="button fit" href="{$conf->action_url}showEmails">Powrót</a>
        </div>
    </div>
{/block}
