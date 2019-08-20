Programmatically remove backgrounds from your images using the remove.bg api.


<p align="center">
<img src="https://i.imgur.com/Rrmusuo.png">
</p>

* [Register an account and obtain your api key](https://www.remove.bg/users/sign_up)
* [Remove.bg Developer Documentation](https://www.remove.bg/api)

## Installation

Install via composer:

```
composer require mtownsend/remove-bg
```

*This package is designed to work with any PHP 7.0+ application but has special support for Laravel.*

### Registering the service provider (Laravel users)

For Laravel 5.4 and lower, add the following line to your ``config/app.php``:

```php
/*
 * Package Service Providers...
 */
Mtownsend\RemoveBg\Providers\RemoveBgServiceProvider::class,
```

For Laravel 5.5 and greater, the package will auto register the provider for you.

### Using Lumen

To register the service provider, add the following line to ``app/bootstrap/app.php``:

```php
$app->register(Mtownsend\RemoveBg\Providers\RemoveBgServiceProvider::class);
```

### Publishing the config file (Laravel users)

````
php artisan vendor:publish --provider="Mtownsend\RemoveBg\Providers\RemoveBgServiceProvider"
````

Once your ``removebg.php`` has been published your to your config folder, add the api key you obtained from [Remove.bg](https://www.remove.bg/). If you are using Laravel and put your remove.bg api key in the config file, Laravel will automatically set your api key every time you instantiate the class through the helper or facade.

## Quick start

### Using the class

```php
use Mtownsend\RemoveBg\RemoveBg;

$absoluteUrl = 'https://yoursite.com/images/photo.jpg';
$pathToFile = 'images/avatar.jpg';
$base64EncodedFile = base64_encode(file_get_contents($pathToFile));

$removebg = new RemoveBg($apiKey);

// Directly saving files
$removebg->url($absoluteUrl)->save('path/to/your/file.png');
$removebg->file($pathToFile)->save('path/to/your/file2.png');
$removebg->base64($base64EncodedFile)->save('path/to/your/file3.png');

// Getting the file's raw contents to save or do something else with
$rawUrl = $removebg->url($absoluteUrl)->get();
$rawFile = $removebg->file($pathToFile)->get();
$rawBase64 = $removebg->base64($base64EncodedFile)->get();

file_put_contents('path/to/your/file4.png', $rawUrl);
// etc...

// Getting the file's base64 encoded contents from the api
$base64Url = $removebg->url($absoluteUrl)->getBase64();
$base64File = $removebg->file($pathToFile)->getBase64();
$base64Base64 = $removebg->base64($base64EncodedFile)->getBase64();

file_put_contents('path/to/your/file5.png', base64_decode($base64Url));
// etc...

// Please note: remove.bg returns all images in .png format, so you should be saving all files received from the api as .png.
```

### Advanced usage

Remove.bg offers several request body parameters for each api call. For an up to date list, you should always check the [remove.bg api documentation](https://www.remove.bg/api).

Here is an example of an api call configured with specific request body parameters.

````php
$removebg = new RemoveBg($apiKey);

// Directly saving files
$removebg->url($absoluteUrl)
->body([
    'size' => '4k', // regular, medium, hd, 4k, auto
    'bg_color' => '#CBD5E0',
    'add_shadow' => true, // primarily used for automotive photos as of the time this documentation was written
    'channels' => 'rgba', // rgba, alpha
])
->save('path/to/your/file.png');
````

You may also directly specify request header parameters. As of right now this does not appear to offer much functionality in terms of how the Remove.bg api will consume these headers, but we thought it was important to expose this functionality. Consider the following example:

````php
$removebg = new RemoveBg($apiKey);

// Directly saving files
$removebg->url($absoluteUrl)
->headers([
    'X-Foo-Header' => 'Some Bar Value',
    'X-Foo-Header-2' => 'Some Bar Value 2',
])
->save('path/to/your/file.png');
````

### Account details

The Remove.bg api offers an endpoint to check your account's credit balance and free api call usage. If your application needs to check your available credits before processing images this package makes it an absolute breeze!

The following code example is how you can programmatically check your account information. Note, the ``account`` method has one optional argument: `$getResponseAsObject = true`. By default your response will be returned as an object. You can return the response as an associative array by passing `false` to the `account(false)` method.

````php
$removebg = new RemoveBg($apiKey);

$account = $removebg->account();

// $account will be something like this:
{
  "data": {
    "attributes": {
      "credits": {
        "total": 200,
        "subscription": 150,
        "payg": 50
      },
      "api": {
        "free_calls": 50,
        "sizes": "all"
      }
    }
  }
}
````

To access your total credits you could do so like this: `$account->data->attributes->credits->total`.

A practical example could look something like the following:

````php
$removebg = new RemoveBg($apiKey);

$account = $removebg->account();

if ($account->data->attributes->credits->total >= 1) {
	$removebg->url($absoluteUrl)->save('path/to/your/file.png');
}
````

### Using the global helper (Laravel users)

If you are using Laravel, this package provides a convenient helper function which is globally accessible.

```php
removebg()->url($absoluteUrl)->save(public_path('path/to/your/file.png'));
```

### Using the facade (Laravel users)

If you are using Laravel, this package provides a facade. To register the facade add the following line to your ``config/app.php`` under the ``aliases`` key.

````php
'RemoveBg' => Mtownsend\RemoveBg\Facades\RemoveBg::class,
````

```php
use RemoveBg;

RemoveBg::file($pathToFile)->save(public_path('path/to/your/file.png'));
```

## Credits

- Mark Townsend
- [All Contributors](../../contributors)

## Testing

*Tests coming soon...*

You can run the tests with:

```bash
./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.