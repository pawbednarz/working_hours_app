<header id="header">
    <div class="row">
        <div class="col-6 col-12-small">
            <a href="{$conf->action_root}dashboard" class="logo"><strong>eHarmonogram</strong></a>
        </div>
        {if isset($userData)}
        <div class="col-6 col-12-small">
            <span class="user-label">Zalogowano jako {$userData->firstName} {$userData->lastName}</span>
        </div>
        {/if}
    </div>
</header>
{if isset($userData)}
    <h4 class="description-header">{$description}</h4>
{/if}
