<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\NavigationController;
use App\Product;
use App\Stock;
use App\Navigation;
use App\NavigationAdditional;
use App\Orderlist;
use App\PartType;
use Carbon\Carbon;
use App\OrderPart;
use App\Sale;
use App\PriceRequest;
use App\BoxPart;
use App\Box;
use Illuminate\Support\Facades\Mail;
use App\Mail\CheckedEmail;
use App\Mail\DeleteOrderEmail;
use App\Skypka;
use App\Company;
use App\StaticText;
use App\SliderImg;
use Illuminate\Support\Facades\Storage;
use App\Tv;
use App\Razbor;
use Illuminate\Support\Facades\DB;
use App\Matrix;
use App\PartImg;
use App\TvImg;

/***************
 * Административный раздел
 * 1. admin (Показывать страницу админстратора только для администратора)
 * 2. productEditContent (Редактирование продуктов для контент менеджера)
 * 3. productUpdateContent (Обновление продуктов для контент менеджера)
 * 4. navigationEdit (Редактирование навигации в административной панеле)
 * 5. navigationEditDeleteSection (Удаление раздела навигации)
 * 6. navigationEditDeleteSubsection (Удаление подраздела навигации)
 * 7. navigationEditSaveSection (Обновление раздела навигации)
 * 8. navigationEditSaveSubsection (Обновление подраздела навигации)
 * 9. navigationEditAddSection (Добавление раздела навигации)
 * 10. navigationEditAddSubsection (Добавление подраздела навигации)
 * 11. contactEdit (Редактирование контактов в административной панеле)
 * 12. contactEditAdd (Добавление контактов в административной панеле)
 * 13. contactEditUpdate (Обновление контактов в административной панеле)
 * 14. contactEditDelete (Удаление контактов в административной панеле)
 * 15. orderEdit (Редактирование заказов в административной панеле)
 * 16. orderEditAll (Редактирование всех заказов в административной панеле)
 * 17. orderEditDetail (Редактирование отдельного заказов в административной панеле)
 * orderEditDetailDeletePart (Удаление товара из таблицы заказа)
 * orderEditChecked (Подтверждение оплаты товара)
 * Tracking Order (Перевод заявки в статус отправлено и присвоение трекинг номера)
 * Delete Payment Order (Удаление заявки)
 * 
 * 18. salesEdit (Получаем сумму продаж)
 * 19. getofferEdit (Получаем список запрашиваемых цен для предложения Нашли дешевле?)
 * 20. getofferEditChecked (Отмечаем о обработке поля Нашли дешевле)
 * 21. boxEdit (Получаем список всех коробок и их содержимое)
 * 22. boxEditUnsort (Получаем список неотсортированных деталей)
 * 23. boxEditUnsortAdd (Отправляем запчасть в выбранную коробку)
 * 24. boxEditControl (Управляем списком коробок)
 * 25. boxEditControlDetail (Получаем детальное описание коробки)
 * 26. boxEditControlDetailSave (Сохраняем детальное описание коробки)
 * 27. boxEditControlDetailCreate ( Сохраняем детальное описание коробки)
 * 30. buyupEdit (вывод списка скупаемых товаров)
 * 31. repairEdit (Вывод списка реанимирующихся товаров)
 * 32. listEdit (Вывод списка)
 * 33. listEditTxt (Вывод списка)
 * 34. listEditAvito (Вывод списка)
 * 35. listEditAvitoModels (Вывод списка)
 * 36. listEditMonitor(Вывод списка)
 * 
 * 
 * statictextEdit(Редактирование статический текст получаем)
 * statictextEditUpdate (Редактирование статический текст обновляем)
 * statictextEditDelivery (Редактирование статический текст получаем)
 * statictextEditContacts (Редактирование статический текст получаем)
 * sliderEdit (Редактирование слайдера на главной странице)
 * sliderEditUpload (Редактирование слайдера на главной странице)
 * sliderEditDelete (Редактирование слайдера на главной странице)
 * companyEdit (Редактирование раздела TV в админ панеле)
 * companyEditTvs (Получаем список всех телевизоров)
 * companyEditTvsAdd (Добавляем новый телевизов)
 * companyEditTvsSingle (Получаем детальную информацию по телевизору)
 * companyEditTvsUpdate (Обновление информации по телевизору)
 * companyEditProduct (Получаем информацию по отдельному продукту)
 * companyEditProductAdd (Добавление нового продукта)
 * companyEditImageUpload (Загружаем иизображение для телевизора)
 * companyEditImageDelete (Удаляем иизображение для телевизора)
 * companyEditProductUpdate (Обновляем информацию о продукте)
 * companyEditImageMainUpdate (Обновляем главное изображение продукта)
 ***********/

class AdminController extends Controller
{
    //
    use NavigationController;

    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * admin
     * Показывать страницу админстратора только для администратора
     */
    public function admin()
    {
        return view('admin', [
            'page' => 'main'
        ]);
    }


    /**
     * productEditContent
     * Редактирование продуктов для контент менеджера
     */
    public function productEditContent(Request $request)
    {
        $id = $request->id;

        $product = Product::where('products.id', $id);
        $product = $product->selectStockInfo();
        $product = $product->selectStockTable();
        $product = $product->first();


        return view('includes.editproductform', [
            'product' => $product
        ]);
    }

