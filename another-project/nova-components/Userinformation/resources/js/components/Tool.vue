<template>
    <loading-view :loading="loading">
        <!--
        Блок будет показан, если данные пользователя загружены, и если на странице модератор
        -->
        <div class="w-full p-3" v-if="user && is_m">
            <div class="flex justify-start items-center ml-auto mb-4">
                <div>
                    &#8592;&nbsp;&nbsp;<!-- <-\s\s -->
                    <a href="javascript:void(0)"
                       class="text-black underline hover:underline"
                       @click.prevent="goUsers">
                        Список пользователей
                    </a>
                    &nbsp;&nbsp;/&nbsp;&nbsp;<!-- \s\s/\s\s -->
                </div>
                <h3 class="font-semibold">Пользователь #{{ id }}</h3>
            </div>
        </div>
        <!--
        Блок будет показан, если:
        1. загружены данные пользователя
        2. на странице НЕ модератор
         -->
        <div class="flex justify-start items-center ml-auto mb-4" v-if="user && !is_m">
            <div v-if="show">
                &#8592;&nbsp;&nbsp;<!-- <-\s\s -->
                <a href="javascript:void(0)"
                   class="text-black underline hover:underline"
                    @click.prevent="show = false">
                    Вернуться к профилю
                </a>
                &nbsp;&nbsp;/&nbsp;&nbsp;<!-- \s\s/\s\s -->
            </div>
            <h3 class="font-semibold">Пользователь #{{ id }}</h3>
        </div>
        <!--
        Блок булет показан, если:
        1. Загружены данные пользователя
        2. Пользователь не в состоянии модерации (@see app/Workflow/AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE)
        3. Пользователь не удален или не подавал заявку на удаление
        -->
        <card class="w-full mb-4" v-show="!show" v-if="user && !user.data_changed && (!user.will_be_deleted || !user.deleted)">
            <div class="bg-orange-lightest border-l-4 border-orange text-orange-dark p-4" role="alert">
                <p><b>Внимание!</b></p>
                <p>Для фото принимаются файлы формата .jpg весом не более 5 МБ</p>
            </div>
        </card>
        <!--
        Блок будет показан, если:
        1. На странице НЕ модератор
        2. Загружены данные пользователя
        3. Пользователя находится в состоянии модерации
        -->
        <card class="w-full mb-4" v-if="!is_m && user && user.data_changed" v-show="!show">
            <div class="bg-blue-lightest border-t border-b border-blue text-blue-dark px-4 py-3" role="alert">
                <p class="font-bold">Идет модерация</p>
                <p class="text-sm">Модератор еще не подтвердил ваши изменения.</p>
                <p class="text-sm">Внесение изменений на странице профиля пока не доступно.</p>
            </div>
        </card>
        <!--
        Блок будет показан, если:
        1. Загружены данные пользователя
        2. Пользователь находится не в состоянии модерации
        3. Профиль пользователя не удален
        -->
        <card class="w-full mb-4" v-if="user && !user.data_changed && user.deleted">
            <div class="bg-red-lightest border border-red-light text-red-dark px-4 py-3 rounded relative" role="alert">
                <!-- Если НЕ модератор -->
                <p v-if="!is_m"><strong class="font-bold">Ваш профиль был удален!</strong></p>
                <!-- Если модератор -->
                <p v-if="is_m"><strong class="font-bold">Профиль пользователя был удален</strong></p>
                <p class="block sm:inline">Изменения профиля больше не доступны</p>
            </div>
        </card>
        <!--
        Блок будет показан, если:
        1. Загружены данные пользователя
        2. Пользователь подал заявку на удаление
        3. Но профиль при этом еще не удален
        -->
        <card class="w-full mb-4" v-if="user && user.will_be_deleted && !user.deleted">
            <div class="bg-red-lightest border border-red-light text-red-dark px-4 py-3 rounded relative" role="alert">
                <p><strong class="font-bold">Профиль отмечен к удалению</strong></p>
            </div>
        </card>
        <!--
        Блок будет показан, если:
        1. Загружены данные пользователя
        2. Пользователь возвращен к изменению данных
        -->
        <card class="w-full mb-4" v-if="user && user.returned_to_change">
            <div class="bg-red-lightest border border-red-light text-red-dark px-4 py-3 rounded relative" role="alert">
                <!-- Если НЕ модератор -->
                <p v-if="!is_m"><strong class="font-bold">Внимание! Модератор отклонил Ваши изменения</strong></p>
                <!-- Если модератор -->
                <p v-if="is_m"><strong class="font-bold">Вы отклонили изменения пользователя</strong></p>
                <p>Причина:</p>
                <p class="block sm:inline">{{user.cause}}</p>
            </div>
        </card>
        <!--
        Блок будет показан, если:
        1. Загружены данные пользователя
        -->
        <card class="w-full" v-if="user">
            <div class="flex flex-wrap w-full items-start" v-show="!show">
                <div class="lg:w-1/3 md:w-full sm:w-full p-3">
                    <div class="flex w-full justify-start mb-4">
                        <img :src="userImage"
                             style="max-width: 150px; height: 150px;"
                             alt="Фото специалиста"
                            id="user-photo"
                        >
                    </div>
                    <!--
                    Блок будет показан, если:
                    1. На странице НЕ модератор
                    -->
                    <div class="flex flex-wrap w-full" v-if="!is_m">
                        <!--
                        Блок будет показан, если:
                        1. На странице НЕ модератор
                        2. Загружены данные пользователя
                        3. Пользователь не находится в состоянии модерации
                        4. Не подавал заявку на удаление профиля
                        5. Не удален
                        -->
                        <div class="w-full mb-3 text-left" v-if="!is_m && user && !user.data_changed && !user.will_be_deleted && !user.deleted">
                            <button
                                class="bg-grey-light hover:bg-grey text-white font-bold py-2 px-4 rounded"
                                @click.prevent="triggerLabel"
                            >Загрузить фото</button>
                        </div>
                        <!--
                        Блок будет показан, если:
                        1. На странице НЕ модератор
                        2. Пользователь не находится в состоянии модерации
                        5. Не удален
                        -->
                        <div class="w-full mb-3 text-left" v-if="!is_m && !user.data_changed">
                            <!--
                            Кнопка будет зеленой, если:
                            1. Пользователь подал заявку на удаление, и не подавал заявку на восстановление
                            ИЛИ
                            2. Профиль удален
                            С текстом "Восстановить профиль"
                            Иначе она будет красной с текстом "Удалить профиль"
                            -->
                            <button :class="(user.will_be_deleted && !user.will_be_restored) || user.deleted
                            ? 'bg-green-light hover:bg-green-dark text-white font-bold py-2 px-4 rounded'
                            : 'bg-red-light hover:bg-red-dark text-white font-bold py-2 px-4 rounded'"
                            @click.prevent="showModal">{{(user.will_be_deleted && !user.will_be_restored) || user.deleted ? 'Восстановить профиль' : 'Удалить профиль'}}</button>
                        </div>
                        <!--
                        Блок будет показан, если:
                        1. На странице НЕ модератор
                        2. Пользователь не в состоянии модерации ИЛИ подал заявку на удаление ИЛИ подал заявку на восстановление
                        -->
                        <div class="w-full mb-3 text-left" v-if="!is_m && (!user.data_changed && !((user.will_be_deleted && !user.will_be_restored) && user.deleted))">
                            <button class="bg-green-light hover:bg-green text-white font-bold py-2 px-4 rounded" @click.prevent="goToModeration">Отправить на модерацию</button>
                        </div>
                    </div>
                    <!--
                    Блок будет показан, если:
                    1. На странице модератор
                    2.1. Пользователь в состоянии модерации
                    2.2. ИЛИ пользователь подал заявку на удаление
                    2.3 ИЛИ пользователь подал заявку на восстановление
                    -->
                    <div class="flex flex-wrap w-full text-left mb-3" v-if="is_m && (user.data_changed || user.will_be_deleted || user.will_be_restored)">
                        <button class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-4 rounded" @click.prevent="moderate">Подтвердить изменения</button>
                    </div>
                    <!--
                    Блок будет показан, если:
                    1. На странице модератор
                    2.1. Пользователь в состоянии модерации
                    2.2. ИЛИ пользователь подал заявку на удаление
                    2.3 ИЛИ пользователь подал заявку на восстановление
                    -->
                    <div class="flex flex-wrap w-full text-left" v-if="is_m && (user.data_changed || user.will_be_deleted || user.will_be_restored)">
                        <button class="bg-red-light hover:bg-red-dark text-white font-bold py-2 px-4 rounded" @click.prevent="showModeratorModal = true">Отменить изменения</button>
                    </div>
                </div>
                <div class="lg:w-2/3 md:w-full sm:w-full p-3">
                    <div class="flex">
                        <div class="flex flex-grow py-3 px-4 justify-start font-bold">{{user.specialist.firstname}}</div>
                        <div class="flex flex-grow py-3 px-4 justify-start font-bold">{{user.specialist.secondname}}</div>
                        <div class="flex flex-grow py-3 px-4 justify-start font-bold">{{user.specialist.lastname}}</div>
                    </div>
                    <!--
                    Блок будет показан, если у специалиста есть описание
                    -->
                    <div class="flex flex-wrap w-full" v-if="user.specialist.description">
                        <p class="w-full text-base justify-start mb-3 p-3" v-html="user.specialist.description"></p>
                        <!--
                        Блок будет показан, если:
                        1. На странице НЕ модератор
                        2. Пользователь не в состоянии модерации
                        3. Польззователь не удален
                        4. Или не подал заявку на удаление, и потом на восстановление
                        -->
                        <div class="flex w-full justify-end p-3" v-if="!is_m && !user.data_changed && !user.will_be_deleted && !user.deleted">
                            <button
                                    class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-4 rounded"
                                    @click.prevent="show = true">Изменить данные</button>
                        </div>
                    </div>
                    <!--
                    Блок будет показан, если:
                    1. На странице НЕ модератор
                    2. У специалиста отсутствует описание
                    -->
                    <div v-if="!user.specialist.description && !is_m" class="flex w-full">
                        <p class="flex w-full text-base justify-start mb-3 p-3">
                            Заполните информацию о себе
                        </p>
                        <div class="flex w-full justify-end p-3">
                            <button
                                class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-4 rounded"
                                @click.prevent="show = true">Заполнить</button>
                        </div>
                    </div>
                    <!--
                    Блок будет показан, если:
                    1. На странице модератор
                    2. У специалиста отсутствует описание
                    -->
                    <div class="flex w-full" v-if="!user.specialist.description && is_m">
                        <p class="flex w-full text-base justify-start p-3">
                            Пользователь еще не заполнил информацию о себе
                        </p>
                    </div>
                </div>
            </div>
            <div class="w-full items-start" v-show="show">
                <div class="bg-orange-lightest border-l-4 border-orange text-orange-dark p-4 text-sm mb-5"
                     role="alert">
                    <p><b>Внимание!</b></p>
                    <p>После того, как внесете необходимые изменения - вернитесь к странице профиля, и нажмите кнопку <b>"Отправить на модерацию"</b></p>
                    <p>Или воспользуйтесь кнопкой <b>"Отправить на модерацию"</b> внизу текущей формы</p>
                </div>
                <TextField
                    :placeholder="'Имя'"
                    :label="'Имя'"
                    :value="user.specialist.firstname"
                    :id="'firstname'"
                    :type="'text'"
                    :required="true"

                    @change="change"
                />
                <TextField
                    :placeholder="'Фамилия'"
                    :label="'Фамилия'"
                    :value="user.specialist.lastname"
                    :id="'lastname'"
                    :type="'text'"
                    :required="true"

                    @change="change"
                />
                <TextField
                    :placeholder="'Отчество'"
                    :label="'Отчество'"
                    :value="user.specialist.secondname"
                    :id="'secondname'"
                    :type="'text'"
                    :required="true"

                    @change="change"
                />
                <TextArea
                    :label="'Описание'"
                    v-model="user.specialist.description"
                    :id="'description'"
                    @change="change"
                />
                <div class="flex border-b border-40">
                    <div class="w-1/5 py-6 px-8">
                        <label class="inline-block text-80 pt-2 leading-tight" for="photo">
                            Загрузите фото
                        </label>
                    </div>
                    <div class="py-6 px-8 w-4/5">
                        <label class="w-64 bg-grey hover:bg-grey-dark text-grey-darkest font-normal py-2 px-4 rounded flex flex-wrap cursor-pointer items-center"
                               for="photo"
                        >
                            <span class="lg:w-1/4 inline-block align-middle text-sm"><i class="fas fa-download"></i></span>
                            <span class="lg:w-3/4 inline-block align-middle text-sm">Загрузить фото</span>
                            <input class="hidden" id="photo" type="file" @change="uploadPhoto" >
                        </label>
                    </div>
                </div>
                <!--
                Блок будет показан, если:
                1. На странице НЕ модератор
                2. Пользователь не в состоянии модерации
                3. Пользователь не удален
                -->
                <div class="w-full mb-3 text-right p-4" v-if="!is_m && !user.data_changed && !user.deleted">
                    <button class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-4 rounded" @click.prevent="save">Сохранить</button>
                </div>
            </div>

            <h2 class="text-black mb-3 w-full px-3"
                v-if="user
                    && user.specialist
                    && user.specialist.reviews
                    && (user.specialist.reviews.length > 0 || user.specialist.reviews.length === 0)"
                v-show="!show">Отзывы:</h2>
            <!--
            Блок будет показан, если:
            1. Загружены данные пользователя
            2. Пользователь - специалист
            3. У специалиста есть отзывы
            -->
            <div class="w-full mt-4 border-2 border-grey-lighter shadow"
                 v-if="user
                    && user.specialist
                    && user.specialist.reviews
                    && user.specialist.reviews.length > 0"
                 v-show="!show">
                <div class="w-full mb-3" v-for="review in user.specialist.reviews">
                    <div class="w-full">
                        <div class="flex flex-wrap w-full">
                            <div class="flex w-1/2 justify-start p-3">
                                <Rate
                                    :rate="review.rate"
                                />
                            </div>
                            <div class="flex w-1/2 justify-end p-3 text-grey">
                                {{review.author}}
                            </div>
                        </div>
                        <p class="flex w-full p-4">
                            {{review.text}}
                        </p>
                        <div class="flex flex-wrap w-full pl-8 pr-4 pb-4 pt-0 review">
                            <div class="w-full mb-2" v-if="review.answer_text">
                                <hr class="w-full border border-grey-lighter mx-0 mt-0 mb-4">
                                <div class="w-full flex text-sm text-grey mb-2">
                                    <div class="mr-2">
                                        {{user.specialist.firstname}} {{user.specialist.lastname}}
                                    </div>
                                    <div>
                                        {{review.date}}
                                    </div>
                                </div>
                                <p class="w-full answer_text">{{review.answer_text}}</p>
                            </div>
                            <div class="w-full">
                                <div class="w-full mb-3">
                                    <textarea class="w-full shadow-inner p-4 border-0 hidden" placeholder="Ответ..." rows="6" :id="`review-${review.id}`">{{review.answer_text}}</textarea>
                                </div>
                                <div class="w-full text-right" v-if="!user.deleted && !user.data_changed && !user.will_be_deleted && !review.answer_text">
                                    <button class="bg-green-light hover:bg-green text-white font-bold py-2 px-4 rounded" @click.prevent="answer(review)">Ответить</button>
                                </div>
                                <div class="w-full text-right" v-if="!user.deleted && !user.data_changed && !user.will_be_deleted && review.answer_text">
                                    <button class="bg-white hover:bg-grey-lightest text-grey-darkest font-semibold py-2 px-4 border border-grey-light rounded shadow" @click.prevent="answer(review)">Редактировать</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <Pagination
                    :elements="user.specialist.reviews"
                    :currentPage="currentPage"
                    :total="total"
                    :perPage="perPage"
                    :buttons="true"
                    :bgColorClass="'bg-transparent'"

                    @changePage="changeSpecialistReviewsPage"
                />
            </div>
            <!--
            Блок будет показан, если:
            1. Загружены данные пользователя
            2. Пользователь - специалист
            3. У специалиста отсутствуют отзывы
            -->
            <div class="w-full mt-4 border-2 border-grey-lighter shadow"
                 v-if="user
                    && user.specialist
                    && user.specialist.reviews
                    && user.specialist.reviews.length === 0"
                 v-show="!show">
                <h3 class="w-full text-center text-grey">Список отзывов пока что пуст</h3>
            </div>
        </card>
        <transition name="fade">
            <Modal
                    ref="modal"
                    :title="modal.title"
                    :question="modal.question"
                    :working="working"
                    :active="openModal"
                    :cancelButtonText="modal.cancelButtonText"
                    :confirmButtonText="modal.confirmButtonText"
                    @confirm="confirm"
                    @close="closeModal"
            />
        </transition>
        <transition name="fade">
            <Modal
                    ref="modal"
                    :title="'Причина отказа'"
                    :question="`Укажите причину отказа`"
                    :working="working"
                    :active="showModeratorModal"
                    :cancelButtonText="'Закрыть'"
                    :confirmButtonText="'Отправить'"

                    :showTextArea="true"
                    :textAreaLabel="`Причина отказа`"
                    :textAreaPlaceholder="`Текст...`"

                    @confirm="rejection"
                    @close="closeModeratorModal"
            />
        </transition>
    </loading-view>
