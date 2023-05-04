**Получение ID инфоблока по символьному коду с кешированием в файл.**
***PHP >= 7.3***

Скрипт подразумевает, что у инфоблоков заполнено поле CODE.

Пример composer.json для установки модуля
```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/MyLittleVerdant/iblock-by-code"
    }
  ],
  "require": {
    "inner-modules/iblock-by-code": "dev-master"
  }
}
```