    /**
     * productUpdateContent
     * Обновление продуктов для контент менеджера
     */
    public function productUpdateContent(Request $request)
    {
        $request->validate([
            'part_model' => 'required',
            'part_link' => 'required',
            'part_cost' => 'required',
            'part_count' => 'required',
        ]);


        $part_status = $request->part_status == "on" ? 1 : 0;
        $part_return = $request->part_return == "on" ? 1 : 0;


        $percent = (1 - intval($request->price) / intval($request->part_cost)) * 100;

        Product::where('id', $request->id)
            ->update([
                'part_model' => $request->part_model,
                'part_comment' => $request->part_comment,
                'part_comment_for_client' => $request->part_comment_for_client,
                'part_link' => $request->part_link,
                'part_cost' => $request->part_cost,
                'part_count' => $request->part_count,
                'part_status' => $part_status,
                'part_return' => $part_return
            ]);

        if ($request->marker == 'new') {
            Stock::updateOrCreate([
                'product_id' => $request->id
            ], [
                'stock' => 'new',
                'percent' => round($percent),
                'price' => $request->price
            ]);
        }

        if ($request->marker == 'discount') {
            Stock::updateOrCreate([
                'product_id' => $request->id
            ], [
                'stock' => 'discount',
                'percent' => round($percent),
                'price' => $request->price
            ]);
        }

        if ($request->marker == 'without') {
            Stock::where('product_id', $request->id)->delete();
        }



        return redirect()->back()->with('success', 'Ок, товар обновлен')
            ->with('message', 'Главное помни: ты красавчик!');
    }


    /**
     * navigationEdit
     * Редактирование навигации в административной панеле
     */
    public function navigationEdit()
    {
        $parttype = PartType::get();

        return view('admin', [
            'page'           => 'navigation',
            'navigations'    =>  $this->navigation(),
            'parttype'       =>  $parttype,
        ]);
    }

    /**
     * navigationEditDeleteSection
     * Удаление раздела навигации
     */
    public function navigationEditDeleteSection(Request $request)
    {
        NavigationAdditional::where('additional_id', $request->id)->delete();
        Navigation::where('id', $request->id)->delete();
        return redirect()->back();
    }

    /**
     * navigationEditDeleteSubsection
     * Удаление подраздела навигации
     */
    public function navigationEditDeleteSubsection(Request $request)
    {

        NavigationAdditional::where('id', $request->id)->delete();
        return redirect()->back();
    }

