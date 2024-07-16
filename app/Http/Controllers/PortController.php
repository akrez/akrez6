<?php

namespace App\Http\Controllers;

use App\Enums\Excel\SheetsName;
use App\Services\BlogService;
use App\Services\ProductService;
use App\Supports\Excel;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function __construct(
        protected Excel $excel,
        protected BlogService $blogService,
        protected ProductService $productService
    ) {}

    public function index(Request $request)
    {
        return view('port.index');
    }

    public function export(Request $request)
    {
        $blog = $this->blogService->findOrFailActiveBlog();

        $fileName = date('Y-m-d-H-i-s').'.xlsx';

        return $this->excel->export($fileName, [
            SheetsName::PRODUCTS->value => $this->productService->export($blog),
        ]);
    }

    public function import(Request $request)
    {
        $blog = $this->blogService->findOrFailActiveBlog();

        $port = $request->file('port');

        if ($port and $path = $port->getRealPath()) {

            $source = $this->excel->read($path) + [
                SheetsName::PRODUCTS->value => [],
            ];

            $this->productService->import($blog, $source[SheetsName::PRODUCTS->value]);
        } else {
            $result = null;
        }

        $response = back()->withInput();
        if ($result) {
            $response->withErrors($result->messages);
        }

        return $response;
    }
}
