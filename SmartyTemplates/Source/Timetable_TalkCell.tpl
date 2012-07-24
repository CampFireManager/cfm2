{foreach $cell as $talkdata}
            <!-- {$talkdata@key}:{$talkdata} -->
{/foreach}
{if isset($cell.intTalkID)}
            <span class="intTalkID">{$cell.intTalkID}</span>
{/if}
            <span class="strTalk">{$cell.strTalk}</span>
{if isset($cell.intUserID)}
            <span class="label_strUser">by 
{foreach $cell.arrPresenters as $presenter}
<span class="strUser">{$presenter.strUser}</span>{if !$presenter@last}, {/if}
{/foreach}
            </span>
{/if}