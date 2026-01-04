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

    protected array $readers = [];

    protected WriterInterface $writer;

    protected function getDefaultWriter(): string
    {
        return \Pastapen\Env\Stores\VariableStore::class;
    }

    protected function getDefaultReaders(): array
    {
        return [
            \Pastapen\Env\Stores\VariableStore::class
        ];
    }


    public function __construct(
        bool $caseSensitive = false,
        ?array $readers = null,
        ?string $writer = null,
    )
    {
        $this->caseSensitive = $caseSensitive;
        $this->buildWriter($writer ?? $this->getDefaultWriter());
        $this->buildReaders($readers ?? $this->getDefaultReaders());
    }

    protected function buildWriter(string $writer): void
    {
        if(!class_exists($writer)){
            throw new InvalidArgumentException("$writer class is not found !");
        }
        
        $writerInstance = new $writer();
        if(!$writerInstance instanceof WriterInterface){
            throw new InvalidArgumentException("$writer is an invalid writer instance !");
        }
        $this->writer = $writerInstance;
        $this->readers[$writer] = $writerInstance;
    }

    protected function buildReaders(array $readers): void
    {
        if(empty($readers)){
            throw new InvalidArgumentException("Reader can't be empty. Please provide atleast one reader instance");
        }

        foreach($readers as $reader){
            if(array_key_exists($reader, $this->readers)){
                continue;
            }

            if(!class_exists($reader)){
                throw new InvalidArgumentException("$reader class is not found !");
            }

            $readerInstance = new $reader();

            if(!$readerInstance instanceof ReaderInterface){
                throw new InvalidArgumentException("$reader is an invalid reader instance !");
            }

            $this->readers[$reader] = $readerInstance;
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

        foreach($this->readers as $reader){
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

        foreach($this->readers as $reader){
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
        foreach(array_reverse($this->readers) as $reader){
            $result = [...$result, ...$reader->all()];
        }
        
        return $result;
    }
}