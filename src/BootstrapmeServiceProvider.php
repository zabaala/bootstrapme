<?php namespace Zabaala\Bootstrapme;

use Illuminate\Html\HtmlServiceProvider;

class BootstrapmeServiceProvider extends HtmlServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerHtmlBuilder();

        $this->registerFormBuilder();

        $this->app->alias('html', 'Illuminate\Html\HtmlBuilder');
        $this->app->alias('form', 'Illuminate\Html\FormBuilder');
    }

    /**
     * Register the HTML builder instance.
     *
     * @return void
     */
    protected function registerHtmlBuilder()
    {
        $this->app->singleton('html', function($app)
        {
            return new HtmlBuilder($app['url']);
        });
    }

    /**
     * Register the form builder instance.
     *
     * @return void
     */
    protected function registerFormBuilder()
    {
        $this->app->singleton('form', function($app)
        {
            if (app()->version() <= 5.3) {
                $token = $app['session.store']->getToken();
            } else {
                $token = $app['session.store']->token();
            }

            $form = new FormBuilder($app['html'], $app['url'], $token);

            return $form->setSessionStore($app['session.store']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('html', 'form');
    }

}
