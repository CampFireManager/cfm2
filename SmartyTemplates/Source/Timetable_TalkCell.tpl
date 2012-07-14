{foreach $cell as $talkdata}
            <!-- {$talkdata@key}:{$talkdata} -->
{/foreach}
{if isset($cell.intTalkID)}
            <span class="intTalkID">{$cell.intTalkID}</span>
{/if}
            <span class="strTalkTitle">{$cell.strTalkTitle}</span>
{if isset($cell.intUserID)}
            <span class="label_strName">by 
{foreach $cell.arrPresenters as $presenter}
<span class="strName">{$presenter.strName}</span>{if !$presenter@last}, {/if}
{/foreach}
            </span>
{/if}