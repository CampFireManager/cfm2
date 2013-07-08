<!--SM:include file="Common_Object_Header.tpl":SM-->
    <div data-role="content">
        <ul data-role="listview" id="theobject" data-theme="d" data-divider-theme="d">
            <!--SM:foreach $renderPage as $object:SM-->
                <div id="ScreenDirection_<!--SM:$object.intScreenDirectionID:SM-->">
                    <script type="text/Javascript">
                        $(function() {
                            $('#ScreenDirection_<!--SM:$object.intScreenDirectionID:SM--> .progressive_basic').hide();
                            $('#ScreenDirection_<!--SM:$object.intScreenDirectionID:SM--> .readwrite').hide();
                            $('#editmode_<!--SM:$object.intScreenDirectionID:SM-->').on("change", function(){
                                if ($(this).val() === '1') {
                                    $('#ScreenDirection_<!--SM:$object.intScreenDirectionID:SM--> .haseditable').hide();
                                    $('#ScreenDirection_<!--SM:$object.intScreenDirectionID:SM--> .readwrite').show();
                                } else {
                                    $('#ScreenDirection_<!--SM:$object.intScreenDirectionID:SM--> .readwrite').hide();
                                    $('#ScreenDirection_<!--SM:$object.intScreenDirectionID:SM--> .haseditable').show();
                                }
                            });
                        });
                    </script>
                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div>
                            <label for="editmode_<!--SM:$object.intScreenDirectionID:SM-->">Edit mode
                                <select name="editmode_<!--SM:$object.intScreenDirectionID:SM-->" id="editmode_<!--SM:$object.intScreenDirectionID:SM-->" data-role="slider">
                                    <option value="0" selected>off</option>
                                    <option value="1">on</option>
                                </select>
                            </label>
                            <a href="<!--SM:$SiteConfig.baseurl:SM-->screendirection/<!--SM:$object.intScreenDirectionID:SM-->?HTTPaction=delete" data-role="button" data-inline="true" data-icon="delete">Delete</a>
                        </div>
                                
                        <form action="<!--SM:$SiteConfig.baseurl:SM-->screendirection/<!--SM:$object.intScreenDirectionID:SM-->" method="post">
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->

                    <div id="intScreenDirectionID">
                        <div>
                            <span class="progressive_basic">Read only: </span>
                            <label>
                                ScreenDirection ID:
                                <data>
                                    <!--SM:$object.intScreenDirectionID:SM-->
                                </data>
                            </label>
                        </div>
                    </div>

                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='intScreenID' label=$object.labels.intScreenID|default:'' edit=$object.isEditable.intScreenID|default:array() current=$object.arrScreen.current|default:array():SM-->
                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='intRoomID' label=$object.labels.intRoomID|default:'' edit=$object.isEditable.intRoomID|default:array() current=$object.arrRoom.current|default:array():SM-->
                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='enumDirection' label=$object.labels.enumDirection|default:'' edit=$object.isEditable.enumDirection|default:array() current=$object.current.enumDirection|default:array():SM-->

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
