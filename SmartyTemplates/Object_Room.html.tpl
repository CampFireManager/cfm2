<!--SM:include file="Common_Object_Header.tpl":SM-->
    <div data-role="content">
        <ul data-role="listview" id="theobject" data-theme="d" data-divider-theme="d">
            <!--SM:foreach $renderPage as $object:SM-->
                <div id="Room_<!--SM:$object.intRoomID:SM-->">
                    <script type="text/Javascript">
                        $(function() {
                            $('#Room_<!--SM:$object.intRoomID:SM--> .progressive_basic').hide();
                            $('#Room_<!--SM:$object.intRoomID:SM--> .readwrite').hide();
                            $('#editmode_<!--SM:$object.intRoomID:SM-->').on("change", function(){
                                if ($(this).val() === '1') {
                                    $('#Room_<!--SM:$object.intRoomID:SM--> .haseditable').hide();
                                    $('#Room_<!--SM:$object.intRoomID:SM--> .readwrite').show();
                                } else {
                                    $('#Room_<!--SM:$object.intRoomID:SM--> .readwrite').hide();
                                    $('#Room_<!--SM:$object.intRoomID:SM--> .haseditable').show();
                                }
                            });
                        });
                    </script>
                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div>
                            <label for="editmode_<!--SM:$object.intRoomID:SM-->">Edit mode
                                <select name="editmode_<!--SM:$object.intRoomID:SM-->" id="editmode_<!--SM:$object.intRoomID:SM-->" data-role="slider">
                                    <option value="0" selected>off</option>
                                    <option value="1">on</option>
                                </select>
                            </label>
                            <a href="<!--SM:$SiteConfig.baseurl:SM-->room/<!--SM:$object.intRoomID:SM-->?HTTPaction=delete" data-role="button" data-inline="true" data-icon="delete">Delete</a>
                        </div>
                                
                        <form action="<!--SM:$SiteConfig.baseurl:SM-->room/<!--SM:$object.intRoomID:SM-->" method="post">
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->

                    <div id="intRoomID">
                        <div>
                            <span class="progressive_basic">Read only: </span>
                            <label>
                                Room ID:
                                <data>
                                    <!--SM:$object.intRoomID:SM-->
                                </data>
                            </label>
                        </div>
                    </div>

                    <!--SM:include file="Elements/TextBox.tpl" field='strRoom' label=$object.labels.strRoom|default:'' edit=$object.isEditable.strRoom|default:array() current=$object.strRoom|default:'':SM-->
                    <!--SM:include file="Elements/TextBox.tpl" field='intCapacity' label=$object.labels.intCapacity|default:'' edit=$object.isEditable.intCapacity|default:array() current=$object.intCapacity|default:0:SM-->
                    <!--SM:include file="Elements/Boolean.tpl" field='isLocked' label=$object.labels.isLocked|default:'' edit=$object.isEditable.isLocked|default:array() current=$object.isLocked|default:'':SM-->
                    <!--SM:include file="Elements/MultiElementFromArray.tpl" field='jsonResourceList' label=$object.labels.jsonResourceList|default:'' edit=$object.isEditable.jsonResourceList|default:array() list=$object.arrResources|default:array():SM-->

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
