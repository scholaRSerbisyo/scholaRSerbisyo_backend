<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scholar;
use App\Services\CloudflareR2Service;

class ScholarProfileController extends Controller
{
    protected $r2Service;

    public function __construct(CloudflareR2Service $r2Service) {
        $this->r2Service = $r2Service;
    }
    public function updateScholar() {

    }
}
