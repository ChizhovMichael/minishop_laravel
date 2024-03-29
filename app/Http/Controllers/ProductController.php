<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use App\Navigation;
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\SortingController;
use Cart;
use App\Company;
use App\Tv;
use App\NavigationAdditional;
use App\Set;
use Illuminate\Support\Facades\Auth;
use App\Stock;



/*************
 * | Контролер продукта/ таблицы products
 * | fillable для проверки заполняемых полей. Не забывать редактировать если 
 * | название полей в таблице изменяется
 * | 
 * | 1. Main Page (вывод всех запросов на главную страницу)
 * | 2. Get Category для Main Page (вывод всей продукции по категориям)
 * | 3. Get Stock для Main Page (вывод всей продукции по стоковости)
 * | 4. Get All Product (вывод всей продукции с погинацией и get запросами-сортировкой)
 * | 5. Item Product (получить единичный продукт)
 * | 6. Get Categories Product (вывод всей продукции с погинацией и get запросами-сортировкой по категориям)
 * | 7. Get Search Product (Получаем все продукты соответствующие критерию поиска)
 * | 8. Get Tv Product (Получаем все продукты соответствующие данному телевизору)
 * | 9. Add Set To Cart (Добавление продукции в карзину);
 * | 10. Add Quantity to products (Изменяем количество товара)
 **************/



class ProductController extends Controller
{
    //
    /**
     * Function for main page. Get item
     *
     * @return \Illuminate\Http\Response
     */

    use NavigationController;
    use SortingController;


    /**********
     * | Main Page
     * | вывод всех запросов на главную страницу
     ***************/
    public function index()
    {

        return view('welcome', [
            'products_main'     =>  $this->getProduct([3]),
            'products_power'    =>  $this->getProduct([2]),
            'products_led'      =>  $this->getProduct([19, 20]),
            'products_new'      =>  $this->getStockProduct('new'),
            'products_discount' =>  $this->getStockProduct('discount'),
            'navigations'       =>  $this->navigation(),
            'contacts'          =>  collect($this->contacts()),
            'cart'              =>  $this->getCartCount(),
            'user'              =>  Auth::user(),
            'adminDetect'       =>  $this->adminDetect(),
            'statictext'        =>  collect($this->statictext()),
            'sliderimgheader'   =>  $this->sliderimgheader(),
            'sliderimgfooter'   =>  $this->sliderimgfooter(),
        ]);
    }


    /**********
     * | Get Category для Main Page
     * | вывод всей продукции по категориям
     ***************/
    private function getProduct($category)
    {

        $products = Product::whereIn('parttype_id', $category);
        $products = $products->selectAllInfo();
        $products = $products->selectAllTable();
        $products = $products->getImgMain();
        $products = $products->get();

        // 6. Убираем товар которого нет в наличии в конец 
        $products = $products->sortBy('part_status');
        $products = $products->paginate(10);

        return $products;
    }

    /**********
     * | Get Stock для Main Page
     * | вывод всей продукции по стоковости
     ***************/
    private function getStockProduct($stock)
    {
        $products = Product::selectAllInfo();
        $products = $products->selectAllTable();
        $products = $products->getImgMain();
        $products = $products->whereStock($stock);
        $products = $products->get();

        // 6. Убираем товар которого нет в наличии в конец 
        $products = $products->sortBy('part_status');
        $products = $products->paginate(10);

        return $products;
    }

