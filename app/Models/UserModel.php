<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields   = ['nickname', 'password_hash', 'created_at', 'updated_at'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Find user by nickname and verify password.
     *
     * @return array|null User row or null if invalid
     */
    public function authenticate(string $nickname, string $password): ?array
    {
        $user = $this->where('nickname', $nickname)->first();
        if ($user === null || ! password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }
}
