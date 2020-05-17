<?php


namespace App\Enum;

abstract class AlertsEnum {

    const ALERT_TYPE_INFO = 0;
    const ALERT_TYPE_SUCCESS = 1;
    const ALERT_TYPE_WARNING = 2;
    const ALERT_TYPE_ERROR = 3;

    const ALERT_FLASH_INFO = 'info';
    const ALERT_FLASH_SUCCESS = 'success';
    const ALERT_FLASH_WARNING = 'warning';
    const ALERT_FLASH_ERROR = 'error';

    const ALERT_FLASHES = [
        self::ALERT_TYPE_INFO => self::ALERT_FLASH_INFO,
        self::ALERT_TYPE_SUCCESS => self::ALERT_FLASH_SUCCESS,
        self::ALERT_TYPE_WARNING => self::ALERT_FLASH_WARNING,
        self::ALERT_TYPE_ERROR => self::ALERT_FLASH_ERROR
    ];

    const ALERT_TITLE_INFO = 'alertTitles.info';
    const ALERT_TITLE_SUCCESS = 'alertTitles.success';
    const ALERT_TITLE_WARNING = 'alertTitles.warning';
    const ALERT_TITLE_ERROR = 'alertTitles.error';

    const ALERT_TITLES = [
        //Po typach
        self::ALERT_TYPE_INFO => self::ALERT_TITLE_INFO,
        self::ALERT_TYPE_SUCCESS => self::ALERT_TITLE_SUCCESS,
        self::ALERT_TYPE_WARNING => self::ALERT_TITLE_WARNING,
        self::ALERT_TYPE_ERROR => self::ALERT_TITLE_ERROR,

        //Po fleszach
        self::ALERT_FLASH_INFO => self::ALERT_TITLE_INFO,
        self::ALERT_FLASH_SUCCESS => self::ALERT_TITLE_SUCCESS,
        self::ALERT_FLASH_WARNING => self::ALERT_TITLE_WARNING,
        self::ALERT_FLASH_ERROR => self::ALERT_TITLE_ERROR,

    ];

    const ALERT_CLASS_INFO = 'alert-info';
    const ALERT_CLASS_SUCCESS = 'alert-success';
    const ALERT_CLASS_WARNING = 'alert-warning';
    const ALERT_CLASS_ERROR = 'alert-danger';

    const ALERT_CLASSES = [
        //Po typach
        self::ALERT_TYPE_INFO => self::ALERT_CLASS_INFO,
        self::ALERT_TYPE_SUCCESS => self::ALERT_CLASS_SUCCESS,
        self::ALERT_TYPE_WARNING => self::ALERT_CLASS_WARNING,
        self::ALERT_TYPE_ERROR => self::ALERT_CLASS_ERROR,

        //Po fleszach
        self::ALERT_FLASH_INFO => self::ALERT_CLASS_INFO,
        self::ALERT_FLASH_SUCCESS => self::ALERT_CLASS_SUCCESS,
        self::ALERT_FLASH_WARNING => self::ALERT_CLASS_WARNING,
        self::ALERT_FLASH_ERROR => self::ALERT_CLASS_ERROR
    ];

}