<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;

class HomeLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $component = 'landing';
        
        $request->merge([
            'router' => setting('landing.type') == 0 ? 'landing_page.index' : 'custom_landing_page.index'
        ]);

        if ($request->user()) {
            $component = 'index';

            $request->merge([
                'router' => 'home_page.index'
            ]);
        }
        
        return $this->renderData($request, null, [
            'component' => $component,
        ]);
    }
}
