<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpDesk extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    const EXECUTION_ADDRESSES = [
        'Открытое шоссе, д. 6, корп. 12',
        'Нижняя Радищевская, д.12',
        '5-я Парковая, д. 51',
        'Ленинградское шоссе, д. 45',
        'ООЦ "ПАТРИОТ"',
        'Удаленное рабочее место',
    ];

    const HELP_DESK_CATEGORIES = [
        'Компьютер',
        'Принтер',
        'Почта',
        'Интернет',
        'Дистанционное подключение',
        'Телефония',
        'Новый сотрудник',
        'Иное'
    ];

    const HELP_DESK_TASK_STATUSES = [
        0 => 'Не принято',
        1 => 'В процессе',
        2 => 'Выполнено',
        3 => 'Не выполнено',
        4 => 'Ошибочная заявка'
    ];

    const HELP_DESK_EXECUTORS = [
        12 => 'Сабиров Рашит Алмазович',
        13 => 'Александр Вячеславович Буздайкин',
        14 => 'Ремизов Александр Сергеевич',
        53 => 'Губин Михаил Юрьевич',
        34 => 'Мосин Сергей Александрович',
        15 => 'Янков Никита Андреевич',
        330 => 'Басов Даниил Александрович',

        389 => 'Панченко Максим Викторович',
        390 => 'Корешков Валерий Сергеевич',
        392 => 'Чесноков Дмитрий Александрович',
        391 => 'Кудряшов Сергей Александрович',
        393 => 'Пашов Даниил Валерьевич',
        394 => 'Николаичев Антон Александрович',
    ];


    static function getExecutionAddressesKeys(): array
    {
        return array_keys(self::EXECUTION_ADDRESSES);
    }

    static function getHelpDeskCategoriesKeys(): array
    {
        return array_keys(self::HELP_DESK_CATEGORIES);
    }

    static function getHelpDeskTaskStatusesKeys(): array
    {
        return array_keys(self::HELP_DESK_TASK_STATUSES);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
