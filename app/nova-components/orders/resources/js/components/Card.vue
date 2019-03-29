<template>
    <div class="w-full max-w-xl">
        <div class="w-full flex flex-wrap items-center">
            <heading class="md:w-full w-1/4 md:mb-3" v-if="!showForm">Мои заказы</heading>

            <div class="bg-40 px-6 py-3" v-if="!showForm && deals.length > 0">
                <div class="flex">
                    <button class="btn btn-default btn-primary rounded-full" @click.prevent="showForm = true">
                        Новый заказ
                    </button>
                </div>
            </div>
        </div>

        <div v-if="!showForm && deals.length > 0">
            <heading :level="3" class="mb-3">В работе</heading>

            <div class="flex flex-wrap -mx-2 justify-start items-stretch mb-3 items-stretch">

                <div class="flex lg:w-1/3 md:w-1/2 sm:w-full px-2 md:mb-3 sm:mb-3" v-for="deal in deals">

                    <div class="shadow-lg rounded overflow-hidden">

                        <div class="w-full relative flex items-center justify-center deal-image-wrapper" style="min-height: 6rem">

                            <img class="w-full h-auto" :src="deal.avatar" />

                        </div>
                        <div class="w-full flex flex-wrap px-6 py-3">

                            <div class="font-bold text-xl mb-2 items-center w-full">Заказ №{{ deal.id }}</div>
                            <div class="text-sm items-center w-full">{{ deal.date }}</div>

                        </div>

                        <div class="px-6 py-4 flex justify-center items-center">
                            <a href="javascript:void(0);"
                               @click.prevent="continueDeal(deal)"
                               class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-4 rounded-full no-underline text-center">
                                {{deal.title}}
                            </a>
                            <a href="javascript:void(0);"
                               class="bg-transparent hover:bg-red text-red font-semibold hover:text-white py-2 px-4 border border-red hover:border-transparent rounded-full ml-2"
                               @click.prevent="modalOpen(deal)"
                               style="height: 34px"
                               v-if="deal.canDelete"
                            >
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </div>

        <div v-if="!showForm && deals.length === 0" class="mt-6">
            <heading :level="1" class="flex justify-center mb-6">Список ваших заказов еще пуст</heading>
            <p class="flex justify-center">
                <a href="javascript:void(0)" @click.prevent="showForm = true" class="no-underline btn btn-default btn-primary rounded-full">Сделайте первый заказ</a>
            </p>
        </div>

        <div v-if="showForm">
            <div class="bg-40 mb-6 py-3">
                <div class="flex justify-start items-center ml-auto mb-4">
                    &#8592;&nbsp;&nbsp;<!-- <-\s\s -->
                    <a href="javascript:void(0);"
                       class="text-black"
                       @click.prevent="showForm = false">Мои заказы</a>
                    &nbsp;&nbsp;/&nbsp;&nbsp;<!-- \s\s/\s\s -->
                    <heading :level="1" class="font-semibold">Новый заказ</heading>
                </div>
            </div>

            <div class="bg-orange-lightest border-l-4 border-orange text-orange-dark p-4 text-sm mb-10"
                 role="alert">
                <p><b>Уважаемые клиенты!</b></p>
                <p>В данный момент через личный кабинет Вы можете самостоятельно создавать заказы и загружать макеты на все виды фотокниг с <b>ФОТООБЛОЖКАМИ</b>.</p>
                <p>Если вы хотите заказать фотокнигу с обложкой из ткани, кожзама, бархата или бумвинила, присылайте такие заказы на почту <a href="mailto:info@funfotobook.ru" class="text-base text-orange-dark hover:text-red-darker">info@funfotobook.ru</a></p>
                <p>Скоро заказы и на эти книги можно будет создавать в личном кабинете. Мы уже работаем над этим.</p>
            </div>

            <div v-for="(types, category) in categories">
                <newOrder :header="`${category}`"
                          :types="types"
                          :category="category"
                          :typesInfo="typesInfo"
                          @createAlbum="createAlbum"
                />
            </div>
        </div>

        <router-view></router-view>

        <transition name="fade">
            <Modal
                ref="modal"
                :title="'Удаление заказа'"
                :question="`Вы действительно хотите удалить заказ №${dealId}?`"
                :working="working"
                :active="showModal"
                :cancelButtonText="'Нет'"
                :confirmButtonText="'Удалить заказ'"
                @confirm="confirm"
                @close="modalClose"
            />
        </transition>
    </div>
