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
            \Pastapen\Env\Stores\VariableStore::class,
            \Pastapen\Env\Stores\SuperGlobalEnvStore::class,
            \Pastapen\Env\Stores\ProcessEnvStore::class,
        ];
    }


    public function __construct(
        bool $caseSensitive = false,
        ?array $readers = null,
        null|string|WriterInterface $writer = null,
    )
    {
        $this->caseSensitive = $caseSensitive;
        $this->buildWriter($writer ?? $this->getDefaultWriter());

        $readers = $readers ?? $this->getDefaultReaders();
        if(empty($readers)){
            throw new InvalidArgumentException("Reader can't be empty. Please provide atleast one reader instance");
        }

        foreach($readers as $reader){
            $this->buildReader($reader);
        }

    }

    protected function buildWriter(string|WriterInterface $writer): void
    {
        if(is_string($writer)){
            if(!class_exists($writer)){
                throw new InvalidArgumentException("$writer class is not found !");
            }
            
            $writerInstance = new $writer();
            if(!$writerInstance instanceof WriterInterface){
                throw new InvalidArgumentException("$writer is an invalid writer instance !");
            }
        } else {
            $writerInstance = $writer;
        }

        $this->writer = $writerInstance;
        $writerClassName = get_class($writerInstance);
        $this->readers[$writerClassName] = $writerInstance;
    }

    protected function buildReader(string|ReaderInterface $reader): void
    {
        if(is_object($reader)){
            $readerInstance = $reader;
            $reader = get_class($readerInstance);
        } else {
            if(!class_exists($reader)){
                throw new InvalidArgumentException("$reader class is not found !");
            }

            $readerInstance = new $reader();
            if(!$readerInstance instanceof ReaderInterface){
                throw new InvalidArgumentException("$reader is an invalid reader instance !");
            }
        }

        if(!array_key_exists($reader, $this->readers)){
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