    /**********
     * | Get All Product
     * | вывод всей продукции с погинацией и get запросами-сортировкой
     ***************/
    public function getAllProduct(Request $request)
    {

        // 0. Сортировка по названию, по цене
        $sorting = $this->getSort($request->sort);

        $products = Product::selectAllInfo();
        $products = $products->selectAllTable();
        $products = $products->getImgMain();
        $products = $products->orderBy($sorting['column'], $sorting['sort']);
        $products = $products->get();

        // 1. Получаем количество продукции, новой, акционной
        $productsCount = $products->count();
        $newCount = $products->where('stock', 'new')->count();
        $saleCount = $products->where('stock', 'discount')->count();

        // 2. Сортировка по компаниям
        $brands = $this->getBrands($request->brands);
        $products = $brands != NUll ? $products->whereIn('company', $brands) : $products;


        // 3. Сортировка по категориям
        $stock = $this->getStock($request->stock);
        $products = $stock != NULL ? $products->where('stock', $stock) : $products;

        // 4. Минимальная максимальная цена пока берет совместно с пагинацией
        $min = $products->min('part_cost');
        $max = $products->max('part_cost');


        // 5. Сортировка по цене
        $from = $this->getFrom($request->from, $min);
        $to = $this->getTo($request->to, $max);
        $products = $products->where('part_cost', '>=', $from);
        $products = $products->where('part_cost', '<=', $to);


        // 6. Убираем товар которого нет в наличии в конец 
        $products = $products->sortBy('part_status');

        // 7. Вывод c пагинацией в 12
        $products = $products->paginate(12);


        return view('page/shop', [
            'part_types'    => $products->appends([
                'sort'      => $request->sort,
                'stock'     => $request->stock,
                'brands'    => $request->brands,
                'from'      => $from,
                'to'        => $to,
            ]),
            'navigations'   => $this->navigation(),
            'contacts'      => collect($this->contacts()),
            'brand'         => $this->getAllBrand(NULL),
            'sort'          => $request->sort,
            'value'         => $sorting['value'],
            'sorting'       => $sorting['sorting'],
            'brands'        => $request->brands,
            'stock'         => $request->stock,
            'min'           => $min,
            'max'           => $max,
            'from'          => $from, // правильно
            'to'            => $to, // правильно
            'route'         => 'catalog', // правильно
            'part_types_id' => NULL, // правильно
            'cart'          => $this->getCartCount(),
            'search'        => NULL, // правильно
            'company'       => NULL,
            'model'         => NULL,
            'productsCount' => $productsCount,
            'newCount'      => $newCount,
            'saleCount'     => $saleCount,
            'user'          => Auth::user(),
            'adminDetect'   => $this->adminDetect(),
        ]);
    }




    /**********
     * | Get Categories Product
     * | вывод всей продукции с погинацией и get запросами-сортировкой по категориям
     ***************/
    public function getFullCategory($part_types_id, Request $request)
    {
        // 0. Сортировка по названию, по цене
        $sorting = $this->getSort($request->sort);

        $categories = [];
        $navigation = Navigation::getNavigationCategories($part_types_id)->get();

        foreach ($navigation as $item) {
            array_push($categories, $item->additional_id);
        }

        $products = Product::whereIn('parttype_id', $categories);
        $products = $products->selectAllInfo();
        $products = $products->selectAllTable();
        $products = $products->getImgMain();
        $products = $products->orderBy($sorting['column'], $sorting['sort']);
        $products = $products->get();

        // 1. Получаем количество продукции, новой, акционной
        $productsCount = $products->count();
        $newCount = $products->where('stock', 'new')->count();
        $saleCount = $products->where('stock', 'discount')->count();


        // 2. Сортировка по компаниям
        $brands = $this->getBrands($request->brands);
        $products = $brands != NUll ? $products->whereIn('company', $brands) : $products;


        // 3. Сортировка по категориям
        $stock = $this->getStock($request->stock);
        $products = $stock != NULL ? $products->where('stock', $stock) : $products;

        // 4. Минимальная максимальная цена пока берет совместно с пагинацией
        $min = $products->min('part_cost');
        $max = $products->max('part_cost');


        // 5. Сортировка по цене
        $from = $this->getFrom($request->from, $min);
        $to = $this->getTo($request->to, $max);
        $products = $products->where('part_cost', '>=', $from);
        $products = $products->where('part_cost', '<=', $to);


        // 6. Убираем товар которого нет в наличии в конец 
        $products = $products->sortBy('part_status');

        // 7. Вывод c пагинацией в 12
        $products = $products->paginate(12);


        return view('page/shop', [
            'part_types'    => $products->appends([
                'sort'      => $request->sort,
                'stock'     => $request->stock,
                'brands'    => $request->brands,
                'from'      => $from,
                'to'        => $to,
            ]),
            'navigations'   => $this->navigation(),
            'contacts'      => collect($this->contacts()),
            'brand'         => $this->getAllBrand($categories),
            'sort'          => $request->sort,
            'value'         => $sorting['value'],
            'sorting'       => $sorting['sorting'],
            'brands'        => $request->brands,
            'stock'         => $request->stock,
            'min'           => $min,
            'max'           => $max,
            'from'          => $from, // правильно
            'to'            => $to, // правильно
            'route'         => 'category.show', // правильно
            'part_types_id' => $part_types_id, // правильно
            'cart'          => $this->getCartCount(),
            'search'        => NULL, //правильно
            'company'       => NULL,
            'model'         => NULL,
            'productsCount' => $productsCount,
            'newCount'      => $newCount,
            'saleCount'     => $saleCount,
            'user'          => Auth::user(),
            'adminDetect'   => $this->adminDetect(),
        ]);
    }


