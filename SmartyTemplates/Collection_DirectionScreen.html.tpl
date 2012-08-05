<html>
    <head>
        <title>Direction Screen</title>
    </head>
    <body>
        <table>
            <tr>
                <td id="UL">
                    <!--SM:if isset($renderPage.upleft.strRoom):SM-->
                        <!--SM:$renderPage.upleft.strRoom:SM-->
                        <!--SM:foreach $renderPage.upleft.now as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                        <!--SM:foreach $renderPage.upleft.next as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                    <!--SM:/if:SM-->
                </td>
                <td id="U">
                    <!--SM:if isset($renderPage.upcentre.strRoom):SM-->
                        <!--SM:$renderPage.upcentre.strRoom:SM-->
                        <!--SM:foreach $renderPage.upcentre.now as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                        <!--SM:foreach $renderPage.upcentre.next as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                    <!--SM:/if:SM-->
                </td>
                <td id="UR">
                    <!--SM:if isset($renderPage.upright.strRoom):SM-->
                        <!--SM:$renderPage.upright.strRoom:SM-->
                        <!--SM:foreach $renderPage.upright.now as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                        <!--SM:foreach $renderPage.upright.next as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                    <!--SM:/if:SM-->
                </td>
            </tr>
            <tr>
                <td id="L">
                    <!--SM:if isset($renderPage.left.strRoom):SM-->
                        <!--SM:$renderPage.left.strRoom:SM-->
                        <!--SM:foreach $renderPage.left.now as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                        <!--SM:foreach $renderPage.left.next as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                    <!--SM:/if:SM-->
                </td>
                <td id="C">Time of this talk and the next goes here</td>
                <td id="R">
                    <!--SM:if isset($renderPage.right.strRoom):SM-->
                        <!--SM:$renderPage.right.strRoom:SM-->
                        <!--SM:foreach $renderPage.right.now as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                        <!--SM:foreach $renderPage.right.next as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                    <!--SM:/if:SM-->
                </td>
            </tr>
            <tr>
                <td id="DL">
                    <!--SM:if isset($renderPage.downleft.strRoom):SM-->
                        <!--SM:$renderPage.downleft.strRoom:SM-->
                        <!--SM:foreach $renderPage.downleft.now as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                        <!--SM:foreach $renderPage.downleft.next as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                    <!--SM:/if:SM-->
                </td>
                <td id="D">
                    <!--SM:if isset($renderPage.downcentre.strRoom):SM-->
                        <!--SM:$renderPage.downcentre.strRoom:SM-->
                        <!--SM:foreach $renderPage.downcentre.now as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                        <!--SM:foreach $renderPage.downcentre.next as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                    <!--SM:/if:SM-->
                </td>
                <td id="DR">
                    <!--SM:if isset($renderPage.downright.strRoom):SM-->
                        <!--SM:$renderPage.downright.strRoom:SM-->
                        <!--SM:foreach $renderPage.downright.now as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                        <!--SM:foreach $renderPage.downright.next as $talk:SM-->
                            <!--SM:$talk.strTalk:SM--> by
                            <!--SM:foreach $talk.arrPresenters as $arrPresenter:SM-->
                                <!--SM:$arrPresenter.strName:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        <!--SM:/foreach:SM-->
                    <!--SM:/if:SM-->
                </td>
            </tr>
        </table>
        <pre>
        <!--SM:var_dump($renderPage):SM-->
        </pre>
    </body>
</html>