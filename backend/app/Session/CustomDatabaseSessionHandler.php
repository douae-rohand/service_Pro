<?php

namespace App\Session;

use Illuminate\Session\DatabaseSessionHandler;

class CustomDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * Add the user information to the session payload.
     *
     * @param  array  $payload
     * @return $this
     */
    protected function addUserInformation(&$payload)
    {
        if ($this->container->bound('auth')) {
            $payload['utilisateur_id'] = $this->userId();
        }

        return $this;
    }
}