    /**********
     * | Item Product
     * | Получить единичный продукт
     ***************/
    public function getItemProduct($slug)
    {
        $slugMain = explode('_', $slug);
        $slugMain = $slugMain[0];

        // 1. Получаем продукт по его слугу
        $products = Product::where('part_link', '=', $slug);

        // 2. Если данного продукта нет в наличии, то отображаем первый
        // из массива slugMain с таким же названием
        if ($products->first()->part_status != 0) {

            $productsExternal = Product::where('part_link', 'LIKE', '%' . $slugMain . '%');
            $productsExternal = $productsExternal->where('part_link', '!=', $slug);
            $productsExternal = $productsExternal->where('part_model', 'LIKE', '%' . $products->first()->part_model . '%');

            if ($productsExternal->doesntExist()) {

                // 3. Если такого продукта не существует
                // то идем ко всем продуктам
                $productsExternal = Product::where('part_link', 'LIKE', '%' . $slugMain . '%');
            }

            $products = $productsExternal;
        }



        $products = $products->where('part_status', '=', '0');
        $products = $products->selectAllInfoWithoutMainImg();
        $products = $products->selectAllTable();
        $products = $products->with('part_img');
        $products = $products->with('tv_img');
        $products = $products->with('matrix');
        $products = $products->first();



        if ($products !== NULL) {


            // 4. Если продукт существуем ищем его аналоги

            $productsAdditional = Product::where('part_link', 'LIKE', '%' . $slugMain . '%');
            $productsAdditional = $productsAdditional->selectAllInfoWithoutMainImg();
            $productsAdditional = $productsAdditional->selectAllTable();
            $productsAdditional = $productsAdditional->with('matrix');
            $productsAdditional = $productsAdditional->with('part_img_main');
            $productsAdditional = $productsAdditional->get();
            $productsAdditional = $productsAdditional->filter(function ($item) use ($products) {
                return $item->id != $products->id;
            });
            $productsAdditional = $productsAdditional->where('part_status', 0);
            $productsSame = $productsAdditional->where('part_status', 0);

            // 5. Ищем комплекты с данным продуктом
            $productsSet = Set::setProduct($products->id)
                ->get();

        } else {

            // 5. Если продукта не существует и нет аналогов
            // то выводим что его нет в налиии

            $productsEmpty = Product::where('part_link', '=', $slug);
            $productsEmpty = $productsEmpty->where('part_status', '=', '1');
            $productsEmpty = $productsEmpty->selectAllInfoWithoutMainImg();
            $productsEmpty = $productsEmpty->selectAllTable();
            $productsEmpty = $productsEmpty->with('part_img');
            $productsEmpty = $productsEmpty->with('tv_img');
            $productsEmpty = $productsEmpty->with('matrix');
            $productsEmpty = $productsEmpty->first();


            $products = $productsEmpty;
            $productsAdditional = collect([]);
            $productsSame = collect([]);
            $productsSet = collect([]);
        }

        $category = NavigationAdditional::where('additional_id', '=', $products->parttype_id)->get();
        

        return view('page/product', [
            'part_types'        => $products,
            'partsAdditional'   => $productsAdditional,
            'partsSame'         => $productsSame,
            'partsSet'          => $productsSet,
            'navigations'       => $this->navigation(),
            'contacts'          => collect($this->contacts()),
            'cart'              => $this->getCartCount(),
            'category'          => $category,
            'qty'               => 1,
            'user'              => Auth::user(),
            'adminDetect'       => $this->adminDetect(),
            'sliderimgfooter'   => $this->sliderimgfooter()
        ]);
    }


