<!--SM:include file="Common_Object_Header.tpl":SM-->
    <div data-role="content">
        <ul data-role="listview" id="theobject" data-theme="d" data-divider-theme="d">
            <!--SM:foreach $renderPage as $object:SM-->
                <div id="Resource_<!--SM:$object.intResourceID:SM-->">
                    <script type="text/Javascript">
                        $(function() {
                            $('#Resource_<!--SM:$object.intResourceID:SM--> .progressive_basic').hide();
                            $('#Resource_<!--SM:$object.intResourceID:SM--> .readwrite').hide();
                            $('#editmode_<!--SM:$object.intResourceID:SM-->').change(function(){
                                if ($(this).val() === '1') {
                                    $('#Resource_<!--SM:$object.intResourceID:SM--> .haseditable').hide();
                                    $('#Resource_<!--SM:$object.intResourceID:SM--> .readwrite').show();
                                } else {
                                    $('#Resource_<!--SM:$object.intResourceID:SM--> .readwrite').hide();
                                    $('#Resource_<!--SM:$object.intResourceID:SM--> .haseditable').show();
                                }
                            });
                        });
                    </script>
                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div>
                            <label for="editmode_<!--SM:$object.intResourceID:SM-->">Edit mode
                                <select name="editmode_<!--SM:$object.intResourceID:SM-->" id="editmode_<!--SM:$object.intResourceID:SM-->" data-role="slider">
                                    <option value="0" selected>off</option>
                                    <option value="1">on</option>
                                </select>
                            </label>
                            <a href="<!--SM:$SiteConfig.baseurl:SM-->resource/<!--SM:$object.intResourceID:SM-->?HTTPaction=delete" data-role="button" data-inline="true" data-icon="delete">Delete</a>
                        </div>
                                
                        <form action="<!--SM:$SiteConfig.thisurl:SM-->" method="post">
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->

                    <div id="intResourceID">
                        <div>
                            <span class="progressive_basic">Read only: </span>
                            <label>
                                Resource ID:
                                <data>
                                    <!--SM:$object.intResourceID:SM-->
                                </data>
                            </label>
                        </div>
                    </div>

                    <!--SM:include file="Elements/TextBox.tpl" field='strResource' label=$object.labels.strResource edit=$object.isEditable.strResource current=$object.strResource:SM-->

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