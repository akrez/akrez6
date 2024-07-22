<?php

namespace App\Services;

use App\DTO\ProductPropertyDTO;
use App\Facades\ArrayHelper;
use App\Models\Blog;
use App\Models\Product;

class ProductPropertyService
{
    const MAX_LENGTH = 32;

    const SEPARATOR_LINES = [PHP_EOL];

    const SEPARATOR_KEY_VALUES = [':', ',', '،', "\t"];

    const GLUE_LINES = PHP_EOL;

    const GLUE_KEY_VALUES = ':';

    const GLUE_VALUES = ',';

    public function getLatestProductsWithProperties(Blog $blog)
    {
        $blog->load([
            'products' => function ($query) {
                $query->with('productProperties', function ($query) {
                    $query->latest('created_at');
                })->latest('created_at');
            },
        ]);

        return $blog->products;
    }

    public function getLatestProductProperties(Product $product): \Illuminate\Database\Eloquent\Collection
    {
        $product->load([
            'productProperties' => function ($query) {
                $query->latest('created_at');
            },
        ]);

        return $product->productProperties;
    }

    public function exportToTextArea(Product $product)
    {
        $properties = $this->getLatestProductProperties($product);

        $keyToValues = [];
        foreach ($properties as $property) {
            $keyToValues[$property->property_key][$property->property_value] = $property->property_value;
        }

        $lines = [];
        foreach ($keyToValues as $key => $values) {
            $lines[] = $key.static::GLUE_KEY_VALUES.' '.implode(static::GLUE_VALUES.' ', $values);
        }

        return implode(static::GLUE_LINES, $lines);
    }

    public function exportToExcel(Blog $blog)
    {
        $source = [];

        $source[] = [
            __('validation.attributes.code'),
            __('validation.attributes.name'),
            __('validation.attributes.property_key'),
            __('validation.attributes.property_value'),
        ];

        $products = $this->getLatestProductsWithProperties($blog);
        foreach ($products as $product) {
            $properties = $product->productProperties()->get()->groupBy('property_key');
            if ($properties->isEmpty()) {
                $source[] = [
                    $product->code,
                    $product->name,
                ];
            } else {
                foreach ($properties as $propertyKey => $property) {
                    $source[] = [
                        $product->code,
                        $product->name,
                        $propertyKey,
                        ...$property->pluck('property_value')->toArray(),
                    ];
                }
            }
        }

        return $source;
    }

    public function importFromExcel(Blog $blog, array $rows)
    {
        $rows = $rows + [0 => []];
        unset($rows[0]);
        //
        $stringLinesArrays = [];
        foreach ($rows as $row) {
            $row += array_fill(0, 3, '');
            //
            $productCode = trim($row[0]);
            $stringLinesArrays[$productCode][] = array_slice($row, 2);
        }
        //
        foreach ($stringLinesArrays as $productCode => $keyAndValuesArray) {
            $product = resolve(ProductService::class)->firstProductByCode($blog, $productCode);
            if ($product) {
                $this->importfromArray($blog, $product, $keyAndValuesArray);
            }
        }
    }

    public function importFromTextArea(Blog $blog, Product $product, array $stringLines)
    {
        $keyAndValuesArray = [];
        foreach ($stringLines as $stringLine) {
            $keyAndValuesArray[] = ArrayHelper::iexplode(static::SEPARATOR_KEY_VALUES, $stringLine);
        }
        $this->importfromArray($blog, $product, $keyAndValuesArray);
    }

    public function importfromArray(Blog $blog, Product $product, array $keyAndValuesArray)
    {
        $keyToValuesArray = [];
        foreach ($keyAndValuesArray as $keyAndValues) {
            $keyAndValues += array_fill(0, 2, '');
            //
            $key = trim($keyAndValues[0]);
            //
            if (! array_key_exists($key, $keyToValuesArray)) {
                $keyToValuesArray[$key] = [];
            }
            //
            $keyToValuesArray[$key] = array_merge($keyToValuesArray[$key], array_slice($keyAndValues, 1));
        }
        $this->import($blog, $product, $keyToValuesArray);
    }

    protected function import(Blog $blog, Product $product, array $keyToValuesArray)
    {
        $this->delete($product);
        $dtos = $this->filter($keyToValuesArray);
        $this->insert($blog, $product, $dtos);
    }

    protected function delete(Product $product)
    {
        $product->productProperties()->delete();
    }

    protected function filter(array $keyToValuesArray)
    {
        $dtos = [];
        foreach ($keyToValuesArray as $key => $values) {
            $keyToValuesArray[$key] = collect($values)
                ->flatten()
                ->map(fn ($value) => trim($value))
                ->filter()
                ->unique()
                ->toArray();
            foreach ($keyToValuesArray[$key] as $safeValue) {
                $dto = new ProductPropertyDTO($key, $safeValue);
                if ($dto->validate()->errors()->isEmpty()) {
                    $dtos[] = $dto;
                }
            }
        }

        return $dtos;
    }

    /**
     * @param  array<int, ProductPropertyDTO>  $dtos
     */
    protected function insert(Blog $blog, Product $product, array $productPropertyDtos): array
    {
        $result = [];
        foreach ($productPropertyDtos as $productPropertyDto) {
            $result[] = $blog->productProperties()->create([
                'property_key' => $productPropertyDto->property_key,
                'property_value' => $productPropertyDto->property_value,
                'product_id' => $product->id,
            ])->toArray();
        }

        return $result;
    }
}
