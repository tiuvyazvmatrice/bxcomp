<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CSimplenewsComp extends \CBitrixComponent
{
    protected $filter = [];

    protected $sort = ['DATE_ACTIVE_FROM' => 'DESC'];

    protected $select = [

    ];

    protected $arNavParams = false;

    public function onPrepareComponentParams($params)
    {
        if ((int)$params['IBLOCK_ID']) {
            $this->filter['IBLOCK_ID'] = $params['IBLOCK_ID'];
        }

        $this->arNavParams = array(
            "nPageSize" => $params["ELEMENT_COUNT"],
            "bDescPageNumbering" => $params["PAGER_DESC_NUMBERING"],
            "bShowAll" => $params["PAGER_SHOW_ALL"],
        );
        $arNavigation = CDBResult::GetNavParams($this->arNavParams);
        if($arNavigation["PAGEN"] == 0 && $params["PAGER_DESC_NUMBERING_CACHE_TIME"] > 0)
            $this->arParams["CACHE_TIME"] = $params["PAGER_DESC_NUMBERING_CACHE_TIME"];

        return $params;
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    protected function selectElements()
    {

    }
}