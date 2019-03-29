<template>
    <div v-if="isSelected" class="relative">
        <div class="bg-40 mb-4 py-3">
            <div class="flex justify-start items-center ml-auto mb-4">
                &#8592;&nbsp;&nbsp;<!-- <-\s\s -->
                <a href="javascript:void(0);"
                   class="text-black"
                   @click.prevent="goBack">Мои заказы</a>
                &nbsp;&nbsp;/&nbsp;&nbsp;<!-- \s\s/\s\s -->
                <heading :level="1" class="font-semibold">Заказ #{{ id }}</heading>
            </div>
        </div>

        <heading :level="2" class="flex mb-10 font-semibold" v-if="type">{{type}}</heading>

        <div class="flex flex-wrap">

            <!--Навигация-->

            <div class="lg:w-1/4 md:w-full sm:w-full">
                <ul class="flex list-reset flex-wrap mb-6">
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           >Параметры заказа</a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           >Загрузка файлов</a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           >Оплата</a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           :class="`flex  ${isDeliveryDataEditingPage() ?
                           'bg-blue hover:bg-blue-dark text-white font-bold py-4 px-4 no-underline text-xs' :
                           ''} text-white font-bold py-4 px-4 no-underline text-xs`"
                           @click.prevent="">Информация о доставке</a>
                    </li>
                </ul>
            </div>

            <!--Шаг №4-->

            <div class="lg:w-3/4 md:w-full sm:w-full lg:pl-2 md:pl-0 sm:pl-0" v-if="client_place !== null">
                <div>

                    <heading :level="3" class="flex mb-5 font-semibold">Информация о доставке</heading>

                    <!--
                        Алерт
                        1) Если данных о доставке еще нет, и кнопка "Заполнить информацию о заказе" не нажата - алерт синий
                        2) Если кнопка нажата, или данные присутствуют, но есть ошибки - алерт оранжевый
                        3) Если все нужные данные заполнены, и ошибок нет - алерт зеленый
                    -->

                    <div :class="showInfoBlockButtons
                                    ? `bg-blue-lightest border-t border-b border-blue text-blue-dark px-4 py-3 text-center`
                                    : (errors.length || !deliveryDataIsNull(delivery))
                                    ? `bg-orange-lightest border-l-4 border-orange text-orange-dark p-4 text-sm text-center`
                                    : `bg-teal-lightest border-t-4 border-teal rounded-b text-teal-darkest px-4 py-3 text-center`"
                         role="alert" v-if="showInfoBlockButtons || errors.length || !deliveryDataIsNull(delivery)">
                        <div v-if="showInfoBlockButtons || errors.length || !deliveryDataIsNull(delivery)">
                            <p class="mb-3"><b>Мы уже приступили к изготовлению Вашего заказа.</b></p>
                            <p>Он будет готов {{date}}.</p>
                            <p>До этого момента Вы можете редактировать информацию о способе доставки заказа.</p>
                        </div>

                        <!-- Блок с кнопками внутри алерта -->

                        <div class="flex flex-wrap w-full mt-3" v-if="showInfoBlockButtons">
                            <div class="flex w-1/2 justify-center">
                                <button
                                    class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-4 rounded"
                                    @click.prevent="showInfoBlockButtons = false"
                                >
                                    Заполнить информацию о доставке
                                </button>
                            </div>
                            <div class="flex w-1/2 justify-center items-center">
                                <transition name="fade" v-if="showWaitingText === false">
                                    <button
                                        class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-4 rounded"
                                        @click.prevent="showWaitingText = true"
                                        v-if="showWaitingText === false"
                                    >
                                        Я сделаю это позже
                                    </button>
                                </transition>
                                <transition name="fade" v-if="showWaitingText">
                                    <p v-if="showWaitingText">Будем ждать, не забудьте.</p>
                                </transition>
                            </div>
                        </div>
                    </div>

                    <!-- Форма -->

                    <div v-if="showInfoBlockButtons === false" class="mt-5">
                        <heading :level="4" class="flex mb-3 font-semibold" v-if="companies.length">Выберите компанию</heading>

                        <card class="mb-5 px-3 py-3" v-if="companies.length">
                            <ul class="flex list-reset flex-wrap">
                                <li v-for="company in companies"
                                    class="p-2 cursor-pointer lg:w-1/4 md:w-1/3 sm:w-full hover:bg-blue hover:text-white md:mb-3 sm:mb-3"
                                    v-bind:class="`${(selectedCompany && company.id === selectedCompany.id) ? 'bg-blue' : ''}`"
                                    :key="company.id"
                                    @click.prevent="selectCompany(company)">
                                    <div>
                                        <div class="w-full h-auto mb-4">
                                            <img :src="`/img/delivery/${company.id}.PNG`" class="w-full h-auto" alt="">
                                        </div>
                                        <p class="font-semibold text-base" v-bind:class="(selectedCompany && company.id === selectedCompany.id) ?
                             'color-white' :
                              'color-black'">{{company.name}}</p>
                                    </div>
                                </li>
                            </ul>
                        </card>
                        <!--
                        Блок будет показан, если:
                        1. Выбрана компания
                        2. У компании есть способы доставки (хранится в объекте deliveryTypes, где
                        ключем способов доставки является id компании, а значением - сами способы доставки)
                        -->
                        <heading :level="4"
                                 class="flex mb-3 font-semibold"
                                 v-if="(selectedCompany !== null) && deliveryTypes[selectedCompany.id]">Выберите способ доставки</heading>

                        <card class="mb-5 px-3 py-3" v-if="(selectedCompany !== null) && deliveryTypes[selectedCompany.id]">

                            <ul class="flex list-reset flex-wrap">
                                <li class="w-full" v-for="deliveryType in deliveryTypes[selectedCompany.id].types">
                                    <Checkbox
                                        :active="selectedDeliveryType ? deliveryType === selectedDeliveryType : false"
                                        :text="deliveryTypesTitle[deliveryType.id]"
                                        :subtext="getSubtextFromDeliveryType(deliveryType)"
                                        :element="deliveryType"
                                        @change="checkboxChange"
                                    />
                                </li>
                            </ul>
                            <!--
                            Блок будет показан, если
                            1. Выбрана компания
                            2. Компания требует предоплату (selectedCompany.params.is_prepay === true)
                            3. И установлена сумма предоплаты
                            -->
                            <div class="bg-orange-lightest border-l-4 border-orange text-orange-dark p-4 text-sm mt-5"
                                 role="alert"
                                 v-if="selectedCompany && selectedCompany.params.is_prepay && prepaySum !== null">
                                <p>При данном способе доставки на вашем балансе будут заблокированы {{prepaySum}} руб.</p>
                                <p>После того, как станет известна точная стоиомсть заказа, мы вернем на баланс разницу.</p>
                            </div>
                        </card>
                        <!--
                        Если выбранный способ доставки - доставка в пункт самовывоза
                        то заголовок будет 'Адрес пункта выдачи'
                        иначе - 'Адрес доставки'
                        -->
                        <heading :level="4" class="flex mb-3 font-semibold">
                            {{
                            selectedDeliveryType && parseInt(selectedDeliveryType.id) === 2 ?
                            'Адрес пункта выдачи' :
                            'Адрес доставки'
                            }}
                        </heading>

                        <card class="mb-5 px-3 py-3">
                            <!--
                            Блок будет показан, если:
                            1. Выбран способ доставки
                            2. Выбранный способ доставки - доставка в пункт самовывоза
                            -->
                            <div class="flex border-b border-40 mb-3" v-if="selectedDeliveryType && !(parseInt(selectedDeliveryType.id) === 2)">
                                <div class="w-1/5 py-6 px-8">
                                    <label class="inline-block text-80 pt-2 leading-tight">
                                        Почтовый индекс
                                    </label>
                                </div>
                                <div class="py-6 px-8 w-4/5">
                                    <input v-model="postcode"
                                           @change="deliveryChange({postcode})"
                                           type="text"
                                           placeholder="Почтовый индекс"
                                           class="w-full form-control form-input form-input-bordered">
                                </div>
                            </div>
                            <!--
                            Блок будет показан, если:
                            1. Выбрана компания
                            2. Выбран тип доставки
                            3. Тип доставки - доставка в пункт самовывоза
                            4. У компании в params.pints есть ссылка
                            Если хотя бы один из этих пунктов не совпадает -
                            блок будет скрыт
                            -->
                            <div class="bg-orange-lightest border-l-4 border-orange text-orange-dark p-4 text-sm mt-5"
                                 role="alert"
                                 v-if="selectedDeliveryType &&
                         parseInt(selectedDeliveryType.id) === 2 &&
                         (selectedCompany && selectedCompany.params.points)">
                                <p>Выберите на сайте транспортной компании подходящий пункт выдачи и внесите его адрес в поля ниже. Адреса пунктов выдачи вы можете найти по
                                    <a :href="selectedCompany.params.points"
                                       target="_blank"
                                       class="text-orange-dark underline text-sm hover:text-orange-darker">
                                        ссылке
                                    </a>.
                                </p>
                            </div>

                            <div class="flex border-b border-40 mb-3">
                                <div class="w-1/5 py-6 px-8">
                                    <label class="inline-block text-80 pt-2 leading-tight">
                                        Регион
                                    </label>
                                </div>
                                <div class="py-6 px-8 w-4/5">
                                    <input v-model="region"
                                           @change="deliveryChange({region})"
                                           type="text"
                                           placeholder="Регион"
                                           class="w-full form-control form-input form-input-bordered">
                                </div>
                            </div>

                            <div class="flex border-b border-40 mb-3">
                                <div class="w-1/5 py-6 px-8">
                                    <label class="inline-block text-80 pt-2 leading-tight">
                                        Адрес
                                    </label>
                                </div>
                                <div class="py-6 px-8 w-4/5">
                                    <input v-model="address"
                                           @change="deliveryChange({address})"
                                           type="text"
                                           placeholder="Адрес"
                                           class="w-full form-control form-input form-input-bordered">
                                </div>
                            </div>
                        </card>

                        <heading :level="4" class="flex mb-3 font-semibold">
                            Получатель
                        </heading>

                        <card class="mb-5 px-3 py-3">
                            <div class="flex border-b border-40 mb-3">
                                <div class="w-1/5 py-6 px-8">
                                    <label class="inline-block text-80 pt-2 leading-tight">
                                        Получатель
                                    </label>
                                </div>
                                <div class="py-6 px-8 w-4/5">
                                    <input v-model="receiver"
                                           @change="deliveryChange({person: receiver})"
                                           type="text"
                                           placeholder="Получатель"
                                           class="w-full form-control form-input form-input-bordered">
                                </div>
                            </div>
                            <!--
                            Блок будет показан, если:
                            1. Выбрана компания
                            2. Компания требует указание паспортных данных
                            (selectedCompany.params.is_need_passport === true)
                            -->
                            <div class="flex border-b border-40 mb-3 items-center"
                                 v-if="selectedCompany && selectedCompany.params.is_need_passport">
                                <div class="w-1/5 py-6 px-8">
                                    <label class="inline-block text-80 pt-2 leading-tight">
                                        Паспортные данные получателя
                                    </label>
                                </div>
                                <div class="py-6 px-8 w-2/5">
                                    <input v-model="passportSeries"
                                           @change="deliveryChange({passportSeries, passportNumber})"
                                           type="text"
                                           placeholder="Серия паспорта"
                                           class="w-full form-control form-input form-input-bordered">
                                </div>
                                <div class="py-6 px-8 w-2/5">
                                    <input v-model="passportNumber"
                                           @change="deliveryChange({passportSeries, passportNumber})"
                                           type="text"
                                           placeholder="Номер паспорта"
                                           class="w-full form-control form-input form-input-bordered">
                                </div>
                            </div>

                            <div class="flex border-b border-40 mb-3">
                                <div class="w-1/5 py-6 px-8">
                                    <label class="inline-block text-80 pt-2 leading-tight">
                                        Телефон
                                    </label>
                                </div>
                                <div class="py-6 px-8 w-4/5">
                                    <input v-model="phone"
                                           @change="deliveryChange({phone})"
                                           v-mask="'7 ### ### ## ##'"
                                           type="text"
                                           placeholder="Номер телефона"
                                           class="w-full form-control form-input form-input-bordered">
                                </div>
                            </div>
                        </card>
                    </div>
                </div>

            </div>
        </div>
        <div class="lg:w-3/4 md:w-full sm:w-full" v-if="client_place === null">
            <heading :level="3" class="flex mb-3 font-semibold">Информация о заказе</heading>

            <Accordion :items="accordionItems" class="w-full rounded overflow-hidden shadow-lg mt-8" v-if="accordionItems.length > 0">
                <Item
                    slot="accordion"
                    slot-scope="{item, selectItem}"
                    :item="item"
                >
                    <div slot="header" @click="selectItem(item)">
                        <div class="border-b border-grey-light p-4 cursor-pointer">
                            <p class="font-bold text-sm">{{item.title}}</p>
                        </div>
                    </div>

                    <div slot="content" v-if="item.active" class="p-4 bg-grey-lightest" >
                        <div v-if="item.tag === 'p'" v-for="content in item.content">
                            <p v-for="(value, key) in content"
                               v-if="getType(value) === 'string' || getType(value) === 'number'"
                               class="mb-4"
                            >
                                <b>{{key}}:</b>&nbsp;&nbsp;{{value}}
                            </p>
                        </div>

                        <div v-else-if="item.tag === 'div'">
                            <div class="w-full flex flex-wrap mt-12 -mx-2">
                                <heading :level="3" class="flex font-semibold w-full" v-if="item.content.length > 1">Книга {{content.number}}</heading>
                                <div class="mb-8 mx-2 image-container"
                                     v-for="file in content.files"
                                     :style="`width: ${110 * file.sizeRate}px;`">

                                    <div :class="file.url ? 'bg-transparent' : 'bg-grey'">
                                        <div class="relative w-full flex justify-center items-center">
                                            <img :src="file.url"
                                                 :alt="file.title"
                                                 v-show="file.url"
                                                 :style="`width: ${110 * file.sizeRate}px; height: 110px;`" />
                                        </div>
                                        <p class="text-center">{{file.title}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Item>
            </Accordion>
        </div>
        <Errors
            id="errors"
            :errors="errors"
        />
        <Buttons
            id="buttons"
            :sum="src_cost"
            :discount="discount"
            :sumWithDiscount="cost"
            :working="working"
            @delete="goTransition('delete')"
        />
    </div>
</template>

<script>
    import axios from 'axios';
    import Errors from '../errors/Index';
    import Buttons from '../buttons/Index';
    import Checkbox from '../custom-checkbox/Index';
    import userOrdersMixin from '../../mixin';
    import Accordion from '../accordion/Index';
    import Item from '../accordion/Item';

    export default {
        name: "Index",

        props: ['id'],

        mixins: [userOrdersMixin],

        data(){
            return {
                // Список компаний
                // И выбранная компания
                companies: [],
                selectedCompany: null,

                // Способ доставки
                deliveryTypes: [],
                selectedDeliveryType: null,
                deliveryTypesTitle: null,

                // Адрес доставки
                postcode: null,
                region: null,
                address: null,

                // Получатель
                receiver: null,
                passportSeries: null,
                passportNumber: null,
                phone: null,

                prepaySum: null,

                date: '',

                client_place: null,

                accordionItems: [],

                showWaitingText: false,
                showInfoBlockButtons: false,

                delivery: {}
            }
        },

        methods: {
            isDeliveryDataEditingPage(){
                return window.location.pathname.match(/printphotobook\/delivery_data_editing\/\d+/g);
            },
            /**
             * Устанавливает выбранную компанию
             * @param company
             */
            selectCompany(company) {
                this.selectedCompany = company;

                axios.post(`/api/photobook/delivery_data_editing/${this.id}`, {
                    _token: this.csrf_token,
                    delivery_company_id: company.id
                }).then(({data}) => {
                    if(data.access) {
                        this.prepaySum = data.prepay;
                        /**
                         * @type {array}
                         */
                        this.updateErrors(data, data => {
                            if(data.errors && data.errors.length) {
                                /**
                                 * Если ошибки есть - размещаем компонент ошибок
                                 * над компонентом кнопок
                                 */
                                this.placeErrorsComponent();
                            }
                        });
                        /**
                         * @type {string|null}
                         */
                        this.updateClientPlace(data);
                        // если у компании есть способ доставки по умолчанию
                        // выбираем его
                        if(this.selectedCompany && data.delivery.type) {
                            /**
                             * Ищем тип доставки, если он был выбран
                             * @type {object}
                             */
                            this.selectedDeliveryType = this.deliveryTypes[this.selectedCompany.id].types.find(type => {
                                return type.id === data.delivery.type;
                            });
                        }
                    }
                });
            },
            /**
             * Возвращает текст, который распологается
             * внутри скобок в описании способа доставки
             * @param deliveryType
             * @return {string}
             */
            getSubtextFromDeliveryType(deliveryType){
                if(deliveryType && deliveryType.price) {
                    let str = '';

                    Object.keys(deliveryType.price).forEach(numBooks => {
                        let price = deliveryType.price[numBooks];

                        if(parseInt(numBooks) === 1) {
                            str += `около ${price} руб. за ${numBooks} книгу, `;
                        } else {
                            str += `${price} руб. за тираж`;
                        }
                    });

                    return str ? `(${str})` : '';
                }
            },
            /**
             * Обработчик изменения на чек-боксе
             * @param deliveryType
             */
            checkboxChange(deliveryType){
                this.selectedDeliveryType = deliveryType;

                axios.post(`/api/photobook/delivery_data_editing/${this.id}`, {
                    _token: this.csrf_token,
                    deliveryType: deliveryType.id
                }).then(({data}) => {
                    if(data.access){
                        /**
                         * @type {array}
                         */
                        this.updateErrors(data, data => {
                            if(data.errors && data.errors.length) {
                                /**
                                 * Если ошибки есть - размещаем компонент ошибок
                                 * над компонентом кнопок
                                 */
                                this.placeErrorsComponent();
                            }
                        });
                        /**
                         * @type {object}
                         */
                        this.updateButtons(data);
                        /**
                         * @type {array}
                         */
                        this.updateTransitions(data);
                        /**
                         * @type {string|null}
                         */
                        this.updateClientPlace(data);
                    }
                });
            },

            deliveryChange(object = {}){
                if(object && Object.keys(object).length && !this.isSomeObjectValuesAreEmpty(object)) {
                    axios.post(`/api/photobook/delivery_data_editing/${this.id}`, {
                        _token: this.csrf_token,
                        ...object
                    }).then(({data}) => {
                        if(data.access){
                            /**
                             * @type {array}
                             */
                            this.updateErrors(data, data => {
                                if(data.errors && data.errors.length) {
                                    /**
                                     * Если ошибки есть - размещаем компонент ошибок
                                     * над компонентом кнопок
                                     */
                                    this.placeErrorsComponent();
                                }
                            });
                            /**
                             * @type {object}
                             */
                            this.updateButtons(data);
                            /**
                             * @type {array}
                             */
                            this.updateTransitions(data);
                            /**
                             * @type {string|null}
                             */
                            this.updateClientPlace(data);
                        }
                    });
                }
            },
            /**
             * Проверяет, есть ли в объекте хотя бы одно значение
             * равное null
             * @param object
             * @return {boolean}
             */
            isSomeObjectValuesAreEmpty(object) {
                if(this.getType(object) === 'object' && Object.keys(object).length > 0) {
                    return Object.keys(object).some(key => {
                        return object[key] === null
                    });
                } else {
                    return false;
                }
            },
            /**
             * Обновляет состояние кнопок
             * @param data
             */
            updateButtons(data){
                if(data.buttons) {
                    Nova.dealStore.dispatch('updateBackwardButton', null);
                    Nova.dealStore.dispatch('updateDeleteButton', null);
                    Nova.dealStore.dispatch('updateForwardButton', null);

                    Object.keys(data.buttons).forEach(key => {
                        let button = {};

                        switch (key){
                            case 'saveDeliveryInfo1':
                                // Роль кнопки
                                // вперед, назад или удалить
                                button.name = 'next';
                                // Имя transition'а, который передаст кнопка
                                button.transition = key;
                                // Нужно ли кнопке игнорировать наличие ошибок
                                button.ignore = false;
                                // Текстовое значение кнопки
                                button.title = data.buttons[key].title;
                                // Алерт, который будет вызван после нажатия кнопки
                                button.alert = {
                                    type: 'success',
                                    text: 'Информация о доставке успешно сохранена'
                                };

                                Nova.dealStore.dispatch('updateForwardButton', button);
                                break;
                            case 'saveDeliveryInfo2':
                                button.name = 'next';
                                button.transition = key;
                                button.ignore = false;
                                button.title = data.buttons[key].title;
                                button.alert = {
                                    type: 'success',
                                    text: 'Информация о доставке успешно сохранена'
                                };

                                Nova.dealStore.dispatch('updateForwardButton', button);
                                break;
                            case 'saveDeliveryInfo3':
                                button.name = 'next';
                                button.transition = key;
                                button.ignore = false;
                                button.title = data.buttons[key].title;
                                button.alert = {
                                    type: 'success',
                                    text: 'Информация о доставке успешно сохранена'
                                };

                                Nova.dealStore.dispatch('updateForwardButton', button);
                                break;
                        }
                    });
                }
            },
            /**
             * Проверяет, все ли значения внутри объекта delivery
             * равны null
             * @param delivery
             * @return {boolean}
             */
            deliveryDataIsNull(delivery){
                if(delivery && this.getType(delivery) === 'object' && Object.keys(delivery).length > 0) {
                    return Object.keys(delivery).every(key => {
                        return delivery[key] === null;
                    });
                }

                return true;
            }
        },

        mounted(){
            if(this.isDeliveryDataEditingPage()) {
                Nova.dealStore.dispatch('changeSelected', true);
                // Пользователь оплатил заказ - обновляем его баланс
                axios.get('/api/get-balance').then(({data}) => {
                    Nova.paymentStore.dispatch('updateBalance', data.balance);
                });

                axios.get(`/api/photobook/delivery_data_editing/${this.id}`).then(({data}) => {
                    /**
                     * Массив компаний
                     * @type {array}
                     **/
                    this.companies = data.companies;

                    if(data.delivery && data.delivery.company) {
                        /**
                         * Выбранная компания
                         * @type {object}
                         **/
                        this.selectedCompany = data.delivery.company;
                    }
                    /**
                     * Способы доставки
                     * @type {object}
                     **/
                    this.deliveryTypes = data.deliveryTypes;
                    /**
                     * Заголовки для способов доставки
                     * @type {object}
                     **/
                    if(data.deliveryTypesTitle) {
                        this.deliveryTypesTitle = data.deliveryTypesTitle;
                    }

                    if(this.selectedCompany && data.delivery.type) {
                        /**
                         * Ищем тип доставки, если он был выбран
                         * @type {object}
                         */
                        this.selectedDeliveryType = this.deliveryTypes[this.selectedCompany.id].types.find(type => {
                            return type.id === data.delivery.type;
                        });
                    }
                    /**
                     * Сумма предоплаты
                     * @type {number}
                     **/
                    this.prepaySum = data.prepay;
                    /**
                     * Адрес
                     * @type {string}
                     */
                    if(data.delivery.address) {
                        this.address = data.delivery.address;
                    }
                    /**
                     * Почтовый индекс
                     * @type {string}
                     */
                    if(data.delivery.post_code) {
                        this.postcode = data.delivery.post_code;
                    }
                    /**
                     * Получатель
                     * @type {string}
                     */
                    if(data.delivery.person) {
                        this.receiver = data.delivery.person;
                    }
                    /**
                     * Паспортные данные получателя
                     * @type {string}
                     */
                    if(data.delivery.person_passport) {
                        this.passportNumber = data.delivery.person_passport.split(' ')[1];
                        this.passportSeries = data.delivery.person_passport.split(' ')[0];
                    }
                    /**
                     * Номер телефона
                     * @type {string}
                     */
                    if(data.delivery.phone) {
                        this.phone = data.delivery.phone;
                    }
                    /**
                     * Регион
                     * @type {string}
                     */
                    if(data.delivery.region) {
                        this.region = data.delivery.region;
                    }
                    /**
                     * @type {array}
                     */
                    this.updateTransitions(data);
                    /**
                     * @type {object}
                     */
                    this.updateButtons(data);
                    /**
                     * @type {array}
                     */
                    this.updateErrors(data, data => {
                        if(data.errors && data.errors.length) {
                            /**
                             * Если ошибки есть - размещаем компонент ошибок
                             * над компонентом кнопок
                             */
                            this.placeErrorsComponent();
                        }
                    });
                    /**
                     * @type number
                     **/
                    this.setAmount(data);
                    /**
                     * @type {string}
                     */
                    this.type = data.type;
                    /**
                     * @type {string}
                     **/
                    this.date = data.date;
                    /**
                     * @type {string|null}
                     */
                    this.updateClientPlace(data);
                    /**
                     * Показывать ли кнопки внутри инфоблока
                     * @type {*|boolean}
                     */
                    this.showInfoBlockButtons = this.deliveryDataIsNull(data.delivery);
                    /**
                     * @type {{}|delivery}
                     */
                    this.delivery = data.delivery;
                });
            } else {
                this.goBack();
            }
        },

        components: {
            Errors,
            Buttons,
            Checkbox,
            Accordion,
            Item
        }
    }
</script>
