<!--SM:include file="Common_Object_Header.tpl":SM-->
    <div data-role="content">
        <ul data-role="listview" id="theobject" data-theme="d" data-divider-theme="d">
            <!--SM:foreach $renderPage as $object:SM-->
                <div id="Attendee_<!--SM:$object.intAttendeeID:SM-->">
                    <script type="text/Javascript">
                        $(function() {
                            $('#Talk_<!--SM:$object.intTalkID:SM--> .progressive_basic').hide();
                            $('#Talk_<!--SM:$object.intTalkID:SM--> .haseditable').hide();
                            $('#Talk_<!--SM:$object.intTalkID:SM--> .readwrite').show();
                        });
                    </script>
                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <form action="<!--SM:$SiteConfig.thisurl:SM-->" method="post">
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->

                    <div id="intAttendeeID">
                        <div>
                            <span class="progressive_basic">Read only: </span>
                            <label>
                                Attendee ID:
                                <data>
                                    <!--SM:$object.intAttendeeID:SM-->
                                </data>
                            </label>
                        </div>
                    </div>

                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='intTalkID' edit=$object.isEditable.intTalkID|default:'' current=$object.arrTalk.current|default:'':SM-->
                    <!--SM:include file="Elements/SingleElementDropDownWithHiddenUserData.tpl" field='intUserID' edit=$object.isEditable.intUserID|default:array() current=$object.arrUser.current|default:array():SM-->

                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div class="readwrite"><input type="submit" value="Confirm"/></div>
                        </form>
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->
                </div>
                <!--SM:if !$object@last:SM--><hr /><!--SM:/if:SM-->
            <!--SM:/foreach:SM-->
        </ul>
    </div>
<!--SM:include file="Common_Object_Footer.tpl":SM-->
