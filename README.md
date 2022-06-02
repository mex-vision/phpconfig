# PhpConfig

**PhpConfig** - удобная работа с конфигурацией вашего проекта.

## Установка
Установка с помощью composer:
```
composer require mex-vision/phpconfig
```
## Использование
### Инициализация
```PHP
    use PhpConfig\Config;
    use PhpConfig\ConfigProvider;
    
    # Инициализация провайдера.
    $provider = new ConfigProvider('path/to/config', '.cfg');
    
    # Инициализация конфигурации.
    $config = new Config($provider);
```
### Чтение
```PHP
    # $result = include 'path/to/config/site.cfg.php';
    # $result = $result['template'];
    $result = $config->get('site.template');
    
    # Передать значение по умолчанию.
    $result = $config->get('site.template', 'default');
    
    # Или
    $result = $config->get('site.template', function(){
        return 'default';
    });
```
### Редактирование
```PHP
    # Запись в контейнер с конфигами.
    $config->set('site.template', 'new_template');
    
    # Или
    $config->set('site.template', [
        'site_template' => 'new_site_template'
    ]);
```
### Сохранение
Для сохранения конфигов вам необходимо передать название файла, **без суфикса**.
```PHP
    # Сохраняет значение ключа site в 'path/to/config/site.cfg.php'.
    $config->save('site');
    
    # Сохраняет все конфиги.
    $config->save();
```

### Источники конфигов
Если Вам необходимо использовать несколько дирректорий для хранения ваших конфигов, Вы можете использовать несколько провайдеров для обращения к ним.
```PHP
    # Инициализируем новый провайдер.
    $newProvider = new ConfigProvider('another/path/to/config', '.cfg');
    
    # Добавляем его в наш объект и устанавливаем namespace для работы с ним.
    $cfg->addProvider('another', $newProvider);
    
    # Чтение.
    $result = $config->get('@another.site.template');
    
    # Редактирование.
    $config->set('@another.site.template', 'new_template');

    # Сохраниение.
    $config->save('@another.site');
    
    # Сохраниение всех конфигов с провайдера.
    $config->save('@another');
```
