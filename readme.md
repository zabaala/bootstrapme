## Bootstrapme Package for Laravel 5

Put your Laravel 5 in action and allow that him use the Bootstrap to make form-groups.

### Requirement
This package requires:
* Laravel 5.*
* illuminate\html (This package dont work with laravelcollective/Html package.)

### How to install

#### 1. Install the package
Add Bootstrapme into package.json. To do this, run  `composer requre zabaala\bootstrapme`.

#### 3. Load Services Providers
After install, open your own `config/app.php` file and place the following code inside of `providers` array key:

```php
    'providers' => [
        // ... (ommited unnecessary list)
        /*
         * Package Service Providers...
         */
        Zabaala\Bootstrapme\BootstrapmeServiceProvider::class,
        // ... (ommited unnecessary list)
    ],
```

And this code inside `aliases` array key:

```php
    'aliases' => [
        // ... (ommited unnecessary list)
        'Form' => Illuminate\Html\FormFacade::class,
        'Html' => Illuminate\Html\HtmlFacade::class,
    ],
```

### Colaborate
Open a pull request and be happy! :)

### Author
Mauricio Rodrigues <mmauricio.vsr@gmail.com>

### License
MIT


