<?php
/**
 * # ----
 * #     Yprisoner <yyprisoner@gmail.com>
 * #                   2019/8/10 20:23
 * #                            ------
 **/

namespace MexCaptcha\Contracts;
/**
 * Interface CaptchaInterface
 * @package MexCaptcha\Contracts
 *
 * 验证码生成通用方法
 */
interface CaptchaInterface {

    /**
     * Create captcha image
     * @param string $captcha_id
     * @param string $config
     * @return mixed
     */
    public function create(string $captcha_id, string $config = 'default');


    /**
     * @param string $captcha_id
     * @param string $input_value
     * @return bool
     */
    public function checkCaptcha(string $captcha_id, string $input_value): bool ;

}
