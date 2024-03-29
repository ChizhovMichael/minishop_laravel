<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\NavigationController;
use App\Product;
use App\Navigation;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;



/*************
 * | Контролер поиска/ таблицы products, navigation,
 * | 
 * | 1. Search (асинхронный поиск по средством get запросов и обработчика searchArticle() в app.js)
 * | 2. Get Mobile Page (Получаем страницу поиска на мобильных устройствах)
 * | 
 * |
 **************/

class SearchController extends Controller
{
    //

    use NavigationController;


    /**********
     * | Search
     * | асинхронный поиск по средством get запросов и обработчика searchArticle() в app.js
     ***************/
    public function search(Request $request)
    {

        if ($request->ajax()) {
            $output = "";


            if ($request->category !== 'all') {

                $additional_id = [];

                $navigation = Navigation::join('navigation_additionals', 'navigations.id', '=', 'navigation_additionals.navigation_id')
                    ->selectRaw('count(*) AS cnt, additional_id')->groupBy('additional_id')
                    ->where('navigations.slug', '=', $request->category)
                    ->orWhere('navigation_additionals.additional_slug', '=', $request->category)
                    ->orderBy('cnt', 'DESC')->get();

                foreach ($navigation as $item) {
                    array_push($additional_id, $item->additional_id);
                }

                $part_types = Product::whereIn('parttype_id', $additional_id);

                $part_types = $part_types->groupBy('part_model')
                    ->join('companies', 'products.company_id', '=', 'companies.id')
                    ->join('tvs', 'products.tv_id', '=', 'tvs.id')
                    ->join('part_types', 'part_types.id', '=', 'products.parttype_id')
                    ->join('part_imgs', function ($join) {
                        $join->on('part_imgs.product_id', '=', 'products.id')
                            ->where('part_img_main', '=', '1');
                    })
                    ->where('part_model', 'LIKE', '%' . $request->search . "%");

            } else {

                $part_types = Product::groupBy('part_model')
                    ->join('companies', 'products.company_id', '=', 'companies.id')
                    ->join('tvs', 'products.tv_id', '=', 'tvs.id')
                    ->join('part_types', 'products.parttype_id', '=', 'part_types.id')
                    ->join('part_imgs', function ($join) {
                        $join->on('part_imgs.product_id', '=', 'products.id')
                            ->where('part_img_main', '=', '1');
                    })
                    ->where('part_model', 'LIKE', '%' . $request->search . "%");

            }

            $part_types = $part_types->paginate(15);

            if ($request->search == '')
                return $output;


            if ($part_types) {
                foreach ($part_types as $part) {
                    $output .= '<div class="articles back-body bb-light sd-12 col-12 bb pt-em-1 pb-em-1 pr-em-2 pl-em-2">' .
                        '<div class="title flex-center-between">'.
                        '<a class="hover-main sd-8 col-8 m-0 ct" href="/product/' . $part->part_link . '">' . $part->parttype_type . ' ' . $part->part_model . '</a>' .
                        '<p class="sd-4 col-4 text-right cm-bold m-0">' . $part->part_cost . '</p>' .
                        '</div>' .
                        '<div class="sd-12 col-12">' .
                        '<p class="cc mt-0 mb-0">' . $part->company . ' ' . $part->tv_model . '</p>' .
                        '</div>' .
                        '</div>';
                }

                if ($part_types->count() > 0) {
                    $output .= '<div class="articles text-center back-body bb-light sd-12 col-12 bb pt-em-1 pb-em-1 pr-em-2 pl-em-2">';
                    $output .= '<a href="'. route('search.product', [ 'search' => $request->search ]) .'" class="hover cm">Посмотреть все</a>';
                    $output .= '</div>';
                }
                
                return $output;
            }
        }
    }

    /**********
     * | Get Mobile Page
     * | Получаем страницу поиска на мобильных устройствах
     ***************/
    public function getMobilePage()
    {
        $agent = new Agent();

        if ( $agent->isDesktop() ) {

            return redirect('/');

        } else {

            return view('page/search', [
                'navigations'       =>  $this->navigation(),
                'contacts'          => collect($this->contacts()),
                'cart'              =>  $this->getCartCount(),
                'user'              =>  Auth::user(),
                'adminDetect'       => $this->adminDetect(),
            ]);

        }
        
    }
}
