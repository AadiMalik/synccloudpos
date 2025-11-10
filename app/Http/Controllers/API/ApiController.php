<?php

namespace App\Http\Controllers\API;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Http\Controllers\Controller;
use App\Product;
use App\Transaction;
use App\TransactionSellLine;
use App\User;
use App\Variation;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\BusinessUtil;

class ApiController extends Controller
{

    protected $moduleUtil;
    protected $productUtil;
    protected $transactionUtil;
    protected $notificationUtil;
    protected $businessUtil;
    public function __construct(
        ModuleUtil $moduleUtil,
        ProductUtil $productUtil,
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil,
        BusinessUtil $businessUtil
    ) {
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
        $this->businessUtil = $businessUtil;
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid username or password',
            ], 401);
        }

        $user = Auth::user();
        $tokenResult = $user->createToken('Personal Access Token');

        // 🔹 Convert keys to snake_case if needed
        $data = $user->toArray();

        // 🔹 Add extra fields as in original JS code
        $data['store_id']        = $user->location_id ?? 0;
        $data['employee_number'] = $user->id;
        $data['user_type']       = $user->tilType ?? null; // optional field
        $data['name']            = $user->username ?? $user->name;

        return response()->json([
            'Status'     => '0',
            'error_code' => null,
            'message'    => 'Success',
            'Response'   => [
                'access_token' => [
                    'access_token' => $tokenResult->accessToken,
                ],
                'data' => $data,
            ],
        ], 200);
    }

    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Auth::guard('web')->attempt($request->only('username', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Delete old tokens (optional)
        $user->tokens()->delete();

        // Create new access token (1 week expiry)
        $tokenResult = $user->createToken('Access Token');
        $token = $tokenResult->token;
        $token->expires_at = now()->addWeek();
        $token->save();

        return response()->json([
            'Status' => '0',
            'error_code' => null,
            'message' => 'Success',
            'Response' => [
                'access_token' => $tokenResult->accessToken,
                'refresh_token' => '1'
            ]
        ]);
    }

    public function tokenData(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $tokenResult = $user->createToken('Short Access Token');
        $tokenResult->token->expires_at = now()->addHour();
        $tokenResult->token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'expires_at' => $tokenResult->token->expires_at
        ]);
    }

    public function showData($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return response()->json(['user' => $user], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    public function getUsers(Request $request)
    {
        try {
            $users = User::all();

            $mappedData = $users->map(function ($user) {
                $userArray = $user->toArray();
                $newObject = [];

                // Convert camelCase keys to snake_case
                foreach ($userArray as $key => $value) {
                    $newObject[\Str::snake($key)] = $value;
                }

                // Add extra fixed keys
                $newObject['employee_number']   = $user->id;
                $newObject['shop_id']           = $user->business_id;
                $newObject['user_type']         = 0;
                $newObject['till_type']         = 0;
                $newObject['group_id']          = 0;
                $newObject['discount_group_id'] = 0;
                $newObject['active']            = 1;
                $newObject['name']              = $user->username ?? $user->name;
                $newObject['is_deleted']        = false;

                return $newObject;
            });

            return response()->json([
                'Status'     => '0',
                'error_code' => null,
                'message'    => 'Success',
                'Response'   => [
                    'users' => $mappedData
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status'     => '1',
                'error_code' => 500,
                'message'    => 'Internal Server Error',
                'Response'   => null,
            ], 500);
        }
    }

    public function getShops(Request $request)
    {

        $shops = BusinessLocation::where('business_id', $request->get('business_id'))->get();
        return response()->json([
            'Status'     => '0',
            'error_code' => null,
            'message'    => 'Success',
            'Response'   => [
                'shops' => $shops
            ]
        ]);
    }

    public function getProducts(Request $request)
    {
        $business_id = $request->get('business_id');
        $location_id = $request->get('location_id');
        $products = Variation::select(
            'p.id as product_id',
            'p.name',
            'p.type',
            'p.enable_stock',
            'p.image as product_image',
            'variations.id',
            'variations.name as variation',
            'VLD.qty_available',
            'variations.default_sell_price as selling_price',
            'variations.sub_sku'
        )
        ->join('products as p', 'variations.product_id', '=', 'p.id')
            ->join('product_locations as pl', 'pl.product_id', '=', 'p.id')
            ->leftjoin(
                'variation_location_details AS VLD',
                function ($join) use ($location_id) {
                    $join->on('variations.id', '=', 'VLD.variation_id');

                    //Include Location
                    if (!empty($location_id)) {
                        $join->where(function ($query) use ($location_id) {
                            $query->where('VLD.location_id', '=', $location_id);
                            //Check null to show products even if no quantity is available in a location.
                            //TODO: Maybe add a settings to show product not available at a location or not.
                            $query->orWhereNull('VLD.location_id');
                        });
                    }
                }
            )
            ->where('p.business_id', $business_id)
            ->where('p.type', '!=', 'modifier')
            ->where('p.is_inactive', 0)
            ->where('p.not_for_selling', 0)
            ->where('VLD.qty_available', '>', 0)
            ->where('p.is_synced', 0)
            ->where(function ($q) use ($location_id) {
                $q->where('pl.location_id', $location_id);
            })
            ->orderBy('p.name', 'asc')
            ->get();

        return response()->json([
            'Status'     => '0',
            'error_code' => null,
            'message'    => 'Success',
            'Response'   => [
                'products' => $products
            ]
        ]);
    }

    public function syncedProducts(Request $request)
    {
        if (!is_array($request->keepings)) {
            return response()->json(['message' => 'Sync status update error.'], 422);
        }
        try {
            $keepingIds = collect($request->keepings)->pluck('keeping_id')->toArray();

            // Update all records in bulk
            Product::whereIn('product_id', $keepingIds)
                ->update(['is_synced' => 1]);
            return response()->json(['message' => 'Sync status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function syncOrdersApi(Request $request)
    {
        try {
            $api_token = $request->header('API-TOKEN');
            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            if (empty($api_settings)) {
                return response()->json([
                    'Status' => '1',
                    'error_code' => null,
                    'message' => 'Invalid API Token',
                    'Response' => ['results' => []],
                ]);
            }

            $business_id = $api_settings->business_id;
            $location_id = $api_settings->location_id;

            $orders = $request->input('orders', []);
            if (empty($orders)) {
                return response()->json([
                    'Status' => '1',
                    'error_code' => null,
                    'message' => 'No orders received',
                    'Response' => ['results' => []],
                ]);
            }

            $synced_ids = [];

            DB::beginTransaction();

            foreach ($orders as $torder) {
                $products = $torder['prouducts'] ?? []; // note typo from Node
                unset($torder['prouducts']);

                // ✅ Convert snake_case → camelCase if needed
                $orderData = [];
                foreach ($torder as $key => $value) {
                    $orderData[\Illuminate\Support\Str::camel($key)] = $value;
                }

                // ✅ Required columns that NodeJS inserted manually
                $orderData['business_id'] = $business_id;
                $orderData['location_id'] = $location_id;
                $orderData['is_draft'] = $orderData['isDraft'] ?? false;
                $orderData['final_total'] = $orderData['totalAmount'] ?? 0;
                $orderData['status'] = $orderData['is_draft'] ? 'draft' : 'final';
                $orderData['created_by'] = $api_settings->user_id ?? 1; // 🔸 Hardcoded fallback
                $orderData['transaction_date'] = now();

                // 🔸 Need table/columns to store external order ID (from POS or App)
                // Suggest adding: external_order_id VARCHAR(100)
                $orderData['external_order_id'] = $torder['id'] ?? null;

                // 🔸 Create Order transaction
                $transaction = Transaction::create($orderData);
                $synced_ids[] = ['id' => $transaction->id];

                // 🔸 Insert related products
                foreach ($products as $product) {
                    $productData = [];
                    foreach ($product as $key => $value) {
                        $productData[\Illuminate\Support\Str::camel($key)] = $value;
                    }

                    $productData['transaction_id'] = $transaction->id;
                    $productData['business_id'] = $business_id;
                    $productData['location_id'] = $location_id;
                    $productData['subtotal'] = ($productData['unitPrice'] ?? 0) * ($productData['quantity'] ?? 0);

                    TransactionSellLine::create($productData);
                }
            }

            DB::commit();

            return response()->json([
                'Status' => '0',
                'error_code' => null,
                'message' => 'Success',
                'Response' => ['results' => $synced_ids],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Sync Order API Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());

            return response()->json([
                'Status' => '1',
                'error_code' => null,
                'message' => 'Failed!',
                'Response' => ['results' => [$e->getMessage()]],
            ]);
        }
    }

    public function getCustomer(Request $request)
    {
        try {
            // Validate input
            if (empty($request->customer_no)) {
                return response()->json([
                    'Status' => '1',
                    'error_code' => null,
                    'message' => 'Invalid params.',
                    'Response' => ['results' => []],
                ], 422);
            }

            // 🔸 Fetch customer by customer_no
            $customer = Contact::where('contact_id', $request->customer_no)
                ->whereIn('type', ['customer', 'both'])
                ->first();

            if (!$customer) {
                return response()->json([
                    'Status' => '1',
                    'error_code' => null,
                    'message' => 'Customer not found',
                    'Response' => ['results' => []],
                ], 404);
            }

            return response()->json([
                'Status' => '0',
                'error_code' => null,
                'message' => 'Customer retrieved successfully',
                'Response' => [
                    'results' => $customer,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Get Customer API Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
            return response()->json([
                'Status' => '1',
                'error_code' => null,
                'message' => 'Internal Server Error',
                'Response' => ['results' => []],
            ], 500);
        }
    }
    public function getCustomDiscounts()
    {
        try {

            $data = [
                [
                    'id' => 1,
                    'category_id' => 1,
                    'title' => 'Discount 1',
                    'description' => 'Description for Discount 1',
                    'discount_type' => 0,
                    'discount_value' => 10.0,
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                    'is_deleted' => false,
                ],
                [
                    'id' => 2,
                    'category_id' => 2,
                    'title' => 'Discount 2',
                    'description' => 'Description for Discount 2',
                    'discount_type' => 1,
                    'discount_value' => 5.0,
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                    'is_deleted' => false,
                ],
            ];

            return response()->json([
                'Status' => '0',
                'error_code' => null,
                'message' => 'Success',
                'Response' => [
                    'results' => $data,
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Get Custom Discounts API Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
            return response()->json([
                'Status' => '1',
                'error_code' => null,
                'message' => 'Internal Server Error',
                'Response' => ['results' => []],
            ], 500);
        }
    }
}
