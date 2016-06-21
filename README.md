# BravesheepLiveReloadBundle
A [Symfony](https://symfony.com) bundle that automatically includes a link to
the livereload script in any html response (based on configuration).

## Installation and configuration
Using [Composer](https://getcomposer.org) add the bundle to your dependencies
using the require command. This command adds the bundle only for development
purposes:

    composer require --dev bravesheep/live-reload-bundle

## Add the bundle to your AppKernel
Add the bundle in your `app/AppKernel.php`. To add it to just your development
bundles:

```php
public function registerBundles()
{
    // ...

    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        // ...
        $bundles[] = new Bravesheep\LiveReloadBundle\BravesheepLiveReloadBundle();
    }
    // ...
}
```

## Configure the bundle
The bundly does not need any configuration and should work right away. However
you can customize some settings, you can adjust the default configuration as
shown below:

```yaml
bravesheep_live_reload:
    enabled: yes
    host: ~
    port: 35729
```

You may want to parameterize the enabled flag to allow individual developers to
pick whether or not to enable livereload:

```yaml
bravesheep_live_reload:
    enabled: %livereload%
```

And in your `app/config/parameters.yml`:

```yaml
parameters:
    livereload: yes
```
