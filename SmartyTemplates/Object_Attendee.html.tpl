<!--SM:include file="Common_Object_Header.tpl":SM-->
    <div data-role="content">
        <ul data-role="listview" id="theobject" data-theme="d" data-divider-theme="d">
            <!--SM:foreach $renderPage as $object:SM-->
                <div id="Attendee_<!--SM:$object.intAttendeeID:SM-->">
                    <script type="text/Javascript">
                        $(function() {
                            $('#Attendee_<!--SM:$object.intAttendeeID:SM--> .progressive_basic').hide();
                            $('#Attendee_<!--SM:$object.intAttendeeID:SM--> .readwrite').hide();
                            $('#editmode_<!--SM:$object.intAttendeeID:SM-->').change(function(){
                                if ($(this).val() === '1') {
                                    $('#Attendee_<!--SM:$object.intAttendeeID:SM--> .haseditable').hide();
                                    $('#Attendee_<!--SM:$object.intAttendeeID:SM--> .readwrite').show();
                                } else {
                                    $('#Attendee_<!--SM:$object.intAttendeeID:SM--> .readwrite').hide();
                                    $('#Attendee_<!--SM:$object.intAttendeeID:SM--> .haseditable').show();
                                }
                            });
                        });
                    </script>
                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div>
                            <label for="editmode_<!--SM:$object.intAttendeeID:SM-->">Edit mode
                                <select name="editmode_<!--SM:$object.intAttendeeID:SM-->" id="editmode_<!--SM:$object.intAttendeeID:SM-->" data-role="slider">
                                    <option value="0" selected>off</option>
                                    <option value="1">on</option>
                                </select>
                            </label>
                            <a href="<!--SM:$SiteConfig.baseurl:SM-->Attendee/<!--SM:$object.intAttendeeID:SM-->?HTTPaction=delete" data-role="button" data-inline="true" data-icon="delete">Delete</a>
                        </div>
                                
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

                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='intTalkID' label=$object.labels.intTalkID edit=$object.isEditable.intTalkID current=$object.arrTalk.current:SM-->
                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='intUserID' label=$object.labels.intUserID edit=$object.isEditable.intUserID current=$object.arrUser.current:SM-->

                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div class="readwrite"><input type="submit" value="Update"/></div>
                        </form>
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->
                </div>
                <!--SM:if !$object@last:SM--><hr /><!--SM:/if:SM-->
            <!--SM:/foreach:SM-->
        </ul>
    </div>
<!--SM:include file="Common_Object_Footer.tpl":SM-->