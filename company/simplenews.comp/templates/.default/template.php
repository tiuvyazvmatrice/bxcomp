<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>

<?
foreach ($arResult['YEARS'] as $year) {
    $currentCss = $year == $arResult['CURRENT_YEAR'] ? "font-weight: bold;" : "";
    echo '<a href="?YEAR=' . $year . '" style="'.$currentCss.'">' . $year . '</a> ';
} ?>
<table>
<? foreach ($arResult['ITEMS'] as $item) { ?>
    <tr>
        <td><?=$item['NAME'];?></td>
        <td><?=$item['ACTIVE_FROM'];?></td>
        <td><?=$item['PREVIEW_TEXT'];?></td>
        <td><img src="<?=$item['PICTURE'];?>"></td>
    </tr>
<? } ?>
</table>

<?php
$APPLICATION->IncludeComponent(
    "bitrix:main.pagenavigation",
    ".default",
    array(
        "NAV_OBJECT" => $arResult["NAV"],
    ),
    false
);