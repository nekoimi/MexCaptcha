<?php
/**
 * # ----
 * #     Yprisoner <yyprisoner@gmail.com>
 * #                   2019/8/10 20:18
 * #                            ------
 **/

namespace MexCaptcha\Contracts;

/**
 * Interface CacheHandlerInterface
 * @package MexCaptcha\Contracts
 *
 * 外部缓存接口
 */
interface CacheHandlerInterface {

    /**
     * Get cache value by cache Key.
     *
     * @param string $key
     * @return string
     */
    public function get(string $key): string;


    /**
     * Set a cache.
     *
     * @param string $key
     * @param string $value
     * @param int $expired_at 过期时间（秒钟）
     */
    public function set(string $key, string $value, int $expired_at);


    /**
     * Determine if the cache exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;


    /**
     * Remove s cache by Key.
     *
     * @param string $key
     */
    public function del(string $key);

}
