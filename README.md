# Laravel VK Geo
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/bigperson/laravel-vk-geo/master/LICENSE)
[![StyleCI](https://styleci.io/repos/93866951/shield?branch=master)](https://styleci.io/repos/93866951)

Пакет предоставляет удобный способ ипорта стран, регионов и городов используя api vk.com.

Пакет использует [atehnix/vk-client](https://github.com/atehnix/vk-client) для выполнения запросов. Используются **синхронные запросы**, если вы хотите постоянно обновлять данные, то используйте пакет [atehnix/laravel-vk-requester](https://github.com/atehnix/laravel-vk-requester)

## Содержание
* Установка
* Импорт данных
* Использование

## Установка
Вы можете установить данный пакет с помощью сomposer:

```
composer require bigperson/laravel-vk-geo
```

Далее необходимо зарегистровать новый сервис-провайдер в config/app.php:

```php
...
'providers' => [
    ...
     Bigperson\VkGeo\VkGeoServiceProvider::class,
],
...
```

### Конфигурация
Сначала необходимо создать необходимые таблицы в базе данных, для этого импортируйте файлы миграций из пакета используя artisan:

```
php artisan vendor:publish --provider=Bigperson\VkGeo\VkGeoServiceProvider
```
Также создастся файл конфигурации `config/vk-geo.php`. После чего необходимо применить миграции:
```
php artisan migrate
```
В `.env` необходимо добавить, данные вашего vk приложения:
```
VKONTAKTE_KEY=
VKONTAKTE_SECRET=
VKONTAKTE_REDIRECT_URI=
```
Также для выполнения импорта получить токен ([Где взять api токен?](https://github.com/atehnix/laravel-vk-requester#Где-взять-api-токен)) приложения и добавить в `.env`:
```
VKONTAKTE_TOKEN=
```
Либо переопределить токен в `config/vk-geo.php`.

## Импорт осуществляется через консоль.
### Импорт всех стран
```
php artisan vk:import-countries
```
### Импорт регионов
Импорт регионов для всех стран
```
php artisan vk:import-regions
```
Возможен также и импорт для отдельных стран по их id
```
php artisan vk:import-regions --countryId=1 --countryId=2
```

### Импорт городов

Импорт городов для отдельных стран
```
php artisan vk:import-cities --countryId=1 --countryId=2
```
Импорт городов для отдельных регионов
```
php artisan vk:import-cities --regionId=1014032 --regionId=1048584
```

Если вам нужен импорт для всех стран и всех регионов, то можно запустить компанду без параметров, но данный способ не тестировался, и скорее всего будут ошибки связанные с ответом от серверов VK. Вы также можете переопределить любую из консольных команд, создав собсвтенные и отнаследовавшись от оригинальных.

## Использование

Использовать пакет достаточно просто. В пакет входят eloquent модели города, региона и страны (City, Region, Country). Вы можете вызывать модели в контроллерах:
```php
namespace App\Http\Controllers;

use Bigperson\VkGeo\Models\City;

class Controller
{
    protected function show($name){

        $city = City::where('title', $name)->first();

    }
}
```
У каждой модели есть `title`, `id`, у городов есть `area` (район), также настроенны связи между моделями. При необходимости можете также переопределить их.


## Лицензия
[MIT](https://raw.github.com/bigperson/laravel-vk-geo/master/LICENSE)
