<?php


namespace Staro\Coro;


interface Handler1 {
    /**
     * @param Stream $current
     * @return Stream[]
     */
    function handle(Stream $current): array;
}
