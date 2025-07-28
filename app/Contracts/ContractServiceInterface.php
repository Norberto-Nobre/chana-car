<?php

namespace App\Contracts;

use App\Models\Booking;
use App\Models\Contract;

interface ContractServiceInterface
{
    public function generateContract(Booking $booking): Contract;
    public function getContractPath(Contract $contract): string;
}