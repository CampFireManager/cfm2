<!--SM:assign var=comma value=',':SM-->
<!--SM:foreach $renderPage as $Timetable:SM-->
    <!--SM:foreach $Timetable.arrSlots as $slot:SM-->
    <!--SM:assign var=showroom value=false:SM-->
                    <li data-role="list-divider"><!--SM:$slot.timeStart:SM--> - <!--SM:$slot.timeEnd:SM--></li>
        <!--SM:foreach $Timetable.arrTimetable[$slot@key] as $talk:SM-->
            <!--SM:if isset($talk.intUserID):SM-->
                    <li>
                        <a href="<!--SM:$SiteConfig.baseurl:SM-->talk/<!--SM:$talk.intTalkID:SM-->">
                            <h3>(ID: <!--SM:$talk.intTalkID:SM-->) <!--SM:$talk.strTalk:SM--></h3>
                            <p>by
                <!--SM:foreach $talk.arrPresenters as $presenter:SM-->
                                <strong><!--SM:$presenter.strUser:SM--></strong><!--SM:if !$presenter@last:SM--><!--SM:$comma:SM--><!--SM:/if:SM-->
                <!--SM:/foreach:SM-->
                                in <strong><!--SM:$talk.arrRoom.strRoom:SM--></strong>
                            </p>
                            <p><!--SM:$talk.strTalkSummary:SM--></p>
                            <span class="ui-li-count"><!--SM:$talk.intAttendees:SM--> / <!--SM:$talk.arrRoom.intCapacity:SM--> Attendees</span>
                            <p class="ui-li-aside"></p>
                        </a>
                    </li>
            <!--SM:else:SM-->
                <!--SM:if !$showroom:SM-->
                <!--SM:assign var=showroom value=true:SM-->
                    <!--SM:if $talk.arrSlot.isStillToCome != null:SM-->
                    <li data-theme="a">
                        <!--SM:if $talk.isLocked == 'hardlock':SM-->
                        <h3>All other rooms in this slot unavailable due to: <!--SM:$talk.strTalk:SM--></h3>
                        <!--SM:else:SM-->
                            <!--SM:if $Object_User.current != null && $Object_User.current != false:SM-->
                        <a href="<!--SM:$SiteConfig.baseurl:SM-->talk/new?slot=<!--SM:$slot.intSlotID:SM-->">
                            <!--SM:/if:SM-->
                            <h3>Empty<!--SM:if $talk.isLocked == 'softlock':SM--> during: <!--SM:$talk.strTalk:SM--><!--SM:/if:SM--></h3>
                            <!--SM:if $Object_User.current != null && $Object_User.current != false:SM-->
                            <p><strong>Click to arrange a talk here!</strong></p>
                            <!--SM:else:SM-->
                                <p>If you were logged in, you could arrange a talk by clicking here.</p>
                            <!--SM:/if:SM-->
                            <p class="ui-li-aside"></p>
                            <!--SM:if $Object_User.current != null && $Object_User.current != false:SM-->
                        </a>
                            <!--SM:/if:SM-->
                        <!--SM:/if:SM-->
                    </li>
                    <!--SM:/if:SM-->
                <!--SM:/if:SM-->
            <!--SM:/if:SM-->
        <!--SM:/foreach:SM-->
    <!--SM:/foreach:SM-->
<!--SM:/foreach:SM-->