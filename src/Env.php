<?php

namespace Pastapen\Env;

use InvalidArgumentException;
use Pastapen\Env\Contracts\ReaderInterface;
use Pastapen\Env\Contracts\WriterInterface;
use Pastapen\Env\Exceptions\ImmutableEnvironmentException;

class Env
{
    protected bool $locked = false;

    protected bool $caseSensitive = false;

    protected array $reader = [];

    protected WriterInterface $writer;

    protected function getDefaultWriter(): string
    {
        return \Pastapen\Env\Stores\VariableStore::class;
    }

    protected function getDefaultReader(): array
    {
        return [
            \Pastapen\Env\Stores\VariableStore::class
        ];
    }


    public function __construct(
        bool $caseSensitive = false,
        ?array $reader = null,
        ?string $writer = null,
    )
    {
        $this->caseSensitive = $caseSensitive;

        if(!$reader){
            $reader = $this->getDefaultReader();
        }

        if(!$writer){
            $writer = $this->getDefaultWriter();
        }
        $writerInstance = new $writer();
        if(!$writerInstance instanceof WriterInterface){
            throw new InvalidArgumentException("$writer is an invalid writer instance !");
        }
        $this->writer = $writerInstance;
        $this->reader[$writer] = $writerInstance;
        
        foreach($reader as $readerClassName){
            if(array_key_exists($readerClassName, $this->reader)){
                continue;
            }

            $readerInstance = new $readerClassName();

            if(!$readerInstance instanceof ReaderInterface){
                throw new InvalidArgumentException("$readerClassName is an invalid reader instance !");
            }

            $this->reader[$readerClassName] = $readerInstance;
        }
    }

    public function set(string $key, mixed $value): void
    {
        if($this->locked){
            throw new ImmutableEnvironmentException();
        }

        $key = $this->normalizeKey($key);
        $this->writer->set($key, $value);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->normalizeKey($key);

        foreach($this->reader as $reader){
            if($reader->has($key)){
                return $reader->get($key, $default);
            }
        }

        return $default;
    }

    protected function normalizeKey(string $key): string
    {
        $key = trim($key);

        if(empty($key)){
            throw new InvalidArgumentException("Environment key can't be empty");
        }

        if(!$this->caseSensitive){
            $key = strtoupper($key);
        }

        return $key;
    }

    public function has(string $key): bool
    {
        $key = $this->normalizeKey($key);

        foreach($this->reader as $reader){
            if($reader->has($key)){
                return true;
            }
        }

        return false;
    }

    public function lock(): void
    {
        if($this->writer->supportsLock()){
            $this->locked = true;
        }
    }

    public function unlock(): void
    {
        $this->locked = false;
    }

    public function all(): array
    {
        $result = [];
        foreach(array_reverse($this->reader) as $reader){
            $result = [...$result, ...$reader->all()];
        }
        
        return $result;
    }
}