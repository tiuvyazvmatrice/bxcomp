<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = [
    "NAME" => Loc::getMessage("C_SIMP_NEW_NAME"),
    "DESCRIPTION" => Loc::getMessage("C_SIMP_NEW_DESC"),
    "COMPLEX" => "N",
    "PATH" => [
        "ID" => "test",
        "NAME" => Loc::getMessage("C_SIMP_NEW_GR_NAME"),
    ],
];