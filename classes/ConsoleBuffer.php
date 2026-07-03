<?php
namespace app\forms\classes;

class ConsoleBuffer
{
    protected $queue = [];

    protected $readIndex = 0;

    public function add(string $line)
    {
        $this->queue[] = $line;
    }

    public function clear()
    {
        $this->queue = [];
        $this->readIndex = 0;
    }

    public function isEmpty(): bool
    {
        return $this->readIndex >= count($this->queue);
    }

    public function count(): int
    {
        return count($this->queue) - $this->readIndex;
    }

    public function getChunk(int $maxLines = 100): string
    {
        if ($this->isEmpty())
        {
            return '';
        }

        $result = '';

        $end = min($this->readIndex + $maxLines, count($this->queue));

        for ($i = $this->readIndex; $i < $end; $i++)
        {
            $result .= $this->queue[$i];
        }

        $this->readIndex = $end;

        if ($this->readIndex >= count($this->queue))
        {
            $this->queue = [];
            $this->readIndex = 0;
        }

        return $result;
    }
}