<!--SM:include file="Common_Object_Header.tpl":SM-->
    <div data-role="content">
        <ul data-role="listview" id="theobject" data-theme="d" data-divider-theme="d">
            <!--SM:foreach $renderPage as $object:SM-->
                <div id="Slot_<!--SM:$object.intSlotID:SM-->">
                    <script type="text/Javascript">
                        $(function() {
                            $('#Slot_<!--SM:$object.intSlotID:SM--> .progressive_basic').hide();
                            $('#Slot_<!--SM:$object.intSlotID:SM--> .readwrite').hide();
                            $('#editmode_<!--SM:$object.intSlotID:SM-->').change(function(){
                                if ($(this).val() === '1') {
                                    $('#Slot_<!--SM:$object.intSlotID:SM--> .haseditable').hide();
                                    $('#Slot_<!--SM:$object.intSlotID:SM--> .readwrite').show();
                                } else {
                                    $('#Slot_<!--SM:$object.intSlotID:SM--> .readwrite').hide();
                                    $('#Slot_<!--SM:$object.intSlotID:SM--> .haseditable').show();
                                }
                            });
                        });
                    </script>
                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div>
                            <label for="editmode_<!--SM:$object.intSlotID:SM-->">Edit mode
                                <select name="editmode_<!--SM:$object.intSlotID:SM-->" id="editmode_<!--SM:$object.intSlotID:SM-->" data-role="slider">
                                    <option value="0" selected>off</option>
                                    <option value="1">on</option>
                                </select>
                            </label>
                            <a href="<!--SM:$SiteConfig.baseurl:SM-->slot/<!--SM:$object.intScreenDirectionID:SM-->?HTTPaction=delete" data-role="button" data-inline="true" data-icon="delete">Delete</a>
                        </div>
                                
                        <form action="<!--SM:$SiteConfig.thisurl:SM-->" method="post">
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->

                    <div id="intSlotID">
                        <div>
                            <span class="progressive_basic">Read only: </span>
                            <label>
                                Slot ID:
                                <data>
                                    <!--SM:$object.intSlotID:SM-->
                                </data>
                            </label>
                        </div>
                    </div>

                    <!--SM:include file="Elements/TextBox.tpl" field='dateStart' label=$object.labels.dateStart edit=$object.isEditable.dateStart current=$object.dateStart:SM-->
                    <!--SM:include file="Elements/TextBox.tpl" field='timeStart' label=$object.labels.timeStart edit=$object.isEditable.timeStart current=$object.timeStart:SM-->
                    <!--SM:include file="Elements/TextBox.tpl" field='dateEnd' label=$object.labels.dateEnd edit=$object.isEditable.dateEnd current=$object.dateEnd:SM-->
                    <!--SM:include file="Elements/TextBox.tpl" field='timeEnd' label=$object.labels.timeEnd edit=$object.isEditable.timeEnd current=$object.timeEnd:SM-->
                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='intDefaultSlotTypeID' label=$object.labels.intDefaultSlotTypeID edit=$object.isEditable.intDefaultSlotTypeID current=$object.arrDefaultSlotType.current:SM-->

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