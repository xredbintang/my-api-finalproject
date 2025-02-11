<?php

use App\Http\Middleware\VerifyRefreshToken;

app()->singleton('verify.refresh.token', VerifyRefreshToken::class);