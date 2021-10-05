<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Data\Cache;


class CSimplenewsComp extends \CBitrixComponent
{
    protected $filter = ['ACTIVE' => 'Y', '!ACTIVE_FROM' => null];

    protected $sort = ['ACTIVE_FROM' => 'DESC'];

    protected $select = [
        'ID', 'NAME', 'ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_PICTURE'
    ];

    public function onPrepareComponentParams($params)
    {
        if ((int)$params['IBLOCK_ID']) {
            $this->filter['IBLOCK_ID'] = $params['IBLOCK_ID'];
        }

        return $params;
    }

    public function setCache($nav)
    {
        return $this->startResultCache(false, [
            $nav,
            $this->request->get("YEAR"),
        ]);
    }

    private function setFilter()
    {
        $year = (int)$this->request->get("YEAR");
        if($year == 0) {
            $this->filter['>=ACTIVE_FROM'] = date('01.01.'.date('Y'));
            $this->filter['<=ACTIVE_FROM'] = date('31.12.'.date('Y'));
        }
        else {
            $this->filter['>=ACTIVE_FROM'] = date('01.01.'.$year);
            $this->filter['<=ACTIVE_FROM'] = date('31.12.'.$year);
        }
    }

    public function executeComponent()
    {
        $nav = $this->getNav();

        try {
            if (!Loader::includeModule("iblock")) {
                return;
            }

            $this->setFilter();
            $res = $this->selectElements();
            $this->arResult = $res;
            $nav->setRecordCount($this->arResult['COUNT']);
            $this->arResult['NAV'] = $nav;

            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            return;
        }
    }

    protected function getNav()
    {
        $nav = new PageNavigation("users_page");
        $nav->allowAllRecords(true)->setPageSize($this->arParams["ELEMENT_COUNT"])->initFromUri();

        return $nav;
    }

    protected function selectElements()
    {
        $nav = $this->getNav();

        $cacheManager = Bitrix\Main\Application::getInstance()->getTaggedCache();
        $cache = Cache::createInstance();

        $cacheId = 'usergroups'.md5(serialize($this->filter).serialize($this->arParams).serialize($nav));
        $cacheDir = '/Simplenews1';

        if ($cache->initCache($this->arParams['CACHE_TIME'], $cacheId, $cacheDir)) {
            $vars = $cache->GetVars();
            $elementData = $vars['arResult'];
        } elseif ($cache->startDataCache()) {
            $dbYears = Bitrix\Iblock\ElementTable::GetList([
                'select' => ['YEARS'],
                'filter' => ['ACTIVE' => "Y", '!ACTIVE_FROM' => null],
                'count_total' => true,
                'runtime' => [
                    'YEARS' => [
                        'data_type' => 'integer',
                        'expression' => ['YEAR(%s)', 'ACTIVE_FROM'],
                    ],
                ],
                'group' => ['YEARS']
            ]);

            while ($arYear = $dbYears->Fetch()) {
                $elementData['YEARS'][] = $arYear['YEARS'];
            }

            $dbElements = \Bitrix\Iblock\ElementTable::GetList([
                'select' => $this->select,
                'order' => $this->sort,
                'filter' => $this->filter,
                'count_total' => true,
                'limit' => $nav->getLimit(),
                'offset' => $nav->getOffset(),
                'cache' => array(
                    'ttl' => $this->arParams["CACHE_TIME"],
                    'cache_joins' => true,
                )
            ]);

            while ($arElement = $dbElements->Fetch()) {
                $elementData['ITEMS'][] = $arElement;
            }

            foreach ($elementData['ITEMS'] as $key => $item) {
                $elementData['ITEMS'][$key]['PICTURE'] = CFile::GetPath($item['PREVIEW_PICTURE']);
            }

            $elementData['COUNT'] = $dbElements->getCount();

            $cacheManager->StartTagCache($cacheDir);
            $cacheManager->RegisterTag("iblock_id_".$this->arParams['IBLOCK_ID']);
            $cacheManager->EndTagCache();

            if (empty($elementData))
                $cache->abortDataCache();

            $cache->endDataCache([
                'arResult' => $elementData,
            ]);
        }
        global $APPLICATION;
        $APPLICATION->SetTitle("Количество элементов: ".$elementData['COUNT']);

        return $elementData;
    }
}