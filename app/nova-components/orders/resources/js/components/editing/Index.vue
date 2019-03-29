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
                           :class="`${isEditingPage() ?
                           'bg-blue hover:bg-blue-dark' :
                           'bg-grey-light hover:bg-grey-dark'}
                           flex text-white font-bold py-4 px-4 no-underline text-xs`">Параметры заказа</a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           @click="canGo('edit') ?
                           goTransition('edit') :
                           false">Загрузка файлов</a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           @click="false">Оплата</a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           @click="false">Информация о доставке</a>
                    </li>
                </ul>
            </div>

            <!--Шаг №1-->

            <div class="lg:w-3/4 md:w-full sm:w-full mb-8 lg:pl-2 md:pl-0 sm:pl-0">
                <div class="w-full rounded overflow-hidden shadow-lg" v-if="categories.length > 0">

                    <div v-for="category in categories" v-show="has(allowedToShowCategories, category.name)">
                        <Category :title="category.title"
                                  :lists="lists[category.name]"
                                  :element="category"
                                  :type="category.field ? category.field.type : null"
                                  :value="calculator[category.name]"
                                  :bookType="type"
                                  :bookCategory="bookCategory"
                                  @selectList="selectList"
                                  @toggle="toggle"
                        />
                    </div>

                    <!--Информация-->

                    <div class="flex flex-wrap border-b border-grey-light p-4">
                        <div class="flex justify-start w-full" v-show="errors.length > 0">
                            <p class="mb-4 text-red-dark" v-for="error in errors">
                                {{error.value}}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center" v-bind:class="!(isSample && sample) ? 'w-full' : ''">
                            <div class="lg:w-1/2 md:w-1/2 sm:w-full">
                                <p class="text-sm" v-if="price !== null ">Базовая цена: <b>{{price.base}} руб.</b></p>
                                <p class="text-sm" v-if="price.discount > 0">Цена cо скидкой: <b>{{price.discountPrice}} руб.</b> (скидка {{price.discount}}%)</p>
                                <p class="text-sm" v-if="price.markup !== 1">Дополнительно: <b>+{{Math.round((price.markup-1)*100)}} %</b> (менее 10 экз. выпускных альбомов)</p>
                                <p class="text-sm" v-if="price.markup !== 1">Итого за 1 книгу: <b>{{price.price}} руб.</b></p>
                                <p class="font-normal mt-2" v-if="price !== null">Итого: <b>{{price.total}} руб.</b></p>
                                <p class="text-sm font-normal mt-2">Дата изготовления: {{ date }}</p>
                            </div>
                            <div class="lg:w-1/2 md:w-1/2 sm:w-full" v-if="isSample && sample">
                                <Checkbox
                                    class="w-full"
                                    :active="sample.value"
                                    :text="sample.title"
                                    :element="sample"
                                    @change="checkboxChange"
                                />
                                <p class="text-sm font-normal w-full mt-2" v-if="isSample && sample.help" v-html="sample.help"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    import _ from 'lodash';
    import Category from './CategoryCard';
    import Errors from '../errors/Index';
    import Buttons from '../buttons/Index';
    import userOrdersMixin from '../../mixin';
    import Checkbox from '../custom-checkbox/Index';

    export default {
        name: "order",

        props: [
            'album',
            'root',
            'id'
        ],

        mixins: [userOrdersMixin],

        data(){
            return {
                date: '',
                lists: {},
                calculator: {},

                categories: [],
                allowedToShowCategories: [],

                price: null,
                // Показывать, что это образец или нет
                isSample: false,
                sample: null,
                checkboxActive: false
            }
        },

        methods: {
            /**
             * Принимает объект карточки
             * Скрывает содержимое всех остальных карточек
             * показывает только содержимое переданной карточки
             * @param toggleToOpenCategory
             * @return obj
             */

            toggle(toggleToOpenCategory) {
                // Если категория уже открыта
                // ничего не делаем
                if (toggleToOpenCategory.opened) return;

                this.categories = this.categories.map(category => {
                    if (category === toggleToOpenCategory) {
                        category.opened = true;
                    } else {
                        category.opened = false;
                    }

                    return category;
                }).filter(category => category !== undefined);
            },

            /**
             * Получает аргументом объект текущей открытой карточки
             * Находит следующую после текущей карточки
             * Показывает её содержимое, скрывая содержимое текущей и всех остальных
             * @param current
             */

            toggleNextCard(current) {
                // Отфильтровываем категории так, что бы остались только разрешенные
                let categories = this.categories.filter(category => {
                    return _.has(this.allowedToShowCategories, category.name);
                });
                // Получаем индекс внутри оставшегося массива
                let index = categories.indexOf(current);

                if (index !== -1 && (index + 1) < Object.keys(this.allowedToShowCategories).length) {
                    // Получаем следующий объект
                    let next = categories.find((category, i) => {
                        return i === (index + 1);
                    });
                    // Если найден - открываем его
                    // Скрывая все остальные
                    if (next) {
                        this.toggle(next);
                    }
                }
            },
            /**
             * Параметром получает объект из списка карты
             * Возвращает объект родительской карты
             * @param list
             * @return {*}
             */
            getCurrentCard(list) {
                return this.categories.find(category => {
                    return category.name === list.category;
                });
            },
            /**
             * Параметром получает объект из списка карты
             * в калькулятор записывает обновленное значение и передает его
             * происходит обновление карт и списков
             * после чего следующая карта открывается после текущей
             * @param list
             */
            selectList(list) {
                let calculator = this.calculator;

                calculator[list.category] = list.value;

                axios.post(`/api/photobook/edit/${this.id}`, {
                    _token: this.csrf_token,
                    id: this.id,
                    calculator
                }).then(({data}) => {
                    // Если у пользователя есть доступ к данной сделке
                    // Допускаем его
                    if (data.access) {

                        if (!this.isUrlMatchToRoute(data.url)) {
                            this.goTransition('backToEditing');
                        }

                        /**
                         * @type array
                         * @var categories
                         **/
                        let categories = this.categories;
                        /**
                         * @type number
                         **/
                        this.setAmount(data);
                        /**
                         * @type object
                         * @var allowedToShowCategories
                         **/
                        this.allowedToShowCategories = data.allowed;
                        /**
                         * @type object
                         * @var lists
                         **/
                        let lists = {};
                        /**
                         * Перезаписываем категории
                         **/
                        this.categories = Object.keys(data.all).map(key => {
                            if (key === 'type' || key === 'category' || key === 'isSample') return;

                            let oldCategory = categories.find(category => category.name === key);

                            let category = data.all[key];

                            if (oldCategory) {
                                category.opened = oldCategory.opened;
                                category.name = key;
                            } else {
                                category.opened = false;
                                category.name = key;
                            }

                            return category;
                        }).filter(category => category !== undefined);
                        /**
                         * Заполняем переменную lists
                         */
                        Object.keys(data.lists).forEach(key => {

                            if(key === 'category' || key === 'type' || key === 'isSample') {
                                return;
                            }

                            let value = data.lists[key];
                            // Например, значением может быть объект, число или даже null
                            if (!_.isArray(value)) {
                                // Если значение - объект с ключами min и max - то это слайдер
                                // см. ./CategoryCard.vue:30
                                if (_.isObject(value) && !_.isArray(value) && (this.has(value, 'min') && this.has(value, 'max'))) {
                                    lists[key] = [{
                                        category: key,
                                        slider: true,
                                        value
                                    }];
                                } else {
                                    // Иначе это обычный элемент списка, со значением, равным value
                                    lists[key] = [{
                                        category: key,
                                        value,
                                        selected: data.calculator[key] === value
                                    }];
                                }
                            } else {
                                // Т.к. в качестве значений списков возвращается массив строк
                                // Преобразуем массив строк в массив объектов
                                // ['hello', 'world'] => [{value: hello, ...}, {value: 'world', ...}]
                                value = value.map(value => {
                                    return {
                                        category: key,
                                        value,
                                        selected: data.calculator[key] === value
                                    }
                                });

                                lists[key] = value;
                            }
                        });
                        /**
                         * @type {object}
                         */
                        this.lists = lists;
                        /**
                         * @type {object}
                         */
                        this.calculator = data.calculator;
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
                         * @type {Array}
                         */
                        this.updateTransitions(data);
                        /**
                         * @type {Object}
                         */
                        this.updateButtons(data);
                        /**
                         * @type {String}
                         **/
                        this.date = data.date;
                        /**
                         * @type {object}
                         * */
                        if(data.deal){
                            /**
                             * @type {number}
                             * */
                            this.price = data.deal.price;
                        }
                        if(data.sample) {
                            this.sample = data.all.isSample;
                            this.sample.value = data.calculator.isSample;
                            this.isSample = true;
                        }

                        /**
                         * Получаем карточку переданного объекта списка
                         * Открываем следующую карточку после текущей
                         **/
                        if (!list.setDefaultValue) {
                            this.toggleNextCard(
                                this.getCurrentCard(list)
                            )
                        }

                    } else { // Иначе, редиректим на главную страницу ЛК
                        this.goBack()
                    }
                });
            },
            /**
             * Проверяет, соответствует ли адресная строка указаному шаблону
             **/
            isEditingPage() {
                return window.location.pathname.match(/printphotobook\/editing\/\d+/g);
            },
            /**
             * Совпадает ли переданный url маршруту
             * @param url
             * @return {array|null}
             **/
            isUrlMatchToRoute(url) {
                return url ? url.match(/printphotobook\/editing\/\d+/g) : false;
            },
            /**
             * Обновляет состояние кнопок
             * @param data
             */
            updateButtons(data){
                if (data.buttons) {
                    Nova.dealStore.dispatch('updateBackwardButton', null);
                    Nova.dealStore.dispatch('updateDeleteButton', null);
                    Nova.dealStore.dispatch('updateForwardButton', null);

                    Object.keys(data.buttons).forEach(key => {
                        let button = {};

                        switch (key) {
                            case 'delete':
                                // Роль кнопки
                                // вперед, назад или удалить
                                button.name = 'delete';
                                // Имя transition'а, который передаст кнопка
                                button.transition = key;
                                // Нужно ли кнопке игнорировать наличие ошибок
                                button.ignore = true;
                                // Текстовое значение кнопки
                                button.title = data.buttons[key].title;

                                Nova.dealStore.dispatch('updateDeleteButton', button);
                                break;
                            case 'edit':
                                button.name = 'next';
                                button.transition = key;
                                button.ignore = false;
                                button.title = data.buttons[key].title;

                                Nova.dealStore.dispatch('updateForwardButton', button);
                                break;
                        }
                    });
                }
            },

            checkboxChange(sample, active){
                sample.value = !active;
                sample.setDefaultValue = true;
                sample.category = 'isSample';

                this.selectList(sample);
            }
        },

        mounted(){
            if(this.isEditingPage()) {
                Nova.dealStore.dispatch('changeSelected', true);

                axios.get(`/api/photobook/edit/${this.id}`).then(({data}) => {
                    // Если у ползователя есть доступ к данной сделке
                    // Работаем дальше
                    if(data.access) {

                        if(!this.isUrlMatchToRoute(data.url)) {
                            this.goTo(
                                this.getRoute( data.url )
                            );
                        }
                        /**
                         * @type {*|number}
                         */
                        this.setAmount(data);
                        /**
                         * @type {*|Object}
                         */
                        this.allowedToShowCategories = data.allowed;
                        /**
                         * @type {{}}
                         */
                        let lists = {};
                        /**
                         * @type {any[]}
                         */
                        this.categories = Object.keys(data.all).map(key => {

                            if (key === 'type' || key === 'category' || key === 'isSample') return;

                            let category = data.all[key];

                            category.opened = false;
                            category.name = key;

                            return category;

                        }).filter(object => object !== undefined);
                        // открываем Формат
                        this.toggle( this.categories[0] );

                        /**
                         * Заполняем переменную lists
                         */
                        Object.keys(data.lists).forEach(key => {

                            if(key === 'category' || key === 'type' || key === 'isSample') {
                                return;
                            }

                            let value = data.lists[key];
                            // Например, значением может быть объект, число или даже null
                            if(!_.isArray(value)){
                                // Если значение - объект с ключами min и max - то это слайдер
                                // см. ./CategoryCard.vue:24
                                if(_.isObject(value) && !_.isArray(value) && (this.has(value, 'min') && this.has(value, 'max'))) {

                                    lists[key] = [{
                                        category: key,
                                        slider: true,
                                        value
                                    }];

                                    return;

                                } else {
                                    // Иначе это обычный элемент списка, со значением, равным value
                                    lists[key] = [{
                                        category: key,
                                        value,
                                        selected: data.calculator[key] === value
                                    }];

                                    return;
                                }
                            } else {
                                // Т.к. в качестве значений списков возвращается массив строк
                                // Преобразуем массив строк в массив объектов
                                // ['hello', 'world'] => [{value: hello, ...}, {value: 'world', ...}]
                                value = value.map(value => {
                                    return {
                                        category: key,
                                        value,
                                        selected: data.calculator[key] === value
                                    }
                                });

                                lists[key] = value;

                                return;
                            }
                        });
                        /**
                         * @type {object}
                         */
                        this.lists = lists;
                        /**
                         * @type {object}
                         */
                        this.calculator = data.calculator;
                        /**
                         * @type {array}
                         */
                        this.updateErrors(data, data => {
                            if(data.errors.length){
                                /**
                                 * Если ошибки есть - размещаем компонент ошибок
                                 * над компонентом кнопок
                                 */
                                this.placeErrorsComponent();
                            }
                        });
                        /**
                         * @type {Array}
                         */
                        this.updateTransitions(data);
                        /**
                         * @type {Object}
                         */
                        this.updateButtons(data);
                        /**
                         * @type {string}
                         */
                        this.type = data.type;
                        /**
                         * @type {string}
                         **/
                        this.bookCategory = data.category;
                        /**
                         * @type {string}
                         **/
                        this.date = data.date;
                        /**
                         * @type {object}
                         * */
                        if(data.deal){
                            /**
                             * @type {number}
                             * */
                            this.price = data.deal.price;
                        }
                        /**
                         * @type {string|null}
                         */
                        this.updateClientPlace(data);

                        if(data.sample) {
                            this.sample = data.all.isSample;
                            this.sample.value = data.calculator.isSample;
                            this.isSample = true;
                        }

                    } else { // Если доступа нет - возвращаем на главную страницу ЛК
                        this.goBack();
                    }
                });
            }
        },

        components: {
            Category,
            Errors,
            Buttons,
            Checkbox
        }
    }
</script>
