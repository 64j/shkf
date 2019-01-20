# shkf

#### Вывод сниппета корзины
```html
[!shkfCart!]
```
-----

#### Общие параметры для всех корзин на странице, задаются в конфигурации сниппета.
##### prefix
Префикс для ajax-запросов к обработчику корзины и префикс для параметров товаров добавляемых в корзину.

Значение по умолчанию - ```shkf```

##### tvPrice
ТВ параметр для вывода цены товара

Значение по умолчанию - ```price```

##### price_decimals
Количество символов после запятой в плесхолдерах с ценой

Значение по умолчанию - ```0```

##### price_thousands_sep
Разделитель тысячных для цены

Значение по умолчанию - ```&nbsp;```

##### prepareTpl
Функция или сниппет для обработки товара в корзине

Значение по умолчанию - ```пусто```

##### prepareWrap
Функция или сниппет для обработки вывода корзины

Значение по умолчанию - ```пусто```

----

#### Основные параметры
##### id
id корзины. Если оставить пустым, сгенерируется автоматически, и будет корректно работать только с выключенным параметром async, то есть равным 0

Значение по умолчанию - ```cart_<уникальный ID>```

##### async
Тип загрузки корзины. При значении = 1, корзину загружает и обрабатывает JS.

Значение по умолчанию - ```0```

##### dataType
Тип данных.
Возможные значения - html, json, info
- ```html``` - выводит сгенерированный код html
- ```json``` - обрабатывается на стороне клиента с помощью JS
- ```info``` - выводит только общие суммы и количества товаров

Значение по умолчанию - ```html```

#### Параметры для отображения корзины.
Весь рендер корзины построен на сниппете DocLister, поэтому все параметры доступны для использования.

##### ownerTPL
Обёртка товаров в одной корзине. У каждой корзины может быть свой шаблон для обёртки.

Значение по умолчанию - ```@CODE:<div id="[+cart.id+]">[+cart.count+]</div>```

##### noneTPL
Обёртка для пустой корзины

Значение по умолчанию - ```ownerTPL```

##### tpl
Шаблон вывода товара

Значение по умолчанию - ```@CODE:<a href="[+url+]">[+pagetitle+]</a><br />```

##### tplParams
Обёртка параметров в корзине

Значение по умолчанию - ```@CODE:<div>[+params+]</div>```

##### tplParam
Шаблон параметра в корзине

Значение по умолчанию - ```@CODE:[+param+]<br>```

_Добавлено только два новых параметра ```&tplParams``` и ```&tplParam```, для шаблонизации параметров товара в корзине._

-----
#### Плейсхолдеры в корзине


