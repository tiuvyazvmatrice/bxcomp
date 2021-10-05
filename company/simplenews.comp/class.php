<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CSimplenewsComp extends \CBitrixComponent
{
    protected $filter = ['ACTIVE' => 'Y', '!ACTIVE_FROM' => null];

    protected $sort = ['ACTIVE_FROM' => 'DESC'];

    protected $select = [
        'ID', 'NAME', 'ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_PICTURE'
    ];

    /**
     * Подготавливаем параметры
     * @param $params
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        if ((int)$params['IBLOCK_ID']) {
            $this->filter['IBLOCK_ID'] = $params['IBLOCK_ID'];
        }
        else {
            throw new \Exception(Loc::getMessage("C_SIMP_NEW_NOIB"));
        }

        if ($params['CACHE_TYPE'] == 'N') {
            $params['CACHE_TIME'] = 0;
        }

        $params["ELEMENT_COUNT"] = (int)$params['ELEMENT_COUNT'] > 0 ? (int)$params['ELEMENT_COUNT'] : 5;

        return $params;
    }

    /**
     * Получаем год, если в GET установлен, то его. Если нет - то текущий
     * Также проставляем значение для фильтра выборки
     * @return int
     */
    private function setFilter(): int
    {
        $year = (int)$this->request->get("YEAR");

        if ($year == 0) {
            $year = date('Y');
        }

        $this->filter['>=ACTIVE_FROM'] = date('01.01.' . $year);
        $this->filter['<=ACTIVE_FROM'] = date('31.12.' . $year);

        return (int)$year;
    }

    /**
     * Главный метод
     */
    public function executeComponent()
    {
        $nav = $this->getNav();

        try {
            if (!Loader::includeModule("iblock")) {
                return;
            }
            $currentYear = $this->setFilter();
            $res = $this->selectElements();
            $this->arResult = $res;
            $nav->setRecordCount($this->arResult['COUNT']);
            $this->arResult['NAV'] = $nav;
            $this->arResult['CURRENT_YEAR'] = $currentYear;

            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            echo $e->getMessage();
            return;
        }
    }

    /**
     * Строим навигацию
     * @return PageNavigation
     */
    protected function getNav()
    {
        $nav = new PageNavigation("users_page");
        $nav->allowAllRecords(true)->setPageSize($this->arParams["ELEMENT_COUNT"])->initFromUri();

        return $nav;
    }

    /**
     * Устанавливаем заголовок с кол-вом новостей в выбранном году
     * @param int $count
     */
    protected function setTitle(int $count): void
    {
        global $APPLICATION;

        $APPLICATION->SetTitle(str_replace('#COUNT#', $count, Loc::getMessage("C_SIMP_NEW_COUNT")));
    }

    /**
     * Получаем все годы, в которые были новости
     * @return array
     */
    protected function getNewsYears(): array {
        $dbYears = Bitrix\Iblock\ElementTable::GetList([
            'select' => ['YEARS'],
            'order' => ['YEARS' => 'DESC'],
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
            $elementData[] = $arYear['YEARS'];
        }

        return $elementData;
    }

    /**
     * Получаем новости
     * @return array
     */
    protected function selectElements(): array
    {
        $nav = $this->getNav();

        $cacheManager = Bitrix\Main\Application::getInstance()->getTaggedCache();
        $cache = Cache::createInstance();

        $cacheId = md5('simplenews' . serialize($this->filter) . serialize($this->arParams) . serialize($nav));
        $cacheDir = '/simplenews';

        if ($cache->initCache($this->arParams['CACHE_TIME'], $cacheId, $cacheDir)) {
            $vars = $cache->GetVars();
            $elementData = $vars['arResult'];
        } elseif ($cache->startDataCache()) {

            $elementData['YEARS'] = $this->getNewsYears();

            $dbElements = \Bitrix\Iblock\ElementTable::GetList([
                'select' => $this->select,
                'order' => $this->sort,
                'filter' => $this->filter,
                'count_total' => true,
                'limit' => $nav->getLimit(),
                'offset' => $nav->getOffset(),
            ]);

            while ($arElement = $dbElements->Fetch()) {
                $elementData['ITEMS'][] = $arElement;
            }

            foreach ($elementData['ITEMS'] as $key => $item) {
                $elementData['ITEMS'][$key]['PICTURE'] = CFile::GetPath($item['PREVIEW_PICTURE']);
            }

            $elementData['COUNT'] = $dbElements->getCount();

            $cacheManager->StartTagCache($cacheDir);
            $cacheManager->RegisterTag("iblock_id_" . $this->arParams['IBLOCK_ID']);
            $cacheManager->EndTagCache();

            if (empty($elementData))
                $cache->abortDataCache();

            $cache->endDataCache([
                'arResult' => $elementData,
            ]);
        }

        $this->setTitle($elementData['COUNT']);

        return $elementData;
    }
}