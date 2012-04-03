{foreach $cell as $talkdata}
            <!-- {$talkdata@key}:{$talkdata} -->
{/foreach}
{if isset($cell.intTalkID)}
            <span class="intTalkID">{$cell.intTalkID}</span>
{/if}
            <span class="strTalkTitle">{$cell.strTalkTitle}</span>
{if isset($cell.arrUser.strUserName)}
            <span class="label_strUserName">by <span class="strUserName">{$cell.arrUser.strUserName}</span></span>
{/if}