<?php

namespace abenevaut\SentryHandler\Contracts;

use Symfony\Component\HttpFoundation\Response;

/**
 * Contract to enrich exception thrown by controllers, with capacity to render his message as json.
 *
 * That capacity is provided by Laravel exception Handler::report() method
 * (cf. `Illuminate\Foundation\Exceptions\Handler`).
 */
abstract class HTTPExceptionAbstract extends ExceptionAbstract
{
    /**
     * @return Response
     */
    public function render(): Response
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode());
    }
}
