<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Http\Response;

/**
 * Trait NoticeRedirect
 *
 * @package Engency\Http\Response
 */
trait NoticeRedirect
{

    /**
     * @param string $noticeMessage
     *
     * @return \Engency\Http\Response\Response
     */
    public function redirectBackWithError(string $noticeMessage)
    {
        return ( new Response() )
            ->error(-1, $noticeMessage)
            ->redirectBack();
    }

    /**
     * @param string $noticeMessage
     *
     * @return \Engency\Http\Response\Response
     */
    public function redirectBackWithSuccess(string $noticeMessage)
    {
        return $this->redirectBackWithNotice($noticeMessage, Notice::NOTICE_SUCCESS);
    }

    /**
     * @param string $noticeMessage
     * @param string $noticeType
     *
     * @return \Engency\Http\Response\Response
     */
    public function redirectBackWithNotice(string $noticeMessage, string $noticeType = Notice::NOTICE_ALERT)
    {
        return ( new Response() )
            ->redirectBack()
            ->message($noticeMessage, $noticeType);
    }

    /**
     * @param string      $routeName
     * @param array       $parameters
     * @param string      $noticeMessage
     * @param string|null $noticeType    Notice::NOTICE_ALERT|Notice::NOTICE_SUCCESS|Notice::NOTICE_WARNING|Notice
     *                                   ::NOTICE_ERROR|Notice::NOTICE_INFO .
     *
     * @return \Engency\Http\Response\Response
     */
    public function redirectWithNotice(
        string $routeName,
        array $parameters,
        string $noticeMessage,
        ?string $noticeType = null
    ) {
        return $this->doRedirectWithNotice($routeName, $parameters, $noticeMessage, $noticeType ?: '');
    }

    /**
     * @param string $routeName
     * @param array  $parameters
     * @param string $noticeMessage
     * @param string $noticeType
     *
     * @return \Engency\Http\Response\Response
     */
    private function doRedirectWithNotice(
        string $routeName,
        array $parameters,
        string $noticeMessage,
        string $noticeType
    ) {
        return ( new Response() )
            ->redirectTo($routeName, $parameters)
            ->message($noticeMessage, $noticeType);
    }
}
