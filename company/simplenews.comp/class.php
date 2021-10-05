<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Data\Cache;


class CSimplenewsComp extends \CBitrixComponent
{
    protected $filter = ['ACTIVE' => 'Y'];

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

    public function executeComponent()
    {
        $nav = $this->getNav();

        try {
            if (!Loader::includeModule("iblock")) {
                return;
            }

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

    protected function selectElements() {
        $nav = $this->getNav();

        $cacheManager = Bitrix\Main\Application::getInstance()->getTaggedCache();
        $cache = Cache::createInstance();
        $cacheId = 'usergroups';
        $cacheDir = '/Simplenews';

        if ($cache->initCache($this->arParams['CACHE_TIME'], $cacheId, $cacheDir)) {
            $vars = $cache->GetVars();
            $elementData = $vars['arResult'];
        } elseif ($cache->startDataCache()) {
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
            $elementData['COUNT'] = $dbElements->getCount();

            $cacheManager->StartTagCache($cacheDir);
            $cacheManager->RegisterTag("iblock_id_1");
            $cacheManager->EndTagCache();

            if (empty($elementData))
                $cache->abortDataCache();

            $cache->endDataCache([
                'arResult' => $elementData,
            ]);
        }

        return $elementData;
    }
}