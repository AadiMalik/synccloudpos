<?php

namespace Modules\AiAssistance\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'aiassistance_module',
                'label' => __('aiassistance::lang.aiassistance_module'),
                'default' => false,
            ],
            [
                'name' => 'aiassistance_max_token',
                'label' => __('aiassistance::lang.aiassistance_max_token'),
                'default' => false,
                'field_type' => 'number',
                'tooltip' => __('aiassistance::lang.max_token_tooltip')
            ],
        ];
    }

    /**
     * Adds menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();

        $is_aiassistance_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'aiassistance_module');

        $commonUtil = new Util();
        $is_admin = $commonUtil->is_admin(auth()->user(), $business_id);

        if (auth()->user()->can('aiassistance.access_aiassistance_module') && $is_aiassistance_enabled) {
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu) {
                    $menu->url(action([\Modules\AiAssistance\Http\Controllers\AiAssistanceController::class, 'index']), __('aiassistance::lang.aiassistance'), ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2z"></path>
                    <path d="M9 16c1 .667 2 1 3 1s2 -.333 3 -1"></path>
                    <path d="M9 7l-1 -4"></path>
                    <path d="M15 7l1 -4"></path>
                    <path d="M9 12v-1"></path>
                    <path d="M15 12v-1"></path>
                  </svg>', 'style' => config('app.env') == 'demo' ? 'background-color: #6EA194;' : '', 'active' => request()->segment(1) == 'aiassistance'])->order(50);
                }
            );
        }
    }

    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'aiassistance.access_aiassistance_module',
                'label' => __('aiassistance::lang.access_aiassistance_module'),
                'default' => false,
            ]      
        ];
    }
}
