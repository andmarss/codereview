<?php

namespace App\Models;

use App\Crm\Bitrix24\BitrixDeal;
use App\DealHandlers\DealHandler;
use App\Jobs\DeleteDealInBitrix;
use App\Workflow\WorkflowException;
use Brexis\LaravelWorkflow\Traits\WorkflowTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Workflow\Event\Event;
use Crabler\Common\Models\Traits\Parameterizable;
use App\DealHandlers\PrintPhotobook;
use Symfony\Component\Workflow\TransitionBlocker;
use Symfony\Component\Workflow\TransitionBlockerList;
use Yandex\Geo\Exception;

/**
 * Class Deal
 * @package App\Models
 *
 * @property integer $id
 * @property string  $deal_type_id
 * @property array   $places
 * @property integer $user_id
 * @property array   $params
 * @property integer $bitrix_deal_id
 * @property integer $bitrix_update
 * @property string  $finished_at
 * @property float   $cost
 * @property int     $discount
 * @property int     $src_cost
 *
 * @property User       $user
 * @property DealFile[] $files
 * @property Delivery   $delivery
 *
 * @property PrintPhotobook $handler
 * @property BitrixDeal $crm
 */
class Deal extends Model
{
    use SoftDeletes, WorkflowTrait, Parameterizable;

    /**
     * @var TransitionBlocker[]
     */
    private $blockerList = [];

    //типы ошибок
    const ERROR_TYPE_WARNING = 'warning';
    const ERROR_TYPE_ERROR   = 'error';

    /**
     * необходимо синхронизировать запись с Бириксом
     */
    const BITRIX_NEED_UPDATE = 1;

    /**
     * @var DealHandler|PrintPhotobook
     */
    private $h;

    /**
     * @var BitrixDeal
     */
    private $_crm;

    /**
     * @var Delivery
     */
    private $_delivery;

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var string
     */
    protected $table = '';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $casts = [];

    /**
     * @var array
     */
    protected $dates = [];


    /**
     * Логирование изменений с заказом
     *
     * @param Event $event
     */
    public function logTransition(Event $event)
    {
        $log = new DealLog();
        $log->deal_id = $this->id;
        $log->transition = $event->getTransition()->getName();
        $log->params = [
            'places' => implode(', ', array_keys($event->getMarking()->getPlaces())),
            'params' => $this->params,
        ];
        $log->save();
    }

    /**
     * Логирование изменений с заказом
     *
     * @param WorkflowException $e
     */
    public function logException(WorkflowException $e)
    {
        $log = new DealLog();
        $log->deal_id = $this->id;
        $log->transition = null;
        $log->params = [
            'msg' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'params' => $this->params,
        ];
        $log->save();
    }


    /**
     * Имя workflow
     *
     * @return string
     */
    public function getWorkflowName()
    {
        return $this->deal_type_id;
    }

    /**
     * Получить workflow
     *
     * @return \Symfony\Component\Workflow\Workflow
     */
    public function getWorkflow()
    {
        return \Workflow::get($this, $this->getWorkflowName());
    }

    /**
     * @param TransitionBlockerList $blockerList
     */
    public function addBlocker(TransitionBlocker $blocker)
    {
        $this->blockerList[] = $blocker;
    }

    public function clearBlockers()
    {
        $this->blockerList = [];
    }

    /**
     * @param TransitionBlockerList $blockerList
     */
    public function getBlockers()
    {
        return $this->blockerList;
    }

    /**
     * Адрес для просмотра текущего состояния сделки
     *
     * @return string
     */
    public function getDealUrl()
    {
        $place = $this->handler->getClientPlace();
        return $place ?
            strtolower($this->deal_type_id) . "/" . $place . "/" . $this->id:
            strtolower($this->deal_type_id) . "/info/" . $this->id;
    }

    /**
     * Имена transitions, доступных для данного заказа
     *
     * @return array
     */
    public function getEnabledTransitionNames(): array
    {
        $transitions = [];
        foreach ($this->getWorkflow()->getEnabledTransitions($this) as $transition) {
            $transitions[] = $transition->getName();
        }

        return $transitions;
    }


    /**
     * Обработчик для сделки, в котором содержится вся специфичная для расчета процесса догика
     *
     * @return DealHandler
     */
    public function getHandlerAttribute()
    {
        if ($this->h) {
            return $this->h;
        }

        $class = 'App\\DealHandlers\\' . $this->deal_type_id;
        $this->h = new $class($this);

        return $this->h;
    }

    /**
     * Обработчик для сделки, в котором содержится вся специфичная для расчета процесса догика
     *
     * @return BitrixDeal
     */
    public function getCrmAttribute()
    {
        if ($this->_crm) {
            return $this->_crm;
        }

        $this->_crm = new BitrixDeal($this);

        return $this->_crm;
    }


