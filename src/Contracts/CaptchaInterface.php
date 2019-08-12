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
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function create(string $captcha_id, int $width = 0, int $height = 0);


    /**
     * @param string $captcha_id
     * @param string $input_value
     * @return bool
     */
    public function checkCaptcha(string $captcha_id, string $input_value): bool ;

}
