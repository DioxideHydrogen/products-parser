<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImportControl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

	private static string $urlWithTextListOfJsonGzipedFiles = "https://challenges.coode.sh/food/data/json/index.txt";

	private static string $urlToDownloadJsonGzipedFiles = "https://challenges.coode.sh/food/data/json/";

	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{

		$products = Product::paginate(\env('PAGINATION_LIMIT'));

		return \response()->json($products);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(int $product)
	{

		$product = Product::where('code', $product)->first();

		if(!$product) return \response()->json([
			"error" => "Product not found"
		], 404);

		return \response()->json($product);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, int $product)
	{
		
		$product = Product::where('code', $product)->first();

		if(!$product) return \response()->json([
			"error" => "Product not found"
		], 404);

		$validator = Validator::make($request->all(),[
			"status" => 'nullable|in:published,trash', 
			"url" => 'nullable|url', 
			"creator" => 'nullable|string', 
			"product_name" => 'nullable|string', 
			"quantity" => 'nullable|string', 
			"brands" => 'nullable|string', 
			"categories" => 'nullable|string', 
			"labels" => 'nullable|string', 
			"cities" => 'nullable|string', 
			"purchase_places" => 'nullable|string', 
			"stores" => 'nullable|string', 
			"ingredients_text" => 'nullable|string', 
			"traces" => 'nullable|string', 
			"serving_size" => 'nullable|string', 
			"serving_quantity" => 'nullable|string', 
			"nutriscore_score" => 'nullable|string', 
			"nutriscore_grade" => 'nullable|string', 
			"main_category" => 'nullable|string', 
			"image_url" => 'nullable|url'
		]);

		if($validator->fails()) return \response()->json([
			"error" => $validator->errors()
		], 400);

		$validated = $validator->validated();

		$product->fill($validated);

		$product->updated_t = now()->format('Y-m-d\TH:i:sP');

		$product->save();

		return \response()->json($product);

	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(int $product)
	{
		
		$product = Product::where('code', $product)->first();

		if(!$product) return \response()->json([
			"error" => "Product not found"
		], 404);

		$product->status = 'trash';

		$product->save();

		return \response()->json([
			"message" => "Product deleted"
		]);

	}

	/**
	 * Update the products from the json ziped files.
	 */
	public function updateProducts()
	{

		$files = $this->getJsonGzipedFiles();

		foreach ($files as $file) {

			if(empty($file)) continue;

			$jsonFileName = \str_replace('.gz', '', $file);

			$productImportControl = ProductImportControl::where('file_name', $jsonFileName)->first();

			if ($productImportControl) {

				$lastImported = Carbon::parse($productImportControl->imported_t);

				$now = Carbon::now();

				if ($lastImported->format('Y-m-d') === $now->format('Y-m-d')) continue;
				
			}
			
			// Download the file

			$request = new \GuzzleHttp\Client();

			$request->get(self::$urlToDownloadJsonGzipedFiles . $file, [
				'sink' => Storage::disk('products')->path($file)
			]);

			// Unzip the file

			$gzReader = gzopen(Storage::disk('products')->path($file), "rb");

			$resouce = fopen(Storage::disk('products')->path($jsonFileName), "w");

			while (!gzeof($gzReader)) { // Read the file in chunks of 10kb

				$string = gzread($gzReader, 1024 * 10);

				fwrite($resouce, $string, strlen($string));

			}

			gzclose($gzReader);

			fclose($resouce);

			// Store last import of the file

			ProductImportControl::updateOrCreate([
				"file_name" => $jsonFileName
			], [
				"imported_t" => now()->format('Y-m-d\TH:i:sP'),
			]);


			// Read the json file by line

			$products = [];

			$handle = fopen(Storage::disk('products')->path($jsonFileName), "r");

			$productsLimit = 100;

			$count = 0;

			while (($line = fgets($handle)) !== false && $count < $productsLimit) {

				$products[] = json_decode($line, true);

				$count++;

			}

			fclose($handle);

			// Delete the json file

			unlink(Storage::disk('products')->path($jsonFileName));

			// Delete the gzip file

			unlink(Storage::disk('products')->path($file));

			// Store or update the products in the database

			foreach ($products as $product) {

				Product::updateOrCreate([
					"code" => (int) $product["code"]
				], [
					"status" => 'published',
					"imported_t" => now()->format('Y-m-d\TH:i:sP'),
					"url" => $product["url"],
					"creator" => $product["creator"],
					"created_t" => $product["created_t"],
					"last_modified_t" => $product["last_modified_t"],
					"product_name" => $product["product_name"],
					"quantity" => $product["quantity"],
					"brands" => $product["brands"],
					"categories" => $product["categories"],
					"labels" => $product["labels"],
					"cities" => $product["cities"],
					"purchase_places" => $product["purchase_places"],
					"stores" => $product["stores"],
					"ingredients_text" => $product["ingredients_text"],
					"traces" => $product["traces"],
					"serving_size" => $product["serving_size"],
					"serving_quantity" => $product["serving_quantity"],
					"nutriscore_score" => $product["nutriscore_score"],
					"nutriscore_grade" => $product["nutriscore_grade"],
					"main_category" => $product["main_category"],
					"image_url" => $product["image_url"],
				]);
			}

		}

		return \response()->json([
			"message" => "Products updated"
		]);
	}

	/**
	 * Get the list of json gziped files.
	 * 
	 * @return array
	 */
	private function getJsonGzipedFiles(): array
	{

		try {

			$request = new \GuzzleHttp\Client();

			$response = $request->get(self::$urlWithTextListOfJsonGzipedFiles);

			$files = \explode("\n", $response->getBody()->getContents());

			return $files;
		} catch (\Exception $e) {

			return \response()->json([
				"error" => $e->getMessage()
			], 500);
		}
	}
}
