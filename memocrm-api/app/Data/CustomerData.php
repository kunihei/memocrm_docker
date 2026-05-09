<?php

namespace App\Data;

class CustomerData
{
    public function __construct(
        public readonly string $coName,
        public readonly string $coAddress,
        public readonly string $tantoName,
        public readonly string $tantoTel,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            coName: $data['co_name'],
            coAddress: $data['co_address'],
            tantoName: $data['tanto_name'],
            tantoTel: $data['tanto_tel'],
        );
    }
}
