<?php

namespace App\Http\Controllers\Web;

use Strux\Component\Http\Controller\Web\Controller;
use Strux\Component\Http\Response;

class TestInvokeMethodController extends Controller
{
    public function __invoke(): Response
    {
        return $this->json([
            'message' => 'This is a test response from the invoke method.',
            'status' => 'success'
        ], 200);
    }
}