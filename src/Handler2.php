<?php
declare(strict_types=1);

namespace Staro\Coro;


interface Handler2 {
    /**
     * @param Stream $current
     * @param mixed $value
     * @return Stream[]
     */
    function handle(Stream $current, $value): array;
}
