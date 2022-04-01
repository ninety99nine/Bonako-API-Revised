<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Repositories\ProductRepository;
use App\Http\Requests\Models\DeleteRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Product\UpdateProductRequest;
use App\Http\Requests\Models\Product\CreateVariationsRequest;

class ProductController extends BaseController
{
    /**
     *  @var ProductRepository
     */
    protected $repository;

    public function index(Request $request)
    {
        return response($this->repository->get()->transform(), 200);
    }

    public function show(Product $product)
    {
        return response($this->repository->setModel($product)->transform(), 200);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        return response($this->repository->setModel($product)->update($request->all())->transform(), 200);
    }

    public function confirmDelete(Product $product)
    {
        return response($this->repository->setModel($product)->generateDeleteConfirmationCode(), 200);
    }

    public function delete(DeleteRequest $request, Product $product)
    {
        return response($this->repository->setModel($product)->delete(), 204);
    }

    public function showVariations(Product $product)
    {
        return response($this->repository->setModel($product)->showVariations(), 200);
    }

    public function createVariations(CreateVariationsRequest $request, Product $product)
    {
        return response($this->repository->setModel($product)->createVariations($request), 201);
    }
}
