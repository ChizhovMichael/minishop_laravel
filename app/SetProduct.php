<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/*************
 * | Модель продукта/ таблицы products
 * | fillable для проверки заполняемых полей. Не забывать редактировать если 
 * | название полей в таблице изменяется
 * | 
 * | Регулировка подключения дополнительных таблиц к products
 * | belongsTo('App\Company', 'company_id'), где 1 - Модель (присоединяемая таблица) id, 2 - столбец из Product
 * | hasMany('App\Company', 'company_id'), где 1 - Модель (присоединяемая таблица), 2 - столбец из этой модели с id Product
 * | 1. Get Products Item (Получаем содержимое комплекта)


 **************/


class SetProduct extends Model
{
    //
    protected $fillable = [
        'set_id',
        'product_id',
        'product_count',
    ];    
}
