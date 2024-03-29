<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Mail\ContactEmail;
use App\Http\Controllers\NavigationController;
use Illuminate\Support\Facades\Auth;


/*************
 * | Контролер продукта/ таблицы products
 * | fillable для проверки заполняемых полей. Не забывать редактировать если 
 * | название полей в таблице изменяется
 * | 
 * | 1. Get Page Contact (Получаем страницы контактов /contacts)
 * | 2. Get Page Delivery (Получаем страницы доставки /delivery)
 * | 3. 
 **************/

class ContactController extends Controller
{
    //

    use NavigationController;


    /**
     * Get Page Contact
     * Получаем страницы контактов /contacts
     */
    public function getPageContact() {

        return view('page/contacts', [
            'navigations'       =>  $this->navigation(),
            'contacts'          => collect($this->contacts()),
            'cart'              =>  $this->getCartCount(),
            'user'              =>  Auth::user(),
            'statictext'        =>  collect($this->statictext())
        ]);
    }

    /**
     * Get Page Delivery
     * Получаем страницы доставки /delivery
     */
    public function getPageDelivery() {

        return view('page/delivery', [
            'navigations'       =>  $this->navigation(),
            'contacts'          => collect($this->contacts()),
            'cart'              =>  $this->getCartCount(),
            'user'              =>  Auth::user(),
            'statictext'        =>  collect($this->statictext())
        ]);
    }


    /**
     * Get Page Private
     * Получаем страницы политики конфиденциальности /private
     */
    public function getPagePrivate() {

        return view('page/private', [
            'navigations'       =>  $this->navigation(),
            'contacts'          => collect($this->contacts()),
            'cart'              =>  $this->getCartCount(),
            'user'              =>  Auth::user(),
        ]);
    }



    /**
     * Get Page Regulations
     * Получаем страницы политики конфиденциальности /regulations
     */
    public function getPageRegulations() {

        return view('page/regulations', [
            'navigations'       =>  $this->navigation(),
            'contacts'          => collect($this->contacts()),
            'cart'              =>  $this->getCartCount(),
            'user'              =>  Auth::user(),
        ]);
    }


    /**
     * Push form from contacts page
     * Отправка формы со страницы контакты
     */
    public function mailpost( Request $request )
    {
        $request->validate([
            'email' => 'required|email|max:255|string',
            'name'  => 'required|max:255|string',
            'message'  => 'required|string',
        ]);

        $contact['name'] = $request->name;
        $contact['email'] = $request->email;
        $contact['tel'] = $request->tel;
        $contact['message'] = $request->message;

        $contacts = collect($this->contacts());

        // Mail delivery logic goes here
        Mail::to($contacts->get('mailMain')->value)->send(new ContactEmail($contact));

        return redirect()->back()->with('success', 'Спасибо за ваше сообщение!')
        ->with('message', 'Спасибо за вашу обратную связь. Надеемся, что наш сервис вас полностью удовлетворяет. Ваше письмо не останется без внимания.');

    }
}
