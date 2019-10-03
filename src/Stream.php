<?php
declare(strict_types=1);

namespace Staro\Coro;


use Generator;

final class Stream {
    /**
     * @var bool
     */
    private $fresh = true;

    /**
     * @var Generator
     */
    private $generator;

    function __construct(Generator $generator) {
        $this->generator = $generator;
    }

    function destruct(): Generator {
        try {
            return $this->generator;
        } finally {
            $this->generator = null;
        }
    }

    /**
     * @return array|null
     */
    function getNext() {
        if (!$this->fresh) {
            $this->generator->next();
        } else {
            $this->fresh = false;
        }
        if ($this->generator->valid()) {
            return [$this->generator->key(), $this->generator->current()];
        }
        # TODO check return value and throw!
        return null;
    }

}
