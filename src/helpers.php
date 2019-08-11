<?php
/**
 * # ----
 * #     Yprisoner <yyprisoner@gmail.com>
 * #                   2019/8/11 9:10
 * #                            ------
 **/ 

if (! function_exists('mexcaptcha')) {
    /**
     * @return \MexCaptcha\Contracts\CaptchaInterface
     */
    function mexcaptcha() {
        return app('mexcaptcha');
    }
}


if (! function_exists('mexcaptcha_uuid')) {
    /**
     * @return string
     */
    function mexcaptcha_uuid(): string {
        return \MexCaptcha\Support\Str::generate_uuid();
    }
}
