<!--SM:assign var=comma value=',':SM-->
<!--SM:foreach $renderPage as $Timetable:SM-->
    <!--SM:foreach $Timetable.arrSlots as $slot:SM-->
    <!--SM:assign var=showroom value=false:SM-->
                    <!--SM:if $slot.isNow != null :SM-->
                        <li data-role="list-divider" class="now">
                        <!--SM:if $slot.dateStart != date('Y-m-d'):SM--><!--SM:$slot.dateStart:SM--> <!--SM:/if:SM--><!--SM:$slot.timeStart:SM--> - <!--SM:$slot.timeEnd:SM--></li>
                    <!--SM:else:SM-->
                        <li data-role="list-divider">
                        <!--SM:if $slot.dateStart != date('Y-m-d'):SM--><!--SM:$slot.dateStart:SM--> <!--SM:/if:SM--><!--SM:$slot.timeStart:SM--> - <!--SM:$slot.timeEnd:SM--></li>
                    <!--SM:/if:SM-->
        <!--SM:assign var=emptyCount value=0:SM-->
        <!--SM:foreach $Timetable.arrTimetable[$slot@key] as $talk:SM-->
            <!--SM:if isset($talk.intUserID):SM-->
                    <li>
                        <a href="<!--SM:$SiteConfig.baseurl:SM-->talk/<!--SM:$talk.intTalkID:SM-->">
                            <!--SM:if isset($Object_User.current) && $Object_User.current != null && $Object_User.current != false:SM-->
                                <!--SM:if $talk.isPresenting:SM-->
                                    <img src="<!--SM:$SiteConfig.baseurl:SM-->media/images/star-blue.png" alt="Presenting" class="ui-li-icon" />
                                <!--SM:elseif $talk.isAttending != false:SM-->
                                    <img src="<!--SM:$SiteConfig.baseurl:SM-->media/images/star-gold.png" alt="Attending" class="ui-li-icon" />
                                <!--SM:else:SM-->
                                    <img src="<!--SM:$SiteConfig.baseurl:SM-->media/images/star-grey.png" alt="Not Attending" class="ui-li-icon" />
                                <!--SM:/if:SM-->
                            <!--SM:/if:SM-->
                            <!--SM:if isset($talk.hasNsfwMaterial) && $talk.hasNsfwMaterial:SM-->
                            <p><img src="<!--SM:$SiteConfig.baseurl:SM-->media/images/alert-triangle-red.png" /> <strong>NSFW/18+ Content!</strong></p><!--SM:/if:SM-->
                            <!--SM:if isset($talk.hasExcessAttendees) && $talk.hasExcessAttendees:SM-->
                            <p><img src="<!--SM:$SiteConfig.baseurl:SM-->media/images/alert-triangle-blue.png" /> <strong>Room Over Capacity!</strong></p><!--SM:/if:SM-->
                            <h3>(ID: <!--SM:$talk.intTalkID:SM-->) <!--SM:$talk.strTalk:SM--></h3>
                            <p>by
                <!--SM:foreach $talk.arrPresenters as $presenter:SM-->
                                <strong><!--SM:$presenter.strUser:SM--></strong><!--SM:if !$presenter@last:SM--><!--SM:$comma:SM--><!--SM:/if:SM-->
                <!--SM:/foreach:SM-->
                                in <strong><!--SM:if isset($talk.arrRoom.strRoom):SM--><!--SM:$talk.arrRoom.strRoom:SM--><!--SM:else:SM-->Undefined<!--SM:/if:SM--></strong>
                            </p>
                            <p><!--SM:$talk.strTalkSummary:SM--></p>
                            <span class="ui-li-count"><img data-icon="alert" /><!--SM:$talk.intAttendees:SM--> / <!--SM:if isset($talk.arrRoom.strCapacity):SM--><!--SM:$talk.arrRoom.strCapacity:SM--><!--SM:else:SM-->Undefined<!--SM:/if:SM--> Attendees</span>
                        </a>
                    </li>
            <!--SM:else:SM-->
                <!--SM:if !$showroom:SM-->
                <!--SM:assign var=showroom value=true:SM-->
                    <!--SM:if $talk.arrSlot.isStillToCome != null:SM-->
                        <!--SM:if $talk.isLocked == 'hardlock':SM-->
                        <li data-theme="a"><h3>All other rooms in this slot unavailable due to: <!--SM:$talk.strTalk:SM--></h3></li>
                        <!--SM:else:SM-->
                            <!--SM:assign var=emptyCount value=$emptyCount+1:SM-->
                        <!--SM:/if:SM-->
                    <!--SM:/if:SM-->
                <!--SM:/if:SM-->
            <!--SM:/if:SM-->
        <!--SM:/foreach:SM-->
        <!--SM:if $emptyCount > 0:SM-->
                        <li data-theme="a">
                            <!--SM:if $Object_User.current != null && $Object_User.current != false:SM-->
                            <a href="<!--SM:$SiteConfig.baseurl:SM-->talk/new?intSlotID=<!--SM:$slot.intSlotID:SM-->">
                            <!--SM:/if:SM-->
                            <h3>Empty<!--SM:if $talk.isLocked == 'softlock':SM--> during: <!--SM:$talk.strTalk:SM--><!--SM:/if:SM--></h3>
                            <!--SM:if $Object_User.current != null && $Object_User.current != false:SM-->
                                <p><strong>Click to arrange a talk here!</strong></p>
                            <!--SM:else:SM-->
                                <p>If you were logged in, you could arrange a talk by clicking here.</p>
                            <!--SM:/if:SM-->
                            <!--SM:if $Object_User.current != null && $Object_User.current != false:SM-->
                            </a>
                            <!--SM:/if:SM-->
                        </li>
        <!--SM:/if:SM-->
    <!--SM:/foreach:SM-->
<!--SM:/foreach:SM-->
