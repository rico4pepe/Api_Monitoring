<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankMessage extends Model
{
    //
    protected $table = 'bank_messages';

     /**
     * This table does not belong to our domain,
     * so we allow mass assignment freely.
     */
    protected $guarded = [];

      /**
     * We rely on created_at / updated_at
     */
    public $timestamps = true;

      /**
     * Dynamically switch database connection.
     */
    public function useConnection(string $connection): self
    {
        $this->setConnection($connection);
        return $this;
    }

     /**
     * Dynamically switch table name.
     */
    public function useTable(string $table): self
    {
        $this->setTable($table);
        return $this;
    }
}
