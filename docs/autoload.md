# Автозагрузка

## Вспомогательный класс загрузки

`Vasoft\MockBuilderBitrix\Autoloader`

Вспомогательный инструмент для загрузки моков ядра битрикс и моков функций.

### Параметры конструктора

- **path** - путь в котором расположены моки ядра
- **classes** - необязательный. Массив соответствия классов и файлов их содержащих. Можно использовать для фиксированной
  установки в каких файлах искать отдельные классы. Ключ - полное имя класса, значение - полный путь к файлу содержащему
  класс. Поиск классов происходит сначала в этом параметре, далее согласно PSR-4
- **includes** - массив дополнительно подключаемых файлов

### Методы конструктора

#### register

Регистрирует автозагрузчик.

```php
(new \Vasoft\MockBuilderBitrix\Autoloader(__DIR__.'/bx/'))
    ->register();
```

#### includes

Выполняет подключение дополнительных файлов. В частности подключает файл с моками функций Битрикс (файл моков будет
постепенно наполняться, сейчас реализованы не все фунrции), а так же файлы из параметра конструктора

```php
(new \Vasoft\MockBuilderBitrix\Autoloader(
    __DIR__.'/bx/',
    [
        __FIL__.'/functions/my-extensions.php',    
        __FIL__.'/functions/other-extensions.php',    
    ]   
))
    ->includes();
```

#### registerAll

Выполняет регистрацию автозагрузчика и подключение файлов

## Настройка загрузки для PHPUnit

Для того чтобы моки подключались во время тестирования можно создать файл `tests/bootstrap.php` примерно со следующим
содержанием

```php
declare(strict_types=1);

use Vasoft\MockBuilderBitrix\Autoloader;

$path = realpath(__DIR__ . '/../vendor/voral/mock-builder-bitrix/src/') . '/Autoloader.php';

if (file_exists($path)) {
    include_once $path;
    (new Autoloader(__DIR__ . '/../bx'))->registerAll();
} else {
    exit('voral/mock-builder-bitrix not found');
}
```

Указать его в настройках phpunit.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php">
</phpunit>
```

## Настройка PHPStan

Так же можно создать phpstan-bootstrap.php аналогичный конфигурации для PHPUnit. И указать его в настройках
.phpstan.neon.dist

```yaml
parameters:
  bootstrapFiles:
    - phpstan-bootstrap.php
```
