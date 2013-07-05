<!--SM:include file="Common_Object_Header.tpl":SM-->
    <div data-role="content">
        <ul data-role="listview" id="theobject" data-theme="d" data-divider-theme="d">
            <!--SM:foreach $renderPage as $object:SM-->
                <div id="Screen_<!--SM:$object.intScreenID:SM-->">
                    <script type="text/Javascript">
                        $(function() {
                            $('#Screen_<!--SM:$object.intScreenID:SM--> .progressive_basic').hide();
                            $('#Screen_<!--SM:$object.intScreenID:SM--> .readwrite').hide();
                            $('#editmode_<!--SM:$object.intScreenID:SM-->').live("change", function(){
                                if ($(this).val() === '1') {
                                    $('#Screen_<!--SM:$object.intScreenID:SM--> .haseditable').hide();
                                    $('#Screen_<!--SM:$object.intScreenID:SM--> .readwrite').show();
                                } else {
                                    $('#Screen_<!--SM:$object.intScreenID:SM--> .readwrite').hide();
                                    $('#Screen_<!--SM:$object.intScreenID:SM--> .haseditable').show();
                                }
                            });
                        });
                    </script>
                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div>
                            <label for="editmode_<!--SM:$object.intScreenID:SM-->">Edit mode
                                <select name="editmode_<!--SM:$object.intScreenID:SM-->" id="editmode_<!--SM:$object.intScreenID:SM-->" data-role="slider">
                                    <option value="0" selected>off</option>
                                    <option value="1">on</option>
                                </select>
                            </label>
                            <a href="<!--SM:$SiteConfig.baseurl:SM-->screen/<!--SM:$object.intScreenID:SM-->?HTTPaction=delete" data-role="button" data-inline="true" data-icon="delete">Delete</a>
                        </div>
                                
                        <form action="<!--SM:$SiteConfig.baseurl:SM-->screen/<!--SM:$object.intScreenID:SM-->" method="post">
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->

                    <div id="intScreenID">
                        <div>
                            <span class="progressive_basic">Read only: </span>
                            <label>
                                Screen ID:
                                <data>
                                    <!--SM:$object.intScreenID:SM-->
                                </data>
                            </label>
                        </div>
                    </div>
                                
                    <!--SM:include file="Elements/TextBox.tpl" field='strScreen' label=$object.labels.strScreen|default:'' edit=$object.isEditable.strScreen|default:array() current=$object.strScreen|default:'':SM-->

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