    /**
     * Обработчик для сделки, в котором содержится вся специфичная для расчета процесса догика
     *
     * @deprecated используй $this->handler
     *
     * @return DealHandler
     */
    public function getHandler()
    {
        if ($this->h) {
            return $this->h;
        }

        $class = 'App\\DealHandlers\\' . $this->deal_type_id;
        $this->h = new $class($this);

        return $this->h;
    }

    /**
     * Возвращает отношение к {@see DealFile::class}
     *
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(DealFile::class, 'deal_id', 'id');
    }

    /**
     * Возвращает отношение к {@see User::class}
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Сбросить ошибку пользователя
     *
     * @param $type
     */
    public function resetErrors($type)
    {
        $this->forgetParam('errors.'.$type);
        $this->save();
    }

    /**
     * Сохранить ошибку пользователя
     *
     * @param string $type
     * @param array $errors
     */
    public function saveClientError(string $type, array $errors)
    {
        $this->setParam('errors.'.$type, $errors);
        $this->save();
    }

    /**
     * Стоимость предоплаты за доставку
     *
     * @return float|null
     */
    public function getDeliveryPrepay()
    {
        if (!$this->delivery || !$this->delivery->company) {
            return null;
        }

        return $this->delivery->company->getCost(
            $this->delivery->type,
            $this->getWeight()
        );
    }

    /**
     * Получение свойства $this->>delivery
     *
     * @return Delivery
     */
    public function getDeliveryAttribute()
    {
        if ($this->_delivery) {
            return $this->_delivery;
        }

        $this->_delivery = Delivery::query()->where('deal_id', $this->id)->first();

        if (!$this->_delivery) {
            $this->_delivery = new Delivery();
            $this->_delivery->deal_id = $this->id;
        }

        return $this->_delivery;
    }

    /**
     * Расчет стоимости доставки
     */
    public function calcDelivery()
    {
        if (!$this->delivery) {
            return;
        }

        $account = $this->user->getAccount();

        /**
         * Если новая запись и еще не известно чем отправлять, сбрасываем стоимость
         */
        if (!$this->delivery->id && empty($this->delivery->delivery_company_id)) {
            return;
        }

        $this->delivery->load('company');

        //если доставку оплачивает клиент
        if (!$this->delivery->company->isPrepay()) {
            $account->returnDelivery($this->delivery);
            $this->delivery->cost = 0;
            $this->delivery->save();
            return;
        }

        /*** доставку оплачиваем мы, поэтому нам нужно списать с баланса деньги за нее ***/

        //если в CRM установлена стоимость доставки
        //мы устанавливаем именно эту стоимость доставки
        if ($this->crm->getDeliveryCost()) {
            //если стоимость уже была сохранена, ничего не делаем
            if ($this->delivery->cost == $this->crm->getDeliveryCost()) {
                return;
            }

            //возвращаем предыдущую стоимость
            $account->returnDelivery($this->delivery);

            $this->delivery->cost = $this->crm->getDeliveryCost();
            $this->delivery->save();

            //списываем новую
            $account->payDelivery($this->delivery);

            return;
        }

        //если в CRM еще нет стоимости, блокируем средства
        if ($this->delivery->cost == 0) {
            $this->delivery->cost = $this->delivery->company->getCost($this->delivery->type, $this->getWeight());
            $this->delivery->save();
            $account->payDelivery($this->delivery, true);
        }
    }

    /**
     * Масса заказа
     * Пока что тупо считаем 1 книга = 300г
     *
     * @return float
     */
    public function getWeight()
    {
       return $this->handler->calculator->getBooksNum() * 0.3;
    }


    /**
     * Сделка оплачена
     */
    public function paid()
    {
        $this->setParam('pay_status', true);
        $this->save();
    }

    /**
     * Оплачен заказ или нет
     *
     * @return bool
     */
    public function isPaid()
    {
        return (bool)$this->getParam('pay_status');
    }

    /**
     * Возвращает уже оплаченные сделки
     * @param Builder $query
     * @return Builder
     */
    public function scopeGetPaid(Builder $query)
    {
        return $query->where(['params->pay_status' => true]);
    }
    /**
     * Возвращает сделки, у которых не установлены переданные свойства
     * свойства, на которые происходит проверка, устанавливаются 'через.точку'
     * @param Builder $query
     * @param string $params
     * @return Builder
     */
    public function scopeParamsNotContains(Builder $query, string $params)
    {
        $params = str_replace('.', '->', $params);

        return $this->scopeGetPaid($query)
            ->whereNull("params->${params}");
    }
}
