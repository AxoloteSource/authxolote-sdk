<?php

namespace Authxolote\Sdk\Enums;

enum Http: int
{
    case Ok = 200;
    case Created = 201;
    case BadRequest = 400;
    case Unauthorized = 401;
    case Forbidden = 403;
    case NotFound = 404;
    case InternalServerError = 500;
}
