<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Message\Encoding;

/**
 * Transform a regular stream into a chunked one.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class ChunkStream extends FilteredStream
{
    protected function readFilter(): string
    {
        return 'chunk';
    }

    protected function writeFilter(): string
    {
        return 'dechunk';
    }

    protected function fill(): void
    {
        parent::fill();

        if ($this->stream->eof()) {
            $this->buffer .= "0\r\n\r\n";
        }
    }
}
