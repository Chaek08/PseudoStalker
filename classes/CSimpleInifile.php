<?php
namespace app\forms\classes;

use app\forms\classes\Debug;

class CSimpleInifile
{
    private $path;
    private $data = [];
    
    private $dirty = false;    

    public function __construct(string $path, array $default = [])
    {
        $this->path = $path;

        if (file_exists($path))
        {
            $this->data = $this->parse(file($path));
        }

        foreach ($default as $key => $value)
        {
            if (!isset($this->data[$key]))
            {
                $this->data[$key] = $value;
            }
        }
    }

    private function parse(array $lines): array
    {
        $data = [];

        foreach ($lines as $line)
        {
            $line = trim($line);

            if ($line === '' || substr($line, 0, 1) === ';')
            {
                continue;
            }
                
            $parts = explode(' ', $line, 2);

            if (count($parts) == 2)
            {
                $key = trim($parts[0]);
                $value = trim($parts[1]);

                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function save(bool $force = false)
    {
        if (!$this->dirty && !$force)
        {
            return;
        }
    
        $out = '';

        foreach ($this->data as $key => $value)
        {
            $out .= "$key $value\n";
        }

        $dir = dirname($this->path);

        if (!is_dir($dir))
        {
            mkdir($dir, 0777, true);
        }
            
        if (!is_writable($dir))
        {
            Debug::fatal("LTX dir not writable");
        }

        file_put_contents($this->path, $out);
    
        $this->dirty = false;
    }

    public function r_string(string $key, $default = '')
    {
        return $this->data[$key] ?? $default;
    }

    public function r_bool(string $key, $default = false)
    {
        $val = $this->r_string($key, $default ? 'on' : 'off');
        return strtolower($val) === 'on';
    }

    public function r_int(string $key, $default = 0)
    {
        return (int)$this->r_string($key, $default);
    }

    public function w_string(string $key, $value)
    {
        $value = (string)$value;
    
        if (!isset($this->data[$key]) || $this->data[$key] !== $value)
        {
            $this->data[$key] = $value;
            $this->dirty = true;
        }
    }

    public function w_bool(string $key, bool $value)
    {
        $this->w_string($key, $value ? 'on' : 'off');
    }

    public function w_int(string $key, int $value)
    {
        $this->w_string($key, $value);
    }

    public function line_exist(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function all(): array
    {
        return $this->data;
    }
}