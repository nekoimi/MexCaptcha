<?php
/**
 * # ----
 * #     Yprisoner <yyprisoner@gmail.com>
 * #                   19-8-9 下午4:23
 * #                            ------
 **/

namespace MexCaptcha\Providers;

use Illuminate\Support\ServiceProvider;
use MexCaptcha\Contracts\CaptchaInterface;
use MexCaptcha\MexCaptcha;

class MexCaptchaServiceProvider extends ServiceProvider {

    /**
     * Register
     */
    public function register () {
        $this->app->singleton('mexcaptcha', function ($app) {
            return new MexCaptcha(
                $app['Illuminate\Filesystem\Filesystem'],
                $app['Illuminate\Config\Repository'],
                $app['Intervention\Image\ImageManager'],
                $app['Illuminate\Hashing\BcryptHasher'],
                $app['Illuminate\Support\Str']
            );
        });
    }


    /**
     * Boot
     */
    public function boot () {
        $this->publishes(array (
            __DIR__ . '/../../config/mexcaptcha.php' => $this->getConfigPath('mexcaptcha.php'),
        ));

        // Validator extensions
        $this->app['validator']->extend('mexcaptcha', function ($attribute, $value, $parameters) {
            if (!is_array($parameters) || sizeof($parameters) <= 0) {
                return false;
            }
            $captcha_id = $parameters[0];
            /**@var CaptchaInterface $mexcaptcha*/
            $mexcaptcha = app('mexcaptcha');
            return $mexcaptcha->checkCaptcha($captcha_id, $value);
        });
    }

    /**
     *
     * @param string $file_name
     * @return string
     */
    protected function getConfigPath (string $file_name = null) {
        if (is_null($file_name)) {
            return $this->app->basePath() . '/config';
        }
        return $this->app->basePath() . '/config/' . ltrim($file_name, '/');
    }

}
