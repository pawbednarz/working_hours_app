{if !$msgs->isEmpty()}
        {foreach $msgs->getMessages() as $msg}
            <div class="{if $msg->isInfo()}isa_success{/if}
                        {if $msg->isWarning()}isa_warning{/if}
                        {if $msg->isError()}isa_error{/if}">
                {$msg->text}
            </div>
        {/foreach}
{/if}
