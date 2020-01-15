<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Http\Response;

/**
 * Class Notice
 *
 * @package Engency\Http\Response
 */
class Notice
{

    public const NOTICE_ALERT   = 'alert';
    public const NOTICE_SUCCESS = 'success';
    public const NOTICE_WARNING = 'warning';
    public const NOTICE_ERROR   = 'error';
    public const NOTICE_INFO    = 'information';
    public const NOTICES        = [
        self::NOTICE_ALERT,
        self::NOTICE_SUCCESS,
        self::NOTICE_WARNING,
        self::NOTICE_ERROR,
        self::NOTICE_INFO,
    ];
}
