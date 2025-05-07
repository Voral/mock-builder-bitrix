# Конфигурация генератора моков

Для генерации моков Битрикс при помощи [voral/mock-builder](https://github.com/Voral/mock-builder) можно воспользоваться вспомогательным классом `Vasoft\MockBuilderBitrix\Configurator` и использовать его в файле настроек `.vs-mock-builder.php` 

В качестве параметра конструктора классу необходимо передать путь к каталогу содержащему модули Битрикс. Далее можно использовать ряд методов.

## getBitrixVisitor

Возвращает массив рекомендуемых AST визиторов. В качестве параметров принимает целевую версию PHP (по умолчанию 8.1)

```php
$configurator = new Vasoft\MockBuilderBitrix\Configurator('/home/bitrix/www/bitrix/modules');
return [
    'visitors' => $configurator->getBitrixVisitors('8.4'),
];
```

## getPaths

Возвращает пути к исходникам для создания моков. Принимает три параметра:

**modules** - массив. Содержит имена модулей, для которых необходимо генерировать моки. По умолчанию для всех, которые будут найдены в каталоге источнике

**excludeModules** - массив. Содержит имена модулей, которые будут исключены из генерации

**bitrixOnly** - boolean. `true` (по умолчанию) - значит будут обрабатываться только битрикс модули (определение упрощенное - если в имени каталога отсутствует точка, считается bitrix-модулем)   

```php
$configurator = new Vasoft\MockBuilderBitrix\Configurator('/home/bitrix/www/bitrix/modules');
return [
    'basePath' => $configurator->getPaths(['main','sale']),
];
```

### getBitrixMockBuilderSettings

Генерация достаточного конфига. Заполняются ключи `targetPath`,`basePath` и `visitors`. в качестве параметров может получать:

**destinationPath** - целевой путь где будут размещены сгенерированные моки  

**modules** - массив. Содержит имена модулей, для которых необходимо генерировать моки. По умолчанию для всех, которые будут найдены в каталоге источнике

**excludeModules** - массив. Содержит имена модулей, которые будут исключены из генерации

**targetPhpVersion** - строка. Целевая версия PHP (по умолчанию 8.1)

```php
declare(strict_types=1);

use Vasoft\MockBuilderBitrix\Configurator;

$bitrixModulesPaths = '';
$local = __DIR__ . '/.vs-mock-builder.local.php';
if (file_exists($local)) {
    // реальный путь рекомендую исключить из git репозитория
    // и добавить .vs-mock-builder.local.php в .gitignore
    $bitrixModulesPaths = require_once $local;
}

$configurator = new Configurator($bitrixModulesPaths);

return $configurator->getBitrixMockBuilderSettings(
    __DIR__ . '/bx/', 
    excludeModules:['sale', 'catalog'], 
    targetPhpVersion: '8.3'
);
```


