<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Klarna Bank AB www.klarna.com
 * @copyright Copyright (c) permanent, Klarna Bank AB
 * @license   ISC
 *
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Klarna Bank AB
 */

namespace Invertus\SaferPay\Enum;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PermissionType
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
}