# bxcomp

## Комонент выводит элементы из инфоблока. Кеширование + постраничная навигация + табы по годам

```php
	$APPLICATION->IncludeComponent(
		"company:simplenews.comp", 
		".default", 
		array(
			"COMPONENT_TEMPLATE" => ".default",
			"IBLOCK_ID" => "1",
			"ELEMENT_COUNT" => "",
			"CACHE_TYPE" => "A",
			"CACHE_TIME" => "3600"
		),
		false
	);
```
