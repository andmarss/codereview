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

            <div class="lg:w-1/4 md:w-full sm:w-full">
                <ul class="flex list-reset flex-wrap mb-6">
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           @click.prevent="">Параметры заказа</a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           @click.prevent="canGo('backToFileUpload', {ignore: true}) ? goTransition('backToFileUpload') : false">Загрузка файлов</a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           :class="`flex  ${isPayingPage() ?
                           'bg-blue hover:bg-blue-dark text-white font-bold py-4 px-4 no-underline text-xs' :
                           ''} text-white font-bold py-4 px-4 no-underline text-xs`"
                           @click.prevent="">Оплата</a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           @click.prevent="canGo('pay') ? goTransition('pay') : false">Информация о доставке</a>
                    </li>
                </ul>
            </div>

        <!--Шаг №3-->

            <div class="lg:w-3/4 md:w-full sm:w-full lg:pl-2 md:pl-0 sm:pl-0">
                <heading :level="3" class="flex font-semibold">Оплата заказа</heading>

                <div class="bg-orange-lightest border-l-4 border-orange text-orange-dark p-4 text-sm my-6" role="alert">
                    <p class="font-bold mb-2">Внимание!</p>
                    <p class="mb-2">После оплаты заказа он будет направлен в печать. С этого момента внести изменения в макеты и параметры заказа будет невозможно. Пожалуйста, еще раз внимательно проверьте все макеты. которые Вы загрузили.</p>
                    <p>После оплаты заказа изменения в макетах не принимаются.</p>
                </div>

                <p class="flex items-center">
                    Сумма заказа <b class="inline-block mr-4">&nbsp;{{cost}} руб.</b>
                    <a href="javascript:void(0)" class="bg-blue text-white font-bold py-2 px-2 rounded-full no-underline"
                       v-bind:class="canGo('pay') ?
                        'hover:bg-blue-dark' :
                         'cursor-not-allowed opacity-50'"
                       @click.prevent="goTransition('pay')" v-if="errors.length === 0">Оплатить заказ</a>
                    <router-link :to="{name: 'payment-form'}"
                                 class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-2 rounded-full no-underline "
                                v-else>
                        Пополнить баланс
                    </router-link>

                </p>

                <div class="flex justify-start w-full mt-8" v-show="errors.length > 0">
                    <p class="text-red-dark" v-for="error in errors">
                        {{error.value}}
                    </p>
                </div>

                <Accordion :items="items" class="w-full rounded overflow-hidden shadow-lg my-8">
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
                            <div v-if="item.role === 'info'" v-for="content in item.content">
                                <p v-for="(value, key) in content"
                                   v-if="getType(value) === 'string' || getType(value) === 'number'"
                                   class="mb-4"
                                >
                                    <b>{{key}}:</b>&nbsp;&nbsp;{{value}}
                                </p>
                            </div>

                            <div v-else-if="item.role === 'layouts'">
                                <div class="w-full flex flex-wrap mt-12 -mx-2">
                                    <heading :level="3" class="flex font-semibold w-full mb-4 mx-2" v-if="content.name">Книга {{upperCaseFirst(content.name)}}</heading>
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

                            <div v-if="item.role === 'url'" class="mb-5">
                                <a :href="item.content"
                                   target="_blank"
                                   class="text-base font-bold text-blue p-3 no-underline hover:underline">
                                    {{item.content}}
                                </a>
                            </div>
                        </div>
                    </Item>
                </Accordion>
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
    import Errors from '../errors/Index';
    import Buttons from '../buttons/Index';
    import Accordion from '../accordion/Index';
    import Item from '../accordion/Item';
    import userOrdersMixin from '../../mixin';

    export default {
        name: "Index.vue",

        props: ['id'],

        mixins: [userOrdersMixin],

        data(){
            return {
                routePattern: /printphotobook\/paying\/\d+/ig,

                items: []
            }
        },

        methods: {
            isPayingPage(){
                return window.location.pathname.match(this.routePattern);
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
                            case 'backToFileUpload':
                                // Роль кнопки
                                // вперед, назад или удалить
                                button.name = 'back';
                                // Имя transition'а, который передаст кнопка
                                button.transition = key;
                                // Нужно ли кнопке игнорировать наличие ошибок
                                button.ignore = true;
                                // Текстовое значение кнопки
                                button.title = data.buttons[key].title;
                                Nova.dealStore.dispatch('updateBackwardButton', button);
                                break;
                            case 'delete':
                                button.name = 'delete';
                                button.transition = key;
                                button.ignore = true;
                                button.title = data.buttons[key].title;
                                Nova.dealStore.dispatch('updateDeleteButton', button);
                                break;
                            case 'pay':
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
            /**
             * @param data
             * @type {object}
             * */
            updateAccordionItems(data){
                let oldItems = this.items;
                let deal = data.deal;
                let loadedUrl = !!(deal.params.files && deal.params.files.link && deal.params.files.link.length > 0);

                this.items = [];

                this.items.push(
                    this.addAccordeonItem(data, {
                        title: 'Информация о заказе',
                        role: 'info',
                        active: oldItems.length > 0 ? oldItems[0].active : false,
                        callback(data, item){
                            item.show = true;

                            Object.keys(data.info).forEach(key => {
                                let content = {};

                                content[key] = data.info[key];

                                item.content.push(content);
                            });

                            return item;
                        }
                    })
                );

                this.items.push(
                    this.addAccordeonItem(data, {
                        title: `${loadedUrl ? `Ссылка на макеты` : `Макеты`}`,
                        role: loadedUrl ? 'url' : 'layouts',
                        active: oldItems.length > 0 ? oldItems[0].active : false,
                        callback(data, item){
                            item.show = true;

                            if(data.books && data.books.length && !loadedUrl) {
                                // Если у всех книг отсутствуют макеты - скрываем элемент
                                let books = data.books.map((book, index) => {
                                    if(book.files && book.files.length > 0) {
                                        book.files = book.files.map(file => {
                                            file.id = `img_${file.name}`;

                                            return file;
                                        });
                                    }
                                    // номер книги
                                    book.number = (index+1);

                                    item.content.push(book);

                                    return book;
                                });

                                let bookHasFiles = books.some(book => {
                                    return book.files && book.files.length > 0;
                                });

                                if(!bookHasFiles) {
                                    item.show = false;
                                }
                                // Есть ли у книг хотя бы один файл НЕ со статусом null или 0
                                let filesWithStatusNotNull = books.filter(book => {
                                    return book.files && book.files.length > 0;
                                }).some(book => {
                                    return book.files.filter(file => {
                                        return file.status !== null && file.status !== 0
                                    }).length > 0;
                                });

                                if(filesWithStatusNotNull) {
                                    item.active = true;
                                } else {
                                    item.active = false;
                                }
                            } else if (loadedUrl) { // если была загружена ссылка
                                item.show = true;
                                item.role = 'url';
                                item.content = deal.params.files.link;
                            } else { // если книги отсутствуют - скрываем элемент
                                item.show = false;
                            }

                            return item;
                        }
                    })
                );
            }
        },

        mounted(){
            if(this.isPayingPage()) {
                Nova.dealStore.dispatch('changeSelected', true);

                axios.get(`/api/photobook/paying/${this.id}`).then(({data}) => {
                    if(data.access) {
                        /**
                         * @type number
                         **/
                        this.setAmount(data);
                        /**
                         * @type {Array}
                         */
                        this.updateTransitions(data);
                        /**
                         * @type {Object}
                         */
                        this.updateButtons(data);
                        /**
                         * @type {Array}
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
                        this.updateAccordionItems(data);
                        /**
                         * @type {string}
                         */
                        this.type = data.type;
                        /**
                         * @type {string|null}
                         */
                        this.updateClientPlace(data);

                    } else {
                        this.goBack();
                    }
                });
            }
        },

        components: {
            Errors,
            Buttons,
            Accordion,
            Item
        }
    }
</script>

<style scoped lang="scss">
    .image-container {
        float: left;
        height: 110px;
    }
</style>