    /**********
     * | Add Product To Cart
     * | Добавление продукции в карзину
     ***************/
    public function addProductToCart($id, $qty)
    {
        
        $products = Product::where('products.id', '=', $id);
        $products = $products->selectAllInfo();
        $products = $products->selectAllTable();
        $products = $products->getImgMain();
        $products = $products->first();

        return $products;
        // Определяем вес запчасти
        if ($products->part_weight) {
            $part_weight = $products->part_weight;
        } else {
            switch ($products->parttype_type) {
                case 'Main Board':
                    $part_weight = 400;
                    break;
                case 'Inverter':
                    $part_weight = 400;
                    break;
                case 'Power Board':
                    $part_weight = 600;
                    break;                
                default:
                    $part_weight = 50;
                    break;
            }
        }
        

        $productsSale = Stock::where('product_id', $id)->first();
        if ($productsSale !== NULL) {
            if ($productsSale->stock == 'new') {
                $price = $products->part_cost;
            } else {
                $price = $productsSale->price;
            }            
        } else {
            $price = $products->part_cost;
        }

        $cart_elem = Cart::content()->where('id', $id)->first();

        if ($cart_elem !== NULL && $cart_elem->qty + $qty > $products->part_count) {
            
            return;
            
        } else {

            Cart::setGlobalTax(0);
            Cart::add([
                'id' => $id, 
                'name' => $products->part_model, 
                'qty' => $qty, 
                'price' => $price, 
                'weight' => $products->part_count,
                'options' => [
                    'type'      => $products->parttype_type,
                    'company'   => $products->company_id,
                    'tv'        => $products->tv_id,
                    'img'       => $products->part_img_name,
                    'part_link' => $products->part_link,
                    'part_weight' => $part_weight,
                ],
            ]);

            return response()->json([
                'count' => Cart::count(),
                'total' => Cart::total(),
                'content' => Cart::content(),
            ]);
        }
        
    }

    /**
     * | Get Search Product
     * | Получаем все продукты соответствующие критерию поиска
     */
    public function getSearchProduct($search, Request $request)
    {
        // 0. Сортировка по названию, по цене
        $sorting = $this->getSort($request->sort);


        $products = Product::where('part_model', 'LIKE', '%' . $search . "%");
        $products = $products->selectAllInfo();
        $products = $products->selectAllTable();
        $products = $products->getImgMain();
        $products = $products->orderBy($sorting['column'], $sorting['sort']);
        $products = $products->get();

        // 1. Получаем количество продукции, новой, акционной
        $productsCount = $products->count();
        $newCount = $products->where('stock', 'new')->count();
        $saleCount = $products->where('stock', 'discount')->count();

        // 2. Сортировка по компаниям
        $brands = $this->getBrands($request->brands);
        $products = $brands != NUll ? $products->whereIn('company', $brands) : $products;


        // 3. Сортировка по категориям
        $stock = $this->getStock($request->stock);
        $products = $stock != NULL ? $products->where('stock', $stock) : $products;

        // 4. Минимальная максимальная цена пока берет совместно с пагинацией
        $min = $products->min('part_cost');
        $max = $products->max('part_cost');


        // 5. Сортировка по цене
        $from = $this->getFrom($request->from, $min);
        $to = $this->getTo($request->to, $max);
        $products = $products->where('part_cost', '>=', $from);
        $products = $products->where('part_cost', '<=', $to);


        // 6. Убираем товар которого нет в наличии в конец 
        $products = $products->sortBy('part_status');

        // 7. Вывод c пагинацией в 12
        $products = $products->paginate(12);



        return view('page/shop', [
            'part_types'    => $products->appends([
                'sort'      => $request->sort,
                'stock'     => $request->stock,
                'brands'    => $request->brands,
                'from'      => $from,
                'to'        => $to,
            ]),
            'navigations'   => $this->navigation(),
            'contacts'      => collect($this->contacts()),
            'brand'         => $this->getAllBrand($search),
            'sort'          => $request->sort,
            'value'         => $sorting['value'],
            'sorting'       => $sorting['sorting'],
            'brands'        => $request->brands,
            'stock'         => $request->stock,
            'min'           => $min,
            'max'           => $max,
            'from'          => $from, // правильно
            'to'            => $to, // правильно
            'route'         => 'search.product', // правильно
            'part_types_id' => NULL, // правильно
            'cart'          => $this->getCartCount(),
            'search'        => $search, //правильно
            'company'       => NULL,
            'model'         => NULL,
            'productsCount' => $productsCount,
            'newCount'      => $newCount,
            'saleCount'     => $saleCount,
            'user'          => Auth::user(),
            'adminDetect'   => $this->adminDetect(),
        ]);
    }

