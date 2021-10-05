<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('iblock'))
    return;

$arIBlock = [];
$rsIBlock = \Bitrix\Iblock\IblockTable::getList([
    'select' => ['ID', 'NAME'],
    'filter' => ['ACTIVE' => 'Y']
]);


while ($arr = $rsIBlock->Fetch())
    $arIBlock[$arr['ID']] = '[' . $arr['ID'] . '] ' . $arr['NAME'];

unset($arr, $rsIBlock);

$arComponentParameters = [
    "PARAMETERS" => [
        "IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("IBLOCK_ID"),
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "MULTIPLE" => "Y",
            "SIZE" => 5,
            "VALUES" => $arIBlock,
            "REFRESH" => "Y",
        ],
        "ELEMENT_COUNT" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("T_IBLOCK_DESC_LIST_CONT"),
            "TYPE" => "STRING",
            "DEFAULT" => 6,
        ],
        "CACHE_TIME" => [
            "DEFAULT" => 3600
        ]
    ]
];
