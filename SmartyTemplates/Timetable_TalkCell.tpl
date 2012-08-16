<!--SM:foreach $cell as $talkdata:SM-->
            <!-- <!--SM:$talkdata@key:SM-->:<!--SM:$talkdata:SM--> -->
<!--SM:/foreach:SM-->
<!--SM:if isset($cell.intTalkID):SM-->
            <span class="intTalkID">(ID: <!--SM:$cell.intTalkID:SM-->)</span><br />
<!--SM:/if:SM-->
            <span class="strTalk"><!--SM:$cell.strTalk:SM--></span>
<!--SM:if isset($cell.intUserID):SM-->
            <br /><span class="label_strUser">by 
<!--SM:foreach $cell.arrPresenters as $presenter:SM-->
<span class="strUser"><!--SM:$presenter.strUser:SM--></span><!--SM:if !$presenter@last:SM-->, <!--SM:/if:SM-->
<!--SM:/foreach:SM-->
            </span>
<!--SM:/if:SM-->
<div style="text-align:left;"><!--SM:if isset($cell.hasNsfwMaterial) && $cell.hasNsfwMaterial:SM--><img src="<!--SM:$SiteConfig.baseurl:SM-->media/images/alert-triangle-red.png" /><!--SM:/if:SM--><!--SM:if isset($cell.hasExcessAttendees) && $cell.hasExcessAttendees:SM--><img src="<!--SM:$SiteConfig.baseurl:SM-->media/images/alert-triangle-blue.png" /><!--SM:/if:SM--></div>
