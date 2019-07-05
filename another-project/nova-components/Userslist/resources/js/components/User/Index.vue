<template>
    <loading-view :loading="loading">
        <card class="flex flex-wrap items-center justify-center">
            <div class="w-full p-3">
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
                <div class="px-2 py-4" v-if="user">
                    <p class="font-semibold mb-3">Имя: {{user.name}}</p>
                    <p class="font-semibold">Email: {{user.email}}</p>
                </div>
            </div>
            <div class="p-3 w-full">
                <h4 class="font-semibold mb-4">Загруженные файлы пользователя</h4>
                <div v-if="user && user.filesLoaded > 0">
                    <ul class="list-reset">
                        <li v-for="file in user.files">
                            <div class="relative text-center justify-center py-3">
                                <ImageModal
                                    :src="`${root}/${file.path}${file.name}`"
                                    :alt="file.old_name"
                                />
                                <p class="text-center">{{file.old_name}}</p>
                            </div>
                        </li>
                    </ul>
                    <div class="flex mt-3 px-4 py-3 justify-end" v-if="user && !user.moderator_confirmed">
                        <button class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-4 rounded"
                                @click.prevent="goTransition(
                                    TRANSITION_AUTH_MODERATOR_CONFIRM,
                                    'auth')"
                        >
                            Подтвердить регистрацию
                        </button>
                    </div>
                    <div class="flex mt-3 px-4 py-3 justify-end" v-if="user && !user.moderator_confirmed">
                        <button class="bg-red-light hover:bg-red-dark text-white font-bold py-2 px-4 rounded"
                                @click.prevent="showModeratorModal = true"
                        >
                            Возврат на подтверждение регистрации
                        </button>
                    </div>
                </div>
            </div>
        </card>
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
    import UserMixin from '@/mixin/index';

    export default {
        props: ['id'],

        mixins: [UserMixin],

        data(){
            return {
                root: null,
                perPage: 10,
                total: 0,
                fields: {},
                // пользователь
                user: null,

                loading: true,

                working: false,
                showModeratorModal: false
            }
        },

        mounted(){
            this.root = window.location.origin;
            // обновляем информацию о пользователе
            this.updateUserPage();
        },

        methods: {
            /**
             * Обновляет информацию о пользователе
             * */
            updateUserPage(){
                axios.post(`/api/user/${this.id}`, {
                    _token: this.csrf_token
                }).then(({data}) => {
                    if(data.access) {
                        this.user = data.user;
                    }

                    this.loading = false;
                })
            },
            /**
             * Переводит пользователя в переданное состояние
             * @param {string} transition
             * @param {string} workflow
             */
            goTransition(transition, workflow){
                axios.post('/api/transition', {
                    _token: this.csrf_token,
                    transition,
                    workflow,
                    id: this.user.id
                }).then(({data}) => {
                    if(data.access && data.success){
                        toastr.success('Регистрация пользователя успешно подтверждена');

                        this.user = data.user;
                    } else {
                        this.goUsers();
                    }
                })
            },
            /**
             * @var {string} cause
             * */
            rejection(cause) {
                this.working = true;

                axios.post(`/api/specialist/${this.user.id}/reject`, {
                    transition: this.TRANSITION_AUTH_BACK_TO_REGISTER,
                    cause
                }).then(({data}) => {
                    if (data.access && data.success) {
                        this.user = data.user;

                        toastr.success('Отмена подтверждения регистрации пользователя успешно выполнена.');
                    } else if (data.access && !data.success) {
                        toastr.error(data.error.message);
                    } else {
                        this.goUsers();
                    }
                }).then(() => {
                    this.closeModeratorModal();

                    this.goUsers();
                }).catch(error => {
                    this.closeModeratorModal();

                    console.error(error);
                });
            },

            closeModeratorModal(){
                this.working = false;

                this.showModeratorModal = false;
            },
        }
    }
</script>

<style scoped lang="scss">
    .flex-grow-1 {
        flex-grow: 1;
    }

    .flex-grow-3 {
        flex-grow: 3;
    }
</style>
