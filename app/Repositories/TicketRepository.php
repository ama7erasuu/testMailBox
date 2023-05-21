<?php

namespace App\Repositories;

use App\Models\Ticket;

class TicketRepository
{
    /**
     * @param integer $id
     * @return Ticket|null
     */
    public function getTicketById(int $id): ?Ticket
    {
        return Ticket::find($id);
    }

    /**
     * @param array $data
     * @return void
     */
    public function create(array $data): void
    {
        Ticket::create($data);
    }

    /**
     * @param array $data
     * @param int $id
     * @return void
     */
    public function update(array $data, int $id): void
    {
        Ticket::where('id', $id)
            ->update($data);
    }
}
