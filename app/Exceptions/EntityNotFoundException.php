<?php

namespace App\Exceptions;

use Exception;

class EntityNotFoundException extends Exception
{
    public function __construct(
        public readonly string $entityType,
        public readonly string|int $identifier,
    ) {
        parent::__construct(
            message: "Entiteti '{$entityType}' me identifikues '{$identifier}' nuk u gjet.",
        );
    }

    /**
     * Return a JSON response for this exception.
     * Used by the exception handler.
     */
    public function render()
    {
        return response()->json(
            data: [
                'message' => $this->message,
                'status' => 404,
                'entity' => $this->entityType,
            ],
            status: 404,
        );
    }
}