</template>

<script>
    import axios from 'axios';
    import methods from '@/mixin/methods';
    import data from '@/mixin/data';
    import Pagination from '@userslist/components/Pagination';

    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    axios.defaults.headers.post['Content-Type'] = 'multipart/form-data';

export default {
    props: {
        id: Number
    },

    data(){
        return {
            // данные пользователя (объект)
            user: null,
            loading: true,
            // модератор или простой пользователь?
            is_m: false,
            root: null,
            // показывать или скрывать блок редактирования данных
            show: false,
            // ресурс с загруженным изображением
            imgSrc: null,
            // пользователь изменил свои данные на странице?
            data_changed: false,
            // пагинация
            currentPage: 1,
            perPage: 10,
            total: 0,
            // какие свойства пользователя нужно игнорировать при отправке запроса на модерацию
            ignoreKeys: [
                'reviews',
                'id',
                'updated_at',
                'created_at',
                'user_id'
            ],

            working: false,
            openModal: false,
            showModeratorModal: false,

            modal: {
                title: 'Удаление профиля',
                question: `Вы действительно хотите удалить свой профиль?`,
                cancelButtonText: 'Нет',
                confirmButtonText: 'Удалить профиль'
            },

            ...data
        }
    },

    mounted(){
        this.root = window.location.origin;

        this.updateUserPage().then(() => {
            this.loading = false;
        })
    },

    methods: {
        /**
         * Обновляет данные пользователя на странице
         * */
        updateUserPage(){
            return axios.post(`/specialist/information/${this.id}`, {
                page: this.currentPage,
                per_page: this.perPage
            }).then(({data}) => {
                if(data.access) {
                    this.user = data.user;
                    this.is_m = data.is_m;
                    this.total = data.total;
                } else {
                    this.goDashboard();
                }
            })
        },
        /**
         * Обновляет список отзывов согласно номеру страницы
         *
         * @param page
         * */
        changeSpecialistReviewsPage(page){
            this.currentPage = page;

            this.loading = true;

            return axios.post(`/specialist/reviews/${this.id}`, {
                page: this.currentPage,
                per_page: this.perPage
            }).then(({data}) => {
                if(data.access) {
                    this.user.specialist.reviews = data.reviews;
                    this.total = data.total;
                } else {
                    this.goDashboard();
                }
            }).then(() => {
                this.loading = false;
            }).catch(() => {
                this.loading = false;
            })
        },
        /**
         * обработчик события change у полей
         *
         * @param field
         * @param value
         */
        change(field, value){
            this.user.specialist[field] = value;
            this.data_changed = true;
        },
        /**
         * триггерит label файлового инпута
         */
        triggerLabel(){
            document.querySelector('[for="photo"]').click();
        },
        /**
         * Пользователь нажа "Отправить на модерацию"
         */
        goToModeration(){
            if(this.user.data_changed) {
                toastr.error('Ваш профиль находится в состоянии модерации. Дождитесь подтверждения модератора');
                return;
            }

            /**
             * @type {FormData}
             */
            let fd = new FormData();

            Object.keys(this.user.specialist).forEach(key => {
                // если свойство для отправки не обязательно
                // игнорируем его
                if(this.ignoreKeys.includes(key)) return;

                fd.append(key, this.user.specialist[key]);
            });

            fd.append('page', this.currentPage);
            fd.append('per_page', this.perPage);

            axios.post(`/specialist/${this.user.id}/moderation`, fd).then(({data}) => {
                if(data.access && data.success && !data.error) {

                    this.updateUser(data.user);
                    this.total = data.total;

                    toastr.success(`Ваши данные успешно переданы на модерацию.
                    Дождитесь подтверждения модератора, после чего сможете вносить новые изменения`);
                } else if (data.access && !data.success && data.error && data.error.message) {
                    toastr.error(data.error.message);
                } else {
                    this.goDashboard();
                }
            }).then(() => {
                // скрываем форму изменения данных
                this.show = false;
                this.loading = false;
            }).catch(error => {
                this.loading = false;
                console.error(error.response.data);
                console.error(error.response.status);
                console.error(error.response.headers);
            });

            this.imgSrc = null;
        },
        /**
         * Обработчик события, когда пользователь нажал под отзывом "Ответить"
         * @param review
         */
        answer(review){
            if(this.is_m) return;

            let textarea = document.querySelector(`#review-${review.id}`);
            let parent = textarea.closest('.review');
            let answer = parent.querySelector('.answer_text');

            if(answer) {
                answer.classList.add('hidden');
            }

            if(textarea.classList.contains('hidden')) {
                textarea.classList.remove('hidden'); return;
            }

            if(textarea.value.length === 0) {
                toastr.error('Ответ на отзыв не может быть пустым'); return;
            }
            /**
             * @type {Promise<AxiosResponse<any>>|undefined}
             * */
            let promise = this.answerReview(review, textarea.value);

            if(promise) {

                promise.then(({data}) => {
                    if(data.access && data.success) {
                        /**
                         * @type {array}
                         * */
                        this.user.specialist.reviews = data.reviews;
                        /**
                         * @type {number}
                         * */
                        this.total = data.total;
                    } else if (data.access && !data.success && data.error && data.error.message) {
                        toastr.error(data.error.message);
                    } else {
                        this.goDashboard();
                    }

                    textarea.classList.add('hidden');

                    if(answer) {
                        answer.classList.remove('hidden');
                    }
                }).then(() => {
                    this.loading = false;
                }).catch(error => {
                    this.loading = false;
                    console.error(error.response.data);
                    console.error(error.response.status);
                    console.error(error.response.headers);
                });

            }
        },
        /**
         * @param review {object}
         * @param answer {string}
         * @return {Promise<AxiosResponse<any>>|undefined}
         * */
        answerReview(review, answer){
            if(review && answer) {
                this.loading = true;

                return axios.post(`/specialist/${this.user.id}/review/${review.id}/answer`, {
                    answer,
                    page: this.currentPage,
                    per_page: this.perPage
                });
            }
        },
        /**
         * Обработчик клика по кнопке "Подтвердить изменения" для модератора
         * */
        moderate(){
            if(!this.is_m) return;

            this.loading = true;

            axios.post(`/api/moderate`, {
                id: this.user.id,
                transition: this.TRANSITION_AUTH_DATA_CHANGE_CONFIRM,
                workflow: 'auth'
            }).then(({data}) => {
                if(data.access && data.success && data.user) {
                    this.user = data.user;
                    toastr.success('Изменения пользователя успешно приняты');
                } else if (data.access && !data.success && data.error && data.error.message) {
                    toastr.error(data.error.message);
                }

                this.loading = false;

                this.goUsers();
            }).catch(() => {
                this.loading = false;

                this.goUsers();
            })
        },
        /**
         * @property user
         * @type {object}
         * */
        updateUser(user) {
            if(user) {
                Object.keys(user).forEach(userProperty => {
                    let userValue = user[userProperty];

                    if(userProperty === 'specialist') {
                        let specialist = userValue;

                        Object.keys(specialist).forEach(specialistProperty => {
                            // т.к. обновление v-model внутри ckeditor вызывает подвисание
                            // игнорируем обновление этого свойства у пользователя
                            if(specialistProperty === 'description') return;

                            this.user[userProperty][specialistProperty] = specialist[specialistProperty];
                        });

                        return;
                    }

                    this.user[userProperty] = userValue;
                });
            }
        },
        /**
         * Пользователь нажал "Сохранить"
         */
        save(){
            if(this.is_m) return;

            /**
             * @type {FormData}
             */
            let fd = new FormData();

            Object.keys(this.user.specialist).forEach(key => {
                // если свойство для отправки не обязательно
                // игнорируем его
                if(this.ignoreKeys.includes(key)) return;

                fd.append(key, this.user.specialist[key]);
            });

            this.loading = true;

            axios.post(`/specialist/${this.user.id}/update`, fd).then(({data}) => {
                if(data.access && data.success) {
                    this.updateUser(data.user);
                } else if (data.access && !data.success) {
                    toastr.error(data.error.message);
                } else {
                    this.goDashboard();
                }
            }).then(() => {
                this.loading = false;
                this.show = false;
            }).catch(error => {
                this.loading = false;
                this.show = false;

                console.error(error)
            })
        },
        /**
         * Обработчик события change, отвечающий за загрузку файла
         * @param e
         */
        uploadPhoto(e){
            // показываем overlay
            // скрываем ... в оверлее
            // показываем прогресс загрузки
            this.hideDotsShowProgress();
            /**
             * @type FormData
             **/
            let fd = new FormData();

            fd.append('image', e.target.files[0]);

            this.upload(fd);
        },
        /**
         * Обработчик, передающий объект formData на сервер
         * @param formData
         * @return {Promise<AxiosResponse<any>>}
         */
        upload(formData){
            /**
             * Конфиг загрузки
             * Внутри устанавливаем заголовок для успешной загрузки файлов
             * @var {object} config
             **/
            let config = {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            };

            if(this.showProgress) {
                /**
                 * обработчик прогресса загрузки
                 **/
                config['onUploadProgress'] = event => {
                    // сколько загружено (байт)
                    let loaded = parseFloat(event.loaded);
                    // общий размер загрузки (байт)
                    let total = parseFloat(event.total);
                    // сколько загружено в МБ
                    let loadedMb = parseFloat((loaded / 1024) / 1024).toFixed(2);
                    // общий размер загрузки в МБ
                    let totalMb = parseFloat((total / 1024) / 1024).toFixed(2);

                    let content = `Загружено ${loadedMb} MB из ${totalMb} MB`;
                    // устанавливаем контент внутри прогресса загрузки файла
                    this.setContentProgress(content);
                };
            }

            return axios.post(
                `/specialist/${this.user.id}/upload-photo`,
                formData,
                config
            ).then(({data}) => {
                if(data.access){
                    if(data.success) {
                        /**
                         * @type {Object}
                         */
                        this.updateUser(data.user);

                    } else {
                        toastr.error(data.error.message);
                    }
                    // скрываем оверлей
                    this.hideOverlay();
                }
            }).catch(() => {
                // скрываем оверлей
                this.hideOverlay();
            });
        },
        hideDotsShowProgress(){
            // показываем overlay
            this.showOverlay(true);
            // Текст, который будет показан в центре оверлея
            this.setContentOverlay('Идет загрузка');
            // скрываем ... в оверлее
            this.showOrHideDots(false);
            // показываем прогресс
            this.showOrHideProgress(true);
        },
        hideOverlay(){
            // скрываем оверлей
            this.showOverlay(false);
            // сбрасываем текст внутри оверлея
            this.setContentOverlay('');
            // выставляем значение отображения ... по умолчанию
            this.showOrHideDots(true);
            // скрываем прогресс
            this.showOrHideProgress(false);
            // сбрасываем текст внутри прогресса
            this.setContentProgress('');
        },
        /**
         * Показывает или скрывает оверлей
         * @param show
         */
        showOverlay(show){
            Nova.mainStore.dispatch('updateShowOverlay', show);
        },
        /**
         * Устанавливает контент для оверлея
         * @param {string} content
         */
        setContentOverlay(content){
            Nova.mainStore.dispatch('updateOverlayContent', content);
        },
        /**
         * Показывает или скрывает ... в оверлее
         * @param {boolean} show
         * */
        showOrHideDots(show){
            Nova.mainStore.dispatch('updateShowDots', show);
        },
        /**
         * Устанавливает контент для прогресса
         * @param {string} content
         */
        setContentProgress(content){
            Nova.mainStore.dispatch('updateProgressContent', content);
        },
        /**
         * Показывает или скрывает прогресс загрузки в оверлее
         * @param {boolean} show
         */
        showOrHideProgress(show){
            Nova.mainStore.dispatch('updateShowProgress', show);
        },
        showModal(){
            if(this.user.will_be_deleted || this.user.deleted) {
                this.modal = {
                    title: 'Восстановление профиля',
                    question: `Вы действительно хотите восстановить свой профиль?`,
                    cancelButtonText: 'Нет',
                    confirmButtonText: 'Восстановить профиль'
                }
            } else {
                this.modal = {
                    title: 'Удаление профиля',
                    question: `Вы действительно хотите удалить свой профиль?`,
                    cancelButtonText: 'Нет',
                    confirmButtonText: 'Удалить профиль'
                }
            }

            this.openModal = true;
        },
        /**
         * Скрыть модальное окно подтверждения
         * */
        closeModal(){
            this.working = false;

            this.openModal = false;
        },
        /**
         * подтверждение удаления профиля
         */
        confirm() {
            this.working = true;

            if(this.is_m) {
                this.closeModal();
                return;
            }
            /**
             * @var deleteProfile
             * @type {Boolean}
             * */
            let deleteProfile = !((this.user.will_be_deleted && !this.user.will_be_restored) || this.user.deleted);

            this.loading = true;

            axios.post(`/specialist/${this.user.id}/profile/action`, {
                delete: deleteProfile
            }).then(({data}) => {
                if(data.access) {
                    if(data.success) {
                        /**
                         * @type {Object}
                         */
                        this.updateUser(data.user);

                        if(deleteProfile) {
                            toastr.success('Теперь Ваш профиль находится на стадии удаления.');
                        } else {
                            toastr.success('Ваш профиль находится на стадии восстановления.');
                        }
                    } else {
                        toastr.error(data.error.message);
                    }
                } else {
                    this.goDashboard();
                }
            }).then(() => {
                this.loading = false;
                this.closeModal();
            }).catch(error => {
                console.error(error);

                this.loading = false;

                this.closeModal();
            });
        },
        /**
         * @var {string} cause
         * */
        rejection(cause) {
            this.working = true;

            if (!this.is_m) {
                this.closeModeratorModal();
                return;
            }

            this.loading = true;

            axios.post(`/api/specialist/${this.user.id}/reject`, {
                transition: this.TRANSITION_AUTH_BACK_TO_CHANGE,
                cause
            }).then(({data}) => {
                if (data.access && data.success) {
                    this.updateUser(data.user);

                    toastr.success('Отмена изменений пользователя успешно выполнена.');
                } else if (data.access && !data.success) {
                    toastr.error(data.error.message);
                } else {
                    this.goDashboard();
                }
            }).then(() => {
                this.closeModeratorModal();

                this.loading = false;

                this.goUsers();
            }).catch(error => {
                this.closeModeratorModal();

                this.loading = false;

                console.error(error);
            });
        },

        closeModeratorModal(){
            this.working = false;

            this.showModeratorModal = false;
        },
        /**
         * @return {Boolean}
         **/
        isUrl(url){
            return /^ht{2}ps?:\/\/[^\.]+\.\w{2,5}\/.*/.test(url)
        },

        ...methods
    },

    computed: {
        /**
         * Возвращает строковое значение пути к фото пользователя
         * @return {string}
         */
        userImage(){
            let user = this.user;

            return user && user.specialist.image && this.isUrl(user.specialist.image)
                ? user.specialist.image : `${this.root}/${user.specialist.image}`;
        }
    },

    components: {
        Pagination
    }
}
</script>

<style scoped lang="scss">
    .shadow-inner {
        box-shadow: inset 0 0 3px rgba(0,0,0,.2);

        &:focus {
            box-shadow: inset 0 0 6px rgba(0,0,0,.4);
            outline: none;
        }
    }
</style>