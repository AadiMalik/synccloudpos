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
    public function shopLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'      => 'required|string',
            'password'      => 'required|string',
            // 'client_id'     => 'required',
            // 'secret_id'     => 'required',
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
                'Status'     => '1',
                'error_code' => 404,
                'message'    => 'Invalid username or password',
                'Response'   => null
            ], 404);
        }

        $user = Auth::user();
        // if ($user->business_id != $request->client_id) {
        //     return response()->json([
        //         'Status'     => '1',
        //         'error_code' => 404,
        //         'message'    => 'Client ID does not match',
        //         'Response'   => null
        //     ], 404);
        // }

        // if ($user->location_id != $request->secret_id) {
        //     return response()->json([
        //         'Status'     => '1',
        //         'error_code' => 404,
        //         'message'    => 'Secret ID does not match',
        //         'Response'   => null
        //     ], 404);
        // }
        $tokenResult = $user->createToken('Personal Access Token');

        // 🔹 Convert keys to snake_case if needed
        $data = [
            'id' => $user->id,
            'employee_number' => $user->id,
            'username' => $user->username ?? null,
            'password' => $user->password_decipt ?? null,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'client_id' => $user->business_id ?? 0,
            'store_id' => $user->location_id ?? 0,
            'phone' => $user->contact_no,
            'address' => $user->permanent_address,
            'city' => null,
            'user_type' => $user->tilType ?? null,
            'hire_date' => $user->hire_date ?? Carbon::now(),
            'end_date' => null,
        ];
        if (!$data) {
            return response()->json([
                'Status'     => '1',
                'error_code' => 404,
                'message'    => 'User not found',
                'Response'   => null
            ], 404);
        }
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
    // shop detail
    public function configrations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id'      => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $location = BusinessLocation::find($request->store_id);
        // 🔹 Convert keys to snake_case if needed
        $data = [
            'id' => $location->id,
            'store_code' => $location->location_id,
            'store_name' => $location->name,
            'shop_name' => $location->name,
            'store_logo' => 'placeholder_brandlogo.png',
            'pos_sync_time' => 1,
            'all_products_sync' => 1,
            'pos_discount_percentage' => 5,
            'invoice_terms' => 'Thank you for shoping. <br>Items..',
            'sale_return_code' => '',
            'hide_sales' => 0,
            'print_able_name' => $location->name,
            'pos_discount_type' => 'percentage',
            'allow_sale_if_qty_not_available' => 0,
        ];
        if (!$data) {
            return response()->json([
                'Status'     => '1',
                'error_code' => 404,
                'message'    => 'configration not found',
                'Response'   => null
            ], 404);
        }
        return response()->json([
            'Status'     => '0',
            'error_code' => null,
            'message'    => 'Success',
            'Response'   => [
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
        $validator = Validator::make($request->all(), [
            'shop_id'      => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }
        try {
            $users = User::where('location_id', $request->store_id)->get();

            $mappedData = $users->map(function ($user) {
                $userArray = $user->toArray();
                $newObject = [];

                // Convert camelCase keys to snake_case
                foreach ($userArray as $key => $value) {
                    $newObject[\Str::snake($key)] = $value;
                }

                // Add extra fixed keys
                $newObject['employee_number']   = $user->id;
                $newObject['password']          = $user->password_decipt ?? null;
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

    public function getShops()
    {

        $shops = BusinessLocation::where('business_id', auth()->user()->business_id)->get();
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
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer',
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|in:10,25,50,100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => '0',
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $locationId = $request->store_id;
        $page       = $request->page ?? 1;
        $perPage    = $request->per_page ?? 10;

        $location = BusinessLocation::findOrFail($locationId);

        $productIds = DB::table('products as p')
            ->join('product_locations as pl', function ($q) use ($locationId) {
                $q->on('pl.product_id', '=', 'p.id')
                    ->where('pl.location_id', $locationId);
            })
            ->where('p.type', '!=', 'modifier')
            ->where('p.is_inactive', 0)
            ->where('p.not_for_selling', 0)
            ->orderBy('p.id')
            ->paginate($perPage, ['p.id'], 'page', $page);

        $rows = DB::table('variations as v')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->leftJoin('variation_location_details as vld', function ($q) use ($locationId) {
                $q->on('vld.variation_id', '=', 'v.id')
                    ->where('vld.location_id', $locationId);
            })
            ->whereIn('p.id', $productIds->pluck('id'))
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                'p.enable_stock',
                'p.image',

                'v.id as variation_id',
                'v.name as variation_name',
                'v.variation_value_id',
                'v.sub_sku',
                'v.default_sell_price',
                'v.is_synced',

                DB::raw('IFNULL(vld.qty_available,0) as qty_available')
            )
            ->where('v.is_synced', 0)
            ->orderBy('p.id')
            ->get();

        $products = $rows->groupBy('product_id')->map(function ($items) use ($location) {

            $first = $items->first();

            return [

                // ========== STR / Shop ==========
                'FromShop' => $location->name,
                'ToShop'   => 0,
                'StrName'  => $location->name,
                'Skip'     => 0,
                'StrId'    => $location->id,

                // ========== Product ==========
                'ProductId' => (int) $first->product_id,
                'Name'      => $first->product_name,
                'FreePrice' => $first->enable_stock == 0,
                'ThumbPath' => $first->image ?? '',

                // ========== Stock ==========
                'KeepId'           => (int) $first->variation_id,
                'DispatchQuantity' => 0,
                'Quantity'         => (float) $items->sum('qty_available'),
                'UnitPrice'        => (float) $items->min('default_sell_price'),

                // ========== Codes ==========
                'BarCode'       => '',
                'AlternateCode' => '',

                // ========== STR ==========
                'StrShopRequestId' => 0,

                // ========== Unit ==========
                'Unit'     => '',
                'UnitCode' => 0,

                // ========== Attributes ==========
                'Attributes' => $items->map(function ($row) {
                    return [
                        'PosProductId'     => (int) $row->product_id,
                        'AttributeId'      => (int) $row->variation_id,
                        'Attribute'        => $row->variation_name ?? '',
                        'AttributeValueId' => $row->variation_value_id ?? 0,
                        'AttributeValue'   => $row->variation_name ?? '',
                    ];
                })->values(),

                // ========== Quantity Log ==========
                'QuantityLog' => [
                    'POSProductId' => (int) $first->product_id,
                    'KeepId'       => (int) $first->variation_id,
                    'StrProductId' => (int) $first->product_id,
                    'Quantity'     => (float) $items->sum('qty_available'),
                    'FromShopId'   => 0,
                    'ToShopId'     => 0,
                ],

                // ========== Sync ==========
                'isSync' => (bool) $first->is_synced
            ];
        })->values();

        return response()->json([
            'Status'     => '0',
            'error_code' => null,
            'message'    => 'Success',
            'Response'   => [
                'products' => $products,
                'pagination' => [
                    'current_page' => $productIds->currentPage(),
                    'per_page'     => $productIds->perPage(),
                    'total'        => $productIds->total(),
                    'last_page'    => $productIds->lastPage(),
                    'has_more'     => $productIds->hasMorePages(),
                ]
            ]
        ]);
    }

    public function getProductById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variation_id' => 'required|integer',
            'store_id'     => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => '0',
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $storeId = $request->store_id;

        // Get location
        $location = BusinessLocation::findOrFail($storeId);

        // Fetch the variation with product info
        $row = Variation::select(
            'p.id as product_id',
            'p.name as product_name',
            'p.enable_stock',
            'p.image',
            'variations.id as variation_id',
            'variations.name as variation_name',
            'variations.variation_value_id',
            'variations.sub_sku',
            'variations.default_sell_price',
            'variations.is_synced',
            DB::raw('IFNULL(VLD.qty_available,0) as qty_available')
        )
            ->join('products as p', 'variations.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as VLD', function ($join) use ($storeId) {
                $join->on('variations.id', '=', 'VLD.variation_id')
                    ->where('VLD.location_id', $storeId);
            })
            ->where('variations.id', $request->variation_id)
            ->first();

        if (!$row) {
            return response()->json([
                'Status'     => '1',
                'error_code' => 404,
                'message'    => 'Product not found',
                'Response'   => null
            ], 404);
        }

        // Build response object like getProducts
        $productObj = [
            'FromShop' => $location->name,
            'ToShop'   => 0,
            'StrName'  => $location->name,
            'Skip'     => 0,
            'StrId'    => $location->id,

            'ProductId' => (int) $row->product_id,
            'Name'      => $row->product_name,
            'FreePrice' => $row->enable_stock == 0,
            'ThumbPath' => $row->image ?? '',

            'KeepId'           => (int) $row->product_id,
            'DispatchQuantity' => 0,
            'Quantity'         => (float) $row->qty_available,
            'UnitPrice'        => (float) $row->default_sell_price,

            'BarCode'       => '',
            'AlternateCode' => '',

            'StrShopRequestId' => 0,

            'Unit'     => '',
            'UnitCode' => 0,

            'Attributes' => [[
                'PosProductId'     => (int) $row->product_id,
                'AttributeId'      => (int) $row->variation_id,
                'Attribute'        => $row->variation_name ?? '',
                'AttributeValueId' => $row->variation_value_id ?? 0,
                'AttributeValue'   => $row->variation_name ?? '',
            ]],

            'QuantityLog' => [
                'POSProductId' => (int) $row->product_id,
                'KeepId'       => 0,
                'StrProductId' => (int) $row->product_id,
                'Quantity'     => (float) $row->qty_available,
                'FromShopId'   => 0,
                'ToShopId'     => 0,
            ],

            'isSync' => (bool) $row->is_synced
        ];

        return response()->json([
            'Status'     => '0',
            'error_code' => null,
            'message'    => 'Success',
            'Response'   => [
                'products' => $productObj
            ]
        ], 200);
    }

    public function getProductInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id'        => 'required|integer',
            'variation_ids'   => 'nullable|array',
            'variation_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'Status'  => 0,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $storeId      = $request->store_id;
        $variationIds = $request->variation_ids ?? [];

        $query = Variation::select(
            'variations.id as variation_id',
            'variations.product_id',
            DB::raw('IFNULL(VLD.qty_available,0) as available_stock')
        )
            ->leftJoin('variation_location_details as VLD', function ($join) use ($storeId) {
                $join->on('variations.id', '=', 'VLD.variation_id')
                    ->where('VLD.location_id', $storeId);
            })
            ->where('variations.is_synced', 1);

        // ✅ If variation_ids array exists → filter
        if (!empty($variationIds)) {
            $query->whereIn('variations.id', $variationIds);
        }

        $data = $query->get();

        return response()->json([
            'Status'     => '1',
            'error_code' => null,
            'message'    => 'Success',
            'Response'   => [
                'data' => $data,
            ],
        ], 200);
    }

    public function syncedProducts(Request $request)
    {
        if (!is_array($request->keepings)) {
            return response()->json([
                'Status'  => '0',
                'message' => 'Sync status update error.',
                'errors'   => 'keepings is not an array.',
            ], 422);
        }
        try {
            $keepingIds = collect($request->keepings)->pluck('keeping_id')->toArray();

            // Update all records in bulk
            Variation::whereIn('id', $keepingIds)
                ->update(['is_synced' => 1]);
            return response()->json([
                'Status'  => '1',
                'error_code' => null,
                'message' => 'Sync status updated successfully',
                'Response' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'Status' => '0',
                'message' => 'Internal Server Error',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function syncOrdersApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|integer',
            'orders' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'Status'  => '0',
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();

        try {
            $location_id = $request->shop_id;
            $shop = BusinessLocation::where('id', $location_id)->first();
            $business_id = $shop->business_id;
            $syncedReceipts = [];
            foreach ($request->orders as $order) {
                $alreadyExists = DB::table('transactions')
                    ->where('invoice_no', $order['receipt_no'])
                    ->where('business_id', $business_id)
                    ->where('location_id', $location_id)
                    ->exists();

                if ($alreadyExists) {
                    // order skip
                    continue;
                }
                /** ===============================
                 *  1️⃣ TRANSACTIONS
                 * =============================== */

                $transaction_id = DB::table('transactions')->insertGetId([
                    'business_id'         => $business_id, // fixed / from auth
                    'location_id'         => $location_id,
                    'contact_id'          => 1,
                    'type'                => 'sell',
                    'status'              => ($order['is_draft'] == 2) ? 'final' : 'draft',
                    'payment_status'      => 'paid',
                    'invoice_no'          => $order['receipt_no'],
                    'transaction_date'    => Carbon::parse($order['created_at']),
                    'total_before_tax'    => $order['total'],
                    'discount_amount'     => $order['discount'] + ($order['local_discount'] ?? 0),
                    'final_total'         => $order['total'],
                    'additional_notes'    => $order['comments'] ?? null,
                    'commission_agent'    => $order['sales_person_id'] ?: null,
                    'created_by'          => $order['sales_person_id'],
                    'is_created_from_api' => 1,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                /** ===============================
                 *  2️⃣ SELL LINES
                 *  NOTE: prouducts (typo)
                 * =============================== */

                foreach ($order['prouducts'] as $item) {
                    $stockRow = DB::table('variation_location_details')
                        ->where('variation_id', $item['keeping_id'])
                        ->where('location_id', $location_id)
                        ->first();
                    if ($stockRow->qty_available === null || $stockRow->qty_available < $item['quantity']) {
                        DB::rollBack();
                        return response()->json([
                            'Status' => '0',
                            'message' => 'Insufficient stock',
                            'errors' => 'Insufficient stock for product ID: ' . $item['pos_product_id'] . ' and available stock is ' . $stockRow->qty_available,
                            'Response' => []
                        ], 400);
                    }
                    DB::table('transaction_sell_lines')->insert([
                        'transaction_id'             => $transaction_id,
                        'product_id'                 => $item['pos_product_id'], // IMPORTANT
                        'variation_id'               => $item['keeping_id'],
                        'quantity'                   => $item['quantity'],
                        'unit_price_before_discount' => $item['unit_price'],
                        'unit_price'                 => $item['unit_price'],
                        'line_discount_amount'       => $item['discount'] ?? 0,
                        'unit_price_inc_tax'         => $item['unit_price'],
                        'sell_line_note'             => null,
                        'created_at'                 => now(),
                        'updated_at'                 => now(),
                    ]);
                    DB::table('variation_location_details')
                        ->where('variation_id', $item['keeping_id'])
                        ->where('location_id', $location_id)
                        ->update([
                            'qty_available' => DB::raw('qty_available - ' . (float) $item['quantity'])
                        ]);
                }

                /** ===============================
                 *  3️⃣ PAYMENTS
                 * =============================== */

                // CASH
                if ($order['cash_amount'] > 0) {
                    DB::table('transaction_payments')->insert([
                        'transaction_id' => $transaction_id,
                        'business_id'    => $business_id,
                        'amount'         => $order['cash_amount'],
                        'method'         => 'cash',
                        'payment_type'   => 'sell',
                        'paid_on'        => Carbon::parse($order['created_at']),
                        'created_by'     => $order['sales_person_id'],
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }

                // CARD
                if ($order['card_amount'] > 0) {
                    DB::table('transaction_payments')->insert([
                        'transaction_id' => $transaction_id,
                        'business_id'    => 1,
                        'amount'         => $order['card_amount'],
                        'method'         => 'card',
                        'payment_type'   => 'sell',
                        'paid_on'        => Carbon::parse($order['created_at']),
                        'created_by'     => $order['sales_person_id'],
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
                $syncedReceipts[] = $order['receipt_no'];
            }

            DB::commit();

            return response()->json([
                'Status'  => '1',
                'error_code' => null,
                'message' => 'Orders synced successfully',
                'Response'   => [
                    'data' => $syncedReceipts,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'Status'  => '0',
                'message' =>  'Server Error',
                'errors'  => $e->getMessage(),
                'Response'   => []
            ], 500);
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
