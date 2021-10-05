<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

echo '<pre>';
var_dump($arResult);
echo '</pre>';



$APPLICATION->IncludeComponent(
    "bitrix:main.pagenavigation",
    ".default",
    array(
        "NAV_OBJECT" => $arResult["NAV"],
        'PAGE_WINDOW' => 4,
    ),
    false
);