</template>

<script>
    import newOrder from './NewOrder';
    import Modal from './modal/Index';
    import axios from 'axios';

    export default {
        props: [
            'card'
        ],

        data(){
            return {
                showForm: false,
                select: 0,
                categories: [],
                deals: [],
                urls: {},
                album: null,
                typesInfo: null,
                showModal: false,
                working: false,
                csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                dealId: null
            }
        },

        mounted() {
            axios.get('/printphotobook').then(({data}) => {
                this.categories = data.categories;
                this.typesInfo = data.typesInfo;
            });

            this.getDeals();
        },

        methods: {
            /**
             * @param {object} album
             **/
            createAlbum(album){
                this.album = album;
            },

            dropAlbum(){
                this.album = null;
            },

            continueDeal(deal){
                this.goTo(
                    {
                        route: this.getRoute(this.urls[ deal.id ]),
                        id: deal.id
                    }
                );
            },

            /**
             * редиректит пользователя на переданный маршрут, если текущее состояние того позволяет
             * @param route
             * @param id
             */
            goTo({route, id}){
                this.$router.push({name: route, params: {id}});
            },

            /**
             * Возвращает имя маршрута, соответствующего ссылке
             * Если ссылка не совпадает - в любом случае вернет маршрут dashboard
             * Который ведет на главную страницу
             * @param url
             * @return {string}
             */
            getRoute(url){
                if(url.match(/printphotobook\/editing\/\d+/g)) {
                    return 'editing';
                } else if (url.match(/printphotobook\/file_uploading\/\d+/g)) {
                    return 'file-upload';
                } else if (url.match(/printphotobook\/paying\/\d+/g)) {
                    return 'pay';
                } else if (url.match(/printphotobook\/delivery_data_editing\/\d+/g)) {
                    return 'delivery'
                } else if (url.match(/printphotobook\/info\/\d+/g)) {
                    return 'info';
                } else {
                    return 'dashboard'
                }
            },
            /**
             * закрывает модальное окно
             */
            modalClose(){
                this.showModal = false;

                this.dealId = null;
            },
            /**
             * открывает модальное окно
             * @param deal
             */
            modalOpen(deal){
                this.showModal = true;

                this.dealId = deal.id;
            },
            /**
             * Если пользователь нажимает "Удалить заказ"
             * Отправляет transition "delete" для удаления заказа
             * После чего показывает уведомление об успешном удалении заказа
             * Либо уведомление об ошибке - если она произошла
             * и закрывает модальное окно
             */
            confirm(){
                if(this.dealId) {
                    this.working = true;

                    axios.post(`/deal/transition/${this.dealId}`, {
                        _token: this.csrf_token,
                        transition: 'delete'
                    }).then(() => {
                        toastr.success(`Заказ № ${this.dealId} успешно удален`);

                        this.modalClose();

                        this.dealId = null;

                        this.getDeals();

                        this.working = false;
                    }).catch(() => {
                        this.modalClose();

                        this.working = false;

                        toastr.error('При выполнении запроса произошла ошибка');
                    });
                }
            },

            getDeals(){
                axios.get('/api/get-deals').then(({data}) => {
                    this.deals = data.deals;
                    this.urls = data.urls;
                });
            }
        },

        components: {
            newOrder,
            Modal
        }
    }
</script>

<style scoped lang="scss">
    .deal-image-wrapper {
        height: 214px;
        overflow-y: hidden;
    }

    @media screen and (max-width: 992px) {
        .deal-image-wrapper {
            height: 304px;
        }
    }

    @media screen and (max-width: 768px) {
        .deal-image-wrapper {
            height: 456px;
        }
    }

    @media screen and (max-width: 576px) {
        .deal-image-wrapper {
            height: 214px;
        }
    }
</style>
