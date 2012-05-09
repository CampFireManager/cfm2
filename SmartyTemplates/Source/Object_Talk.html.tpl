{include file="header.tpl" title="Timetable"}
{foreach $Object_Talk as $talk}
<div class="Talk" id="talk_{$talk@key}">
    <div class="strTalkTitle">{$talk.strTalkTitle}</div>
    <div class="label_strUserName">by <span class="strUserName">{$talk.arrUser.strUserName}</span></div>
    <div class="strTalkSummary">{$talk.strTalkSummary}</div>
    <div class="label_strRoomName">in <span class="strRoomName">{$talk.arrRoom.strRoomName}</span></div>
    <div class="label_timeStartEnd">from {if $talk.arrSlot_start.dateStart != date('Y-m-d')}{$talk.arrSlot_start.dateStart}{/if}<span class="timeStart">{$talk.arrSlot_start.timeStart}</span> to <span class="timeEnd">{$talk.arrSlot_stop.timeEnd}</span></div>
    <div class="arrLinks">
{foreach $talk.arrLinks as $link}
        <div class="strLink"><a href="{$link}">{$link@key}</a></div>
{/foreach}
    </div>
</div>
{/foreach}
{include file="footer.tpl"}