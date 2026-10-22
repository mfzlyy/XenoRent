<?php

namespace App\Domain\Exceptions;

class RentalNotFoundException extends DomainException
{
    public function __construct(string $message = 'Transaksi sewa tidak ditemukan')
    {
        parent::__construct($message);
    }
}
