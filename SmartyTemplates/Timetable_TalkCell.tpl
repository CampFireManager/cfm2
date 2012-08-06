<!--SM:foreach $cell as $talkdata:SM-->
            <!-- <!--SM:$talkdata@key:SM-->:<!--SM:$talkdata:SM--> -->
<!--SM:/foreach:SM-->
<!--SM:if isset($cell.intTalkID):SM-->
            <span class="intTalkID"><!--SM:$cell.intTalkID:SM--></span>
<!--SM:/if:SM-->
            <span class="strTalk"><!--SM:$cell.strTalk:SM--></span>
<!--SM:if isset($cell.intUserID):SM-->
            <span class="label_strUser">by 
<!--SM:foreach $cell.arrPresenters as $presenter:SM-->
<span class="strUser"><!--SM:$presenter.strUser:SM--></span><!--SM:if !$presenter@last:SM-->, <!--SM:/if:SM-->
<!--SM:/foreach:SM-->
            </span>
<!--SM:/if:SM-->
