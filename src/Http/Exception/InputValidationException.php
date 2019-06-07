<?php

declare(strict_types=1);

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LC\Portal\Http\Exception;

use Exception;

class InputValidationException extends HttpException
{
    public function __construct(string $message, int $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, [], $previous);
    }
}
