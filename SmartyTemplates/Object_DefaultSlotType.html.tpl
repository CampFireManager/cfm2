<!--SM:include file="Common_Object_Header.tpl":SM-->
    <div data-role="content">
        <ul data-role="listview" id="theobject" data-theme="d" data-divider-theme="d">
            <!--SM:foreach $renderPage as $object:SM-->
                <div id="DefaultSlotType_<!--SM:$object.intDefaultSlotTypeID:SM-->">
                    <script type="text/Javascript">
                        $(function() {
                            $('#DefaultSlotType_<!--SM:$object.intDefaultSlotTypeID:SM--> .progressive_basic').hide();
                            $('#DefaultSlotType_<!--SM:$object.intDefaultSlotTypeID:SM--> .readwrite').hide();
                            $('#editmode_<!--SM:$object.intDefaultSlotTypeID:SM-->').live("change", function(){
                                if ($(this).val() === '1') {
                                    $('#DefaultSlotType_<!--SM:$object.intDefaultSlotTypeID:SM--> .haseditable').hide();
                                    $('#DefaultSlotType_<!--SM:$object.intDefaultSlotTypeID:SM--> .readwrite').show();
                                } else {
                                    $('#DefaultSlotType_<!--SM:$object.intDefaultSlotTypeID:SM--> .readwrite').hide();
                                    $('#DefaultSlotType_<!--SM:$object.intDefaultSlotTypeID:SM--> .haseditable').show();
                                }
                            });
                        });
                    </script>
                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div>
                            <label for="editmode_<!--SM:$object.intDefaultSlotTypeID:SM-->">Edit mode
                                <select name="editmode_<!--SM:$object.intDefaultSlotTypeID:SM-->" id="editmode_<!--SM:$object.intDefaultSlotTypeID:SM-->" data-role="slider">
                                    <option value="0" selected>off</option>
                                    <option value="1">on</option>
                                </select>
                            </label>
                            <a href="<!--SM:$SiteConfig.baseurl:SM-->defaultslottype/<!--SM:$object.intDefaultSlotTypeID:SM-->?HTTPaction=delete" data-role="button" data-inline="true" data-icon="delete">Delete</a>
                        </div>
                                
                        <form action="<!--SM:$SiteConfig.baseurl:SM-->defaultslottype/<!--SM:$object.intDefaultSlotTypeID:SM-->" method="post">
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->

                    <div id="intDefaultSlotTypeID">
                        <div>
                            <span class="progressive_basic">Read only: </span>
                            <label>
                                DefaultSlotType ID:
                                <data>
                                    <!--SM:$object.intDefaultSlotTypeID:SM-->
                                </data>
                            </label>
                        </div>
                    </div>

                    <!--SM:include file="Elements/TextBox.tpl" field='strDefaultSlotType' label=$object.labels.strDefaultSlotType|default:'' edit=$object.isEditable.strDefaultSlotType|default:array() current=$object.strDefaultSlotType|default:'':SM-->
                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='lockSlot' label=$object.labels.lockSlot|default:'' edit=$object.isEditable.lockSlot|default:array() current=$object.current.arrDefaultSlotType|default:array():SM-->

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