    /**
     * navigationEditDeleteSection
     * Обновлние раздела навигации
     */
    public function navigationEditSaveSection(Request $request)
    {

        $navigations = Navigation::find($request->id);

        $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|alpha_dash|unique:navigations,slug,' . $navigations->id
        ]);


        $navigations->name = $request->name;
        $navigations->slug = $request->slug;
        $navigations->show = $request->show;

        $navigations->save();

        return redirect()->back();
    }

    /**
     * navigationEditSaveSubsection
     * Обновление подраздела навигации
     */
    public function navigationEditSaveSubsection(Request $request)
    {

        $navigations = NavigationAdditional::find($request->id);

        $request->validate([
            'additional_name' => 'required|max:255',
            'additional_id' => 'required|alpha_dash|unique:navigation_additionals,additional_id,' . $navigations->id
        ]);        

        $parttype = PartType::find($request->additional_id);
        $parttype_link = $parttype->parttype_link;

        // $navigations->navigation_id = $request->navigation_id;
        $navigations->additional_id = $request->additional_id;
        $navigations->additional_name = $request->additional_name;
        $navigations->additional_slug = $parttype_link;
        $navigations->show = $request->show;

        $navigations->save();

        return redirect()->back();
    }

    /**
     * navigationEditAddSection
     * Добавление раздела навигации
     */
    public function navigationEditAddSection(Request $request)
    {

        $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|alpha_dash|unique:navigations'
        ]);

        $navigations = new Navigation();
        $navigations->name = $request->name;
        $navigations->slug = $request->slug;
        $navigations->show = $request->show;
        $navigations->save();

        return redirect()->back();
    }
    /**
     * navigationEditAddSubsection
     * Добавление подраздела навигации
     */
    public function navigationEditAddSubsection(Request $request)
    {
        $request->validate([
            'additionalname' => 'required|max:255',
            'additional_id' => 'required|alpha_dash|unique:navigation_additionals'
        ]);

        $parttype = PartType::find($request->additional_id);
        $parttype_link = $parttype->parttype_link;


        $navigations_additional = new NavigationAdditional();
        $navigations_additional->navigation_id = $request->navigation_id;
        $navigations_additional->additional_id = $parttype->id;
        $navigations_additional->additional_name = $request->additionalname;
        $navigations_additional->additional_slug = $parttype_link;
        $navigations_additional->show = $request->show;
        $navigations_additional->save();

        return redirect()->back();
    }


    /**
     * contactEdit
     * Редактирование контактов в административной панеле
     */
    public function contactEdit()
    {
        $contacts = Contact::get();

        return view('admin', [
            'page'           => 'contact',
            'contact'        => $contacts
        ]);
    }

    /**
     * contactEditAdd
     * Добавление контактов в административной панеле
     */
    public function contactEditAdd(Request $request)
    {

        $name = $request->name;

        $contacts = new Contact();
        $contacts->name =  $name;
        $contacts->value = $request->$name;

        if ($request->status == 'on') {
            $status = 1;
        } else {
            $status = 0;
        }

        $contacts->status = $status;
        $contacts->save();

        return redirect()->back();
    }


    /**
     * contactEditUpdate
     * Обновление контактов в административной панеле
     */
    public function contactEditUpdate(Request $request)
    {

        $name = $request->name;

        Contact::where('id', $request->id)->update([
            'name'      => $name,
            'value'     => $request->$name,
        ]);

        return redirect()->back();
    }


    /**
     * contactEditDelete
     * Удаление контактов в административной панеле
     */
    public function contactEditDelete(Request $request)
    {
        Contact::find($request->id)->delete();
        return redirect()->back();
    }

    /**
     * orderEdit
     * Редактирование заказов в административной панеле
     */
    public function orderEdit()
    {

        $orderlist = Orderlist::latest('id');
        $orderlist = $orderlist->selectInfoListItem();
        $orderlist = $orderlist->with('get_part.get_product.get_box');
        $orderlist = $orderlist->paginate(50);

        return view('admin', [
            'page'           => 'order',
            'orderlist'      => $orderlist,
        ]);
    }

    /**
     * orderEditAll
     * Редактирование всех заказов в административной панеле
     */
    public function orderEditAll()
    {
        $orderlist = Orderlist::latest('id');
        $orderlist = $orderlist->selectInfoListItem();
        $orderlist = $orderlist->with('get_part.get_product.get_box');
        $orderlist = $orderlist->get();


        return view('admin', [
            'page'           => 'order',
            'orderlist'      => $orderlist,
        ]);
    }

    /**
     * orderEditDetail
     * Редактирование отдельного заказов в административной панеле
     */
    public function orderEditDetail($id)
    {
        $orderlist = Orderlist::find($id);

        $orderparts = OrderPart::where('order_id', $orderlist->id);
        $orderparts = $orderparts->selectPartInfo();
        $orderparts = $orderparts->getProductInfo();
        $orderparts = $orderparts->getBox();
        $orderparts = $orderparts->getImgMain();
        $orderparts = $orderparts->get();

        $sum = 0;
        foreach ($orderparts as $item) {
            $sum += $item->order_count * $item->part_cost;
        }



        return view('admin', [
            'page'           => 'orderdetail',
            'order'          => $orderlist,
            'products'       => $orderparts,
            'sum'            => $sum
        ]);
    }


    /**
     * Delete Part from Order
     * Удаление товара из таблицы заказа
     */
    public function orderEditDetailDeletePart(Request $request)
    {

        $order_status = $request->order_status;
        $part_id = $request->part_id;
        $order_id = $request->order_id;
        $order_timestamp = $request->order_timestamp;

        

        if ( $order_status == 1 or $order_status == 2 or $order_status == 3 )
        {

            $orderlist = Orderlist::find($order_id);            
            $orderpart = OrderPart::where([
                ['part_id', '=', $part_id],
                ['order_id', '=', $order_id]                
            ])->first();
            $product = Product::find($part_id);
            $sales = Sale::where([
                ['sales_year', date("Y", $order_timestamp)],
                ['sales_month', date("m", $order_timestamp)]
            ])->first();
            $box_part = BoxPart::where('part_id', $part_id)->first();

            // Информация по состоянию 
            $partcount = OrderPart::where('order_id', '=', $order_id)->count();
            $partcountreturn = OrderPart::where([
                ['order_id', '=', $order_id],
                ['part_cancel', '=', 1]
            ])->orWhere([
                ['order_id', '=', $order_id],
                ['part_return', '=', 1]
            ])->count();
            
            // return $orderpart;
            // Фиксируем количество шт и стоимость
            $qty = $orderpart->order_count;
            $cost = $product->part_cost;
            
            if ($order_status == 1 or $order_status == 2)
            {
                // Переносим из заказа в продукты
                $orderpart->part_cancel = 1;
                $orderpart->order_count = $orderpart->order_count - $qty;
                $orderpart->save();
                
                $product->part_status = 0;
                $product->part_count = $product->part_count + $qty;
                $product->save();

            }

            if ($order_status == 2 or $order_status == 3)
            {                
                $sales->sales_turnover = $sales->sales_turnover - ($qty * $cost);
                $sales->save();
            }

        }

        if($order_status == 3 )
        {
            $orderpart->part_return = 1;
            $orderpart->order_status = 4;
            $orderpart->save();

            $box_part->box_hide = $box_part->box_box;
            $box_part->box_box = 33;
            $box_part->save();
        }

        if ( $order_status == 2 or $order_status == 3 or $order_status == 1 )
        {
            if ($partcount == $partcountreturn)
            {
                $orderlist->update([
                    'order_status' => $request->order_status != 3 ? 0 : 4
                ]);
            }

            if ($order_status == 2 or $order_status == 3)
            {
                if ($partcount == $partcountreturn)
                {
                    $sales->sales_orders = $sales->sales_orders - 1;
                    $sales->save();
                }
            }
        }

        return redirect()->back();
    }


    /**
     * | Checked Payment Order
     * | Подтверждение оплаты товара
     */
    public function orderEditChecked(Request $request)
    {
        $id = $request->id;
        $payment = $request->order_payment;


        $orderlist = Orderlist::find($id);
        $orderlist->update([
            'order_payment' => $payment,
            'order_status'  => 2
        ]);

        $orderparts = OrderPart::where('order_id', $orderlist->id);
        $orderparts->update([
            'payment_status' => $payment,
            'order_status'  => 2
        ]);

        // Убираем товар из таблицы продуктов
        foreach ($orderparts->get() as $item) {
            $product = Product::find($item->part_id);
            $product->part_count = $product->part_count - $item->order_count;
            if ($product->part_count == 0) {
                $product->part_status = 1;
            }
            $product->save();
        }


        $contact['id'] = $request->id;
        $contact['order_email'] = $orderlist->order_email;
        $contact['order_lname'] = $orderlist->order_lname;
        $contact['order_fname'] = $orderlist->order_fname;
        $contact['order_mname'] = $orderlist->order_mname;


        // Добавить сумму заказа в таблицу sales
        $sale = Sale::where('sales_year', Carbon::now()->format('Y'))
            ->where('sales_month', Carbon::now()->format('m'))
            ->first();

        if ($sale == NULL) {

            $saleNew = new Sale();
            $saleNew->sales_year = Carbon::now()->format('Y');
            $saleNew->sales_month = Carbon::now()->format('m');
            $saleNew->sales_turnover = $request->sum;
            $saleNew->sales_orders = 1;
            $saleNew->save();
            
        } else {

            $sale->sales_turnover = $sale->sales_turnover + $request->sum;
            $sale->sales_orders = $sale->sales_orders + 1;
            $sale->save();
            
        }

        //Mail to client
        if (!$request->mailstatus == 'on')
            Mail::to($contact['order_email'])->send(new CheckedEmail($contact));

        return redirect()->back();
    }

    /**
     * | Tracking Order
     * | Перевод заявки в статус отправлено и присвоение трекинг номера
     */
    public function orderEditTracking(Request $request)
    {
        $orderlist = Orderlist::find($request->id);
        $orderlist->order_tracking = $request->order_tracking;
        $orderlist->order_status = 3;
        $orderlist->save();

        $orderparts = OrderPart::where('order_id', $orderlist->id);
        $orderparts->update([
            'order_status'  => 3
        ]);

        $contact['id'] = $request->id;
        $contact['order_email'] = $orderlist->order_email;
        $contact['order_lname'] = $orderlist->order_lname;
        $contact['order_fname'] = $orderlist->order_fname;
        $contact['order_mname'] = $orderlist->order_mname;
        $contact['order_city'] = $orderlist->order_city;
        $contact['order_address'] = $orderlist->order_address;
        $contact['order_tracking'] = $orderlist->order_tracking;


        //Mail to client
        if (!$request->mailstatus == 'on')
            Mail::to($contact['order_email'])->send(new CheckedEmail($contact));

        return redirect()->back();

    }

    /**
     * | Delete Payment Order
     * | Удаление заявки
     */
    public function orderEditDelete(Request $request)
    {
        $id = $request->id;

        $orderlist = Orderlist::find($id);

        $orderparts = OrderPart::where([
            ['order_id', '=', $id],
            ['order_status', '>', 0],
            ['part_cancel', '=', 0]
        ]);
        $orderparts = $orderparts->selectPartInfo();
        $orderparts = $orderparts->getProductInfo();
        $orderparts = $orderparts->getBox();
        $orderparts = $orderparts->getImgMain();
        $orderparts = $orderparts->get();

        // foreach ( $orderparts as  $orderpart ) {
        //     if ($orderpart->part_count == 0 && $orderpart->part_status == 0) {
        //         $part_id[] = $orderpart->part_id;
        //     }
        // }

        return $orderparts->sum('part_cost');

        // $orderpart = OrderPart::where('order_id', $orderlist->id);
        // $orderpart->update([
        //     'part_cancel' => 1,
        //     'order_status'  => 0
        // ]);

        // $contact['id'] = $request->id;
        // $contact['order_email'] = $orderlist->order_email;
        // $contact['order_lname'] = $orderlist->order_lname;
        // $contact['order_fname'] = $orderlist->order_fname;
        // $contact['order_mname'] = $orderlist->order_mname;

        // //Mail to client
        // if (!$request->mailstatus == 'on')
        //     Mail::to($contact['order_email'])->send(new DeleteOrderEmail($contact));

        // return redirect()->back();

    }



    /**
     * salesEdit
     * Получаем сумму продаж
     */
    public function salesEdit()
    {
        $salelist = Sale::orderBy('sales_year', 'DESC')
        ->orderBy('sales_month', 'DESC')
        ->get();

        $monthcollection = [
            '1' => 'Январь', 
            '2' => 'Февраль',
            '3' => 'Март',
            '4' => 'Апрель',
            '5' => 'Май',
            '6' => 'Июнь',
            '7' => 'Июль',
            '8' => 'Август',
            '9' => 'Сентябрь',
            '10' => 'Октябрь',
            '11' => 'Ноябрь', 
            '12' => 'Декабрь',
        ];

        $salelist = $salelist->groupBy(['sales_year', 'sales_month']);

        return view('admin', [
            'page'              => 'sales',
            'salelist'          => $salelist,
            'monthcollection'   => $monthcollection
        ]); 
    }

    /**
     * getofferEdit
     * Получаем список запрашиваемых цен для предложения Нашли дешевле?
     */
    public function getofferEdit()
    {
        $pricerequest = PriceRequest::latest('id')->get();

        return view('admin', [
            'page'              => 'getoffer',
            'pricerequest'      => $pricerequest
        ]);
    }

    /**
     * getofferEditChecked
     * Отмечаем о обработке поля Нашли дешевле
     */
    public function getofferEditChecked( Request $request )
    {
        $pricerequest = PriceRequest::find($request->id);
        $pricerequest->checked = $request->show;
        $pricerequest->save();

        return redirect()->back();
    }

    /**
     * boxEdit
     * Получаем список всех коробок и их содержимое
     */
    public function boxEdit()
    {
        
        $box_parts = BoxPart::with('get_product');
        $box_parts = $box_parts->orderBy('box_box', 'ASC');
        // 0 коробка относитя к неотсортированным
        // и выводится на странице неотсортированных
        $box_parts = $box_parts->where('box_box', '>', 0);
        $box_parts = $box_parts->where('box_box', '!=', 20);
        $box_parts = $box_parts->get();
        $box_parts = $box_parts->groupBy('box_box');

        // 20 коробка для разбивки по 
        // фирмам по скольку там вся мелочь
        $box_parts_20 = BoxPart::with('get_product_20.company');
        $box_parts_20 = $box_parts_20->where('box_box', 20);
        $box_parts_20 = $box_parts_20->get();
        $box_parts_20 = $box_parts_20->groupBy('get_product_20.company.company');

        return view('admin', [
            'page'              => 'box',
            'box_parts'         => $box_parts,
            'box_parts_20'      => $box_parts_20
        ]);
    }

    /**
     * boxEditUnsort
     * Получаем список неотсортированных деталей
     */
    public function boxEditUnsort()
    {
        $box_parts = BoxPart::with(['get_product_unsort.tv', 'get_product_unsort.part_type']);
        $box_parts = $box_parts->orderBy('box_box', 'ASC');
        // 0 коробка относитя к неотсортированным
        // и выводится на странице неотсортированных
        $box_parts = $box_parts->where('box_box', '=', 0);
        $box_parts = $box_parts->get();
        $box_parts = $box_parts->groupBy(['get_product_unsort.tv.tv_model', 'get_product_unsort.tv.tv_datetime']);

        $boxes = Box::get();

        // + разобраться с коробкой №20

        return view('admin', [
            'page'              => 'boxunsort',
            'box_parts'         => $box_parts,
            'boxes'             => $boxes
        ]);
    }


    /**
     * boxEditUnsortAdd
     * Отправляем запчасть в выбранную коробку
     */
    public function boxEditUnsortAdd(Request $request)
    {

        $request->validate([
            'box_box' => 'required|numeric'
        ]);

        $box = BoxPart::find($request->id);

        $box->box_box = $request->box_box;
        $box->box_timestamp = Carbon::now()->timestamp;
        $box->timestamps = false;
        $box->save();

        return redirect()->back();

    }

    /**
     * boxEditControl
     * Управляем списком коробок
     */
    public function boxEditControl()
    {
        $boxes = Box::get();

        return view('admin', [
            'page'              => 'boxcontrol',
            'boxes'         => $boxes, 
        ]);
    }


    /**
     * boxEditControlDetail
     * Получаем детальное описание коробки
     */
    public function boxEditControlDetail($boxes_number)
    {
        $boxDetail = Box::where('boxes_number', $boxes_number)->first();

        if (!$boxDetail || !(int)$boxes_number)
            return redirect()->route('admin.box.control');

        return view('admin', [
            'page'          => 'boxcontrol',
            'boxDetail'     => $boxDetail,
        ]);
    }

    /**
     * boxEditControlDetailSave
     * Сохраняем детальное описание коробки
     */
    public function boxEditControlDetailSave(Request $request)
    {
        $boxDetail = Box::find($request->id);

        $boxDetail->boxes_name = $request->boxes_name;
        $boxDetail->boxes_description = $request->boxes_description;
        $boxDetail->save();

        return redirect()->route('admin.box.control');
    }


    /**
     * boxEditControlDetailCreate
     * Сохраняем детальное описание коробки
     */
    public function boxEditControlDetailCreate(Request $request)
    {
        $request->validate([
            'boxes_number' => 'required|alpha_dash|unique:boxes,boxes_number'
        ]);
        
        $boxDetail = new Box();
        $boxDetail->boxes_number = $request->boxes_number;
        $boxDetail->boxes_name = $request->boxes_name;
        $boxDetail->boxes_description = $request->boxes_description;
        $boxDetail->save();

        return redirect()->back();
    }

    /**
     * buyupEdit
     * Вывод списка скупаемых товаров
     */
    public function buyupEdit()
    {
        $skypka = Skypka::latest('id')->get();

        return view('admin', [
            'page'      => 'buyup',
            'skypka'    => $skypka
        ]);
    }

    /**
     * buyupEditDetail
     * Вывод списка скупаемых товаров
     */
    public function buyupEditDetail($id)
    {
        $skypka = Skypka::find($id);

        return view('admin', [
            'page'      => 'buyupdetail',
            'skypka'    => $skypka
        ]);
    }

    /**
     * buyupEditDetail
     * Вывод списка скупаемых товаров
     */
    public function buyupEditDetailUpdate(Request $request)
    {
        $request->validate([
            'skypka_self_cost' => 'numeric'
        ]);

        $skypka = Skypka::find($request->id);
        $skypka->skypka_self_cost = $request->skypka_self_cost;
        $skypka->skypka_status = $request->skypka_status;
        $skypka->save();

        return redirect()->route('admin.buyup');
    }


    /**
     * repairEdit
     * Вывод списка реанимирующихся товаров
     */
    public function repairEdit()
    {

        $companies = Company::get();
        $part_without_test = Product::with('company');
        $part_without_test = $part_without_test->where('part_condition', 1);
        $part_without_test = $part_without_test->paginate(30);

        
        return view('admin', [
            'page'      => 'repair',
            'companies'   => $companies,
            'part_without_test' => $part_without_test
        ]);
    }

    /**
     * listEdit
     * Вывод списка товаров
     */
    public function listEdit()
    {   
        $products = Product::with('tv');
        $products = $products->with('company');
        $products = $products->with('matrix');
        $products = $products->with('part_type');
        $products = $products->get();
        $products = $products->groupBy('part_type.parttype_type');
        
        return view('admin', [
            'page'      => 'list',
            'products'  => $products
        ]);
    }

    /**
     * listEditTxt
     * Вывод списка товаров
     */
    public function listEditTxt()
    {       
        $products = Product::with('tv');
        $products = $products->with('company');
        $products = $products->with('matrix');
        $products = $products->with('part_type');
        $products = $products->get();

        return view('admin', [
            'page'      => 'list',
            'products'  => $products
        ]);
    }

    /**
     * listEditAvito
     * Вывод списка товаров
     */
    public function listEditAvito(Request $request)
    {    

        $part_condition[1] = "(БТ)";
		$part_condition[2] = "(Н)";

        if (!$request->company)
            $request->company = 1;

        $companies = Company::get();

        if ($request->company == 'other') {
            $other = [];
            foreach ($companies as $company) {
                if ($company->id > 7) {
                    $other[] = $company->id;
                }
            }
        }

        

        $products = Product::with('part_type')
            ->where('part_status', 0)->get();



        if ($request->company != 'other') {
            $products = $products->where('company_id', $request->company);
            $companyName = $companies->where('id', $request->company)->first();
            $companyName = $companyName->company;
        } else {
            $products = $products->whereIn('company_id', $other);
            $companyName = '';
        }
            
        $products = $products->groupBy('part_type.parttype_type');

        $sort = collect([
            'Main Board',
			'Power Board',
			'T-Con',
			'Inverter',			
			'LED',
			'LED Backlight',
			'LED Driver',
			'Power Supply',
			'Logic Board',
			'YSUS',
			'XSUS',
			'Main AV Board',
			'Y-DRV BUFFER Y-SUS BOARD',
			'Y-Main',
			'X-Main',
            'Logic Main',
            '',
			'',
			'Buffer Board',
			'Logic Board',
			'Matrix Board'
        ]);

        $sortAdd = collect([
            'Unknown',
			'Buttons Board',
			'IR Sensor',
			'EMI Noise Filter',
			'Шлейфы к T-Con',
			'Power Button',
			'WiFi Module',
			'Bluetooth Module',
			'WLAN Module',
			'WiFi/BT Combo Module',
			'Cooler',
			'Connector Board',
			'Inputs',
			'Tuner Board',
			'IR / Button Board',
			'Индикаторы / Lights / H3E',
			'Touch Key Controller',
			'3D Emitter',
			'Speakers',
			'Camera',
			'Cart Reader',
			'IR Receptor',
			'Power Button + IR Sensor',
			'Common Interface',
			'Dimming',
			'CD ROM',
			'IR Receiver',
			'Side AV Board'
        ]);

        $sort = $sort->flip();
        $sortAdd = $sortAdd->flip();

        $sorted_array = $sort->merge($products);
        $sorted_array = $sorted_array->reverse();
        $sorted_array_add = $sortAdd->merge($sorted_array);
        $sorted_array_add = $sorted_array_add->reverse();     

        

        return view('admin', [
            'page'      => 'list',
            'companies' => $companies,
            'companySelected' => $request->company,
            'products' => $sorted_array_add,
            'part_condition' => $part_condition,
            'companyName'   => $companyName
        ]);
    }

    /**
     * listEditAvito
     * Вывод списка товаров
     */
    public function listEditAvitoModels()
    {       
        

        return view('admin', [
            'page'      => 'list'
        ]);
    }

    /**
     * listEditMonitor
     * Вывод списка товаров
     */
    public function listEditMonitor()
    {       
        

        return view('admin', [
            'page'      => 'list'
        ]);
    }




















    /**
     * Не закончено выше
     */

    
    // Static text
    /**
     * statictextEdit
     * Редактирование статический текст
     */
    public function statictextEdit()
    {
        $statictext = StaticText::where('name', 'about')->first();

        return view('admin', [
            'page'          => 'statictext',
            'statictext'    => $statictext
        ]);
    }

    /**
     * statictextEditUpdate
     * Редактирование статический текст
     */
    public function statictextEditUpdate(Request $request)
    {

        $name = $request->name;
        $value = $request->value;

        $statictext = StaticText::where('name', $name)->first();
        $statictext->value = $value;
        $statictext->save();

        return redirect()->back();
    }

    /**
     * statictextEditDelivery
     * Редактирование статический текст
     */
    public function statictextEditDelivery()
    {
        $statictext = StaticText::where('name', 'delivery')->first();

        return view('admin', [
            'page'          => 'statictext',
            'statictext'    => $statictext
        ]);
    }


    /**
     * statictextEditContacts
     * Редактирование статический текст
     */
    public function statictextEditContacts()
    {
        $statictext = StaticText::where('name', 'contacts')->first();

        return view('admin', [
            'page'          => 'statictext',
            'statictext'    => $statictext
        ]);
    }

    /**
     * sliderEdit
     * Редактирование слайдера на главной странице
     */
    public function sliderEdit()
    {
        
        $sliderimg = SliderImg::get();

        return view('admin', [
            'page'          => 'slider',
            'sliderimg'        => $sliderimg
        ]);
    }

    /**
     * sliderEditUpload
     * Редактирование слайдера на главной странице
     */
    public function sliderEditUpload(Request $request)
    {

        // cache the file
        $file = $request->file('slide');
        $type = $request->type;
        $position = $request->position;


        // generate a new filename. getClientOriginalExtension() for the file extension
        $filename = 'slider-slide-' . time() . '.' . $file->getClientOriginalExtension();

        // save to storage/app/photos as the new $filename
        $path = $file->storeAs('public/slides', $filename);

        // dd($filename);

        SliderImg::create([
            'slide' => $filename,
            'type'  => $type,
            'position'  => $position
        ]);
        
        return redirect()->back();
    }

    /**
     * sliderEditDelete
     * Редактирование слайдера на главной странице
     */
    public function sliderEditDelete(Request $request)
    {
        $slide_id = $request->id;
        $slide = SliderImg::find($slide_id);
        $slide_name = $slide->slide;

        Storage::delete('public/slides/' . $slide_name);

        $slide->delete();       
        
        return redirect()->back();

    }


    /**
     * companyEdit
     * Редактирование раздела TV в админ панеле
     */
    public function companyEdit()
    {
        $companies = Company::get();

        return view('admin', [
            'page'          => 'tv',
            'companies'           => $companies
        ]);
    }

    /**
     * companyEditTvs
     * Получаем список всех телевизоров
     */
    public function companyEditTvs($id)
    {
        $tvs = Tv::where('corp_id', $id)->with('get_matrix')->get();
        $tvs = $tvs->sortby('tv_model');

        $tv_condition[] = "Разбитая";
		$tv_condition[] = "Залитая";
        $tv_condition[] = "Неисправная";
        
        //Группы поставщиков
		$group[1] = '1';
		$group[2] = '2';
		$group[3] = '3';
		$group[4] = '4';
		$group[5] = '5';

        return view('admin', [
            'companyId'     => $id,
            'page'          => 'tv',
            'tvs'           => $tvs,
            'tv_condition'  => $tv_condition,
            'group_id'      => $group
        ]);

    }

    /**
     * companyEditTvsAdd
     * Добавляем новый телевизов
     */
    public function companyEditTvsAdd(Request $request)
    {

        $tv_datetime = $request->tv_datetime ? strtotime($request->tv_datetime) : '';

        $tv = Tv::create([
            'tv_model' => $request->tv_model,
            'tv_condition'  => $request->tv_condition,
            'group_id'  => $request->group_id,
            'tv_comment'    => $request->tv_comment,
            'corp_id'   => $request->corp_id,
            'tv_datetime'   => $tv_datetime,
            'tv_timestamp' => Carbon::now()->timestamp
        ]);
        // пока хз для чего
        DB::insert("INSERT INTO razbors (datetime, user_id, tv_count) VALUES (" . strtotime($request->tv_datetime) . ", 1, 1) ON DUPLICATE KEY UPDATE tv_count=(tv_count+1)");

        Matrix::create([
            'matrix_model'  => $request->matrix_model,
            'tv_id'         => $tv->id
        ]);


        return redirect()->back();
    }

    /**
     * companyEditTvsSingle
     * Получаем детальную информацию по телевизору
     */
    public function companyEditTvsSingle($companyId, $tvId)
    {
        $tv = Tv::where('id', $tvId)->with('get_matrix')->first();
        $products = Product::where('tv_id', $tvId)->with('part_type')->get();
        $parttype = PartType::get();
        $tv_images = TvImg::where('tv_id', $tvId)->orderBy('id', 'desc')->get();

        $tv_condition[] = "Разбитая";
		$tv_condition[] = "Залитая";
        $tv_condition[] = "Неисправная";
        
        //Группы поставщиков
		$group[1] = '1';
		$group[2] = '2';
		$group[3] = '3';
		$group[4] = '4';
        $group[5] = '5';
        
        // Состояние платы
        $part_condition[] = 'Рабочий';
        $part_condition[] = 'Без тестирования';
        $part_condition[] = 'Неисправный';

        return view('admin', [
            'page'              => 'tv',
            'companyId'         => $companyId,
            'tv'                => $tv,
            'tv_condition'      => $tv_condition,
            'group_id'          => $group,
            'products'          => $products,
            'part_condition'    => $part_condition,
            'parttype'          => $parttype,
            'tv_images'         => $tv_images
        ]);
    }

    /**
     * companyEditTvsUpdate
     * Обновление информации по телевизору
     */
    public function companyEditTvsUpdate(Request $request)
    {          

        $tv = Tv::find($request->tvId);

        $matrix = Matrix::where('tv_id', $request->tvId)->update([
            'matrix_model'    => $request->matrix_model
        ]);
        
        $products = Product::where([
            ['tv_id', '=', $tv->id],
            ['matrix_id', '=', $matrix->id]
        ])->update([
            'group_id'  => $request->group_id
        ]);

        $tv->update([
            'tv_model' => $request->tv_model,
            'tv_condition'  => $request->tv_condition,
            'group_id'  => $request->group_id,
            'tv_comment'    => $request->tv_comment
        ]);

        return redirect()->back();

    }


    /**
     * companyEditProduct
     * Получаем информацию по отдельному продукту
     */
    public function companyEditProduct($companyId, $tvId, $productId)
    {
        $product = Product::find($productId);
        $products = Product::where('tv_id', $tvId)->with('part_type')->get();
        $part_imgs = PartImg::where('product_id', $productId)->orderBy('id', 'desc')->get();
        $tv = Tv::where('id', $tvId)->with('get_matrix')->first();
        $parttype = PartType::get();

        // Состояние платы
        $part_condition[] = 'Рабочий';
        $part_condition[] = 'Без тестирования';
        $part_condition[] = 'Неисправный';

        return view('admin', [
            'page'              => 'tv',
            'tvId'              => $tvId,
            'companyId'         => $companyId,
            'productId'         => $productId,
            'product'           => $product,
            'products'          => $products,
            'part_imgs'         => $part_imgs,
            'tv'                => $tv,
            'part_condition'    => $part_condition,
            'parttype'          => $parttype,
        ]);
    }

    /**
     * companyEditProductAdd
     * Добавление нового продукта
     */
    public function companyEditProductAdd(Request $request)
    {
        $request->validate([
            'part_link' => 'required|alpha_dash|unique:products',
            'part_cost' => 'numeric',
            'part_count' => 'numeric'  
        ]);


        $product = new Product();
        $product->part_model = $request->part_model;
        $product->part_cost = $request->part_cost;
        $product->part_link = $request->part_link;
        $product->part_count = $request->part_count;
        $product->part_comment = $request->part_comment;
        $product->part_comment_for_client = $request->part_comment_for_client;
        $product->parttype_id = $request->parttype_id;
        $product->matrix_id = $request->matrix_id;
        $product->tv_id = $request->tv_id;
        $product->company_id = $request->company_id;
        $product->part_condition = $request->part_condition;
        $product->group_id = $request->group_id;
        $product->part_weight = $request->part_weight;        
        $product->save();

        return redirect()->back();
    }

    /**
     * companyEditImageUpload
     * Загружаем иизображение для телевизора
     */
    public function companyEditImageUpload(Request $request)
    {
        $request->validate([
            'slide' => 'mimes:jpeg,jpg,png,gif'
        ]);
        $file = $request->file('slide');
        $tvId = $request->tv_id;
        $companyId = $request->company_id;
        $cls = $request->cls;
        $productId = $request->productId;

        $fileupload = new UploadImage($file, $companyId, $tvId, $cls, $productId);

        $fileupload->upload();

        return redirect()->back();
    }

    /**
     * companyEditImageDelete
     * Удаляем иизображение для телевизора
     */
    public function companyEditImageDelete(Request $request)
    {
        $imgId = $request->imgId;
        $companyId = $request->companyId;
        $tvId = $request->tvId;

        if ($request->cls == 'TvImg')
        {
            $cls_img = TvImg::find($imgId);
            $image_name = $cls_img->tv_img_name;
        }

        if ($request->cls == 'PartImg')
        {
            $cls_img = PartImg::find($imgId);
            $image_name = $cls_img->part_img_name;
        }

        

        Storage::disk('products')->delete( $companyId . '/' . $tvId . '/' . $image_name);
        Storage::disk('products')->delete( $companyId . '/' . $tvId . '/m' . $image_name);

        $cls_img->delete();       
        
        return redirect()->back();
    }

    
    /**
     * companyEditProductUpdate
     * Обновляем информацию о продукте
     */
    public function companyEditProductUpdate(Request $request)
    {

        $request->validate([
            'part_link' => 'required|alpha_dash|unique:products,part_link,' . $request->productId,
            'part_cost' => 'numeric',
            'part_count' => 'numeric'  
        ]);


        Product::where('id', $request->productId)
            ->update([
                'part_condition' => $request->part_condition,
                'part_model' => $request->part_model,
                'parttype_id' => $request->parttype_id,                
                'part_comment' => $request->part_comment,
                'part_comment_for_client' => $request->part_comment_for_client,
                'part_link' => $request->part_link,
                'part_cost' => $request->part_cost,
                'part_count' => $request->part_count,
                'part_weight' => $request->part_weight
            ]);

        return redirect()->back();

    }


    /**
     * companyEditImageMainUpdate
     * Обновляем главное изображение продукта
     */
    public function companyEditImageMainUpdate(Request $request)
    {
        PartImg::where('product_id', $request->productId)
            ->update([
                'part_img_main' => 0
            ]);

        PartImg::where('id', $request->imgId)
        ->update([
            'part_img_main' => 1
        ]);

        return redirect()->back();
    }
    


    

}