    /**
     * | Get Tv Product
     * | Получаем все продукты соответствующие данному телевизору
     */
    public function getTvCategory($company, $model, Request $request)
    {
        // 0. Сортировка по названию, по цене
        $sorting = $this->getSort($request->sort);


        $company = Company::where('company', '=', $company)->first();
        $model = Tv::where('tv_model', '=', $model)->first();
        $company_id = $company->id;
        $model_id = $model->id;


        $products = Product::selectAllInfo();
        $products = $products->selectAllTable();
        $products = $products->getImgMain();
        $products = $products->where('company_id', '=', $company_id);
        $products = $products->where('tv_id', '=', $model_id);
        $products = $products->orderBy($sorting['column'], $sorting['sort']);
        $products = $products->get();

        // 1. Получаем количество продукции, новой, акционной
        $productsCount = $products->count();
        $newCount = $products->where('stock', 'new')->count();
        $saleCount = $products->where('stock', 'discount')->count();

        // 2. Сортировка по компаниям
        $brands = $this->getBrands($request->brands);
        $products = $brands != NUll ? $products->whereIn('company', $brands) : $products;


        // 3. Сортировка по категориям
        $stock = $this->getStock($request->stock);
        $products = $stock != NULL ? $products->where('stock', $stock) : $products;

        // 4. Минимальная максимальная цена пока берет совместно с пагинацией
        $min = $products->min('part_cost');
        $max = $products->max('part_cost');


        // 5. Сортировка по цене
        $from = $this->getFrom($request->from, $min);
        $to = $this->getTo($request->to, $max);
        $products = $products->where('part_cost', '>=', $from);
        $products = $products->where('part_cost', '<=', $to);


        // 6. Убираем товар которого нет в наличии в конец 
        $products = $products->sortBy('part_status');

        // 7. Вывод c пагинацией в 12
        $products = $products->paginate(12);


        return view('page/shop', [
            'part_types'    => $products->appends([
                'sort'      => $request->sort,
                'stock'     => $request->stock,
                'brands'    => $request->brands,
                'from'      => $from,
                'to'        => $to,
            ]),
            'navigations'   => $this->navigation(),
            'contacts'      => collect($this->contacts()),
            'brand'         => collect([$company]),
            'sort'          => $request->sort,
            'value'         => $sorting['value'],
            'sorting'       => $sorting['sorting'],
            'brands'        => $request->brands,
            'stock'         => $request->stock,
            'min'           => $min,
            'max'           => $max,
            'from'          => $from, // правильно
            'to'            => $to, // правильно
            'route'         => 'category.tv', // правильно
            'part_types_id' => NULL, // правильно
            'cart'          => $this->getCartCount(),
            'search'        => NULL, // правильно
            'company'       => $company->company,
            'model'         => $model->tv_model,
            'productsCount' => $productsCount,
            'newCount'      => $newCount,
            'saleCount'     => $saleCount,
            'user'          => Auth::user(),
            'adminDetect'   => $this->adminDetect(),
        ]);

    }


    /**
     * | Get Set Product
     * | Получаем комплект продукции, соответствующий данному комплекту
     */
    public function getItemSet( $slug ) 
    {
        $products = Set::where('set_slug', $slug);
        $products = $products->with('get_set_products');
        $products = $products->first();
        

        $productsInfo = Set::where('set_slug', $slug);
        $productsInfo = $productsInfo->getSetProducts();
        $productsInfo = $productsInfo->getProductsItem();
        $productsInfo = $productsInfo->get();    


        return view('page/set',[
            'navigations'   => $this->navigation(),
            'contacts'      => collect($this->contacts()),
            'set'           => $products,
            'setInfo'       => $productsInfo,
            'cart'          => $this->getCartCount(),
            'qty'           => 1,
            'user'          => Auth::user(),
            'adminDetect'   => $this->adminDetect(),
            'sliderimgfooter'   =>  $this->sliderimgfooter(),
        ]);

    }

    /**********
     * | Add Set To Cart
     * | Добавление продукции в карзину
     ***************/
    public function addSetToCart($id, $qty)
    {
        $products = Set::where('sets.id', '=', $id);
        $products = $products->first();

        Cart::setGlobalTax(0);
        Cart::add([
            'id' => $id,
            'name' => $products->set_name,
            'qty' => $qty,
            'price' => $products->set_cost,
            'weight' => 0, 
            'options' => [
                'img' => $products->set_img,
                'part_link' => $products->set_slug,
            ],
        ]);

        return response()->json([
            'count' => Cart::count(),
            'total' => Cart::total(),
            'content' => Cart::content(),
        ]);

    }


    /**
     * Add Quantity to products
     * Добавляем количество к товару
     */
    public function addQuantity(Request $request)
    {

        $link = route('addproduct', [ 'id' => $request->product, 'qty' => $request->qty ]);
        return $link;

    }

    /**
     * Add Set Quantity to products
     * Добавляем количество к товару
     */
    public function addSetQuantity(Request $request)
    {

        $link = route('addset', [ 'id' => $request->product, 'qty' => $request->qty ]);
        return $link;

    }

}
