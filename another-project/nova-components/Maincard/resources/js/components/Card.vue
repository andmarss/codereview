<template>
    <loading-view :loading="loading">

        <card class="w-full mb-4" v-if="user && user.returned_to_register">
            <div class="bg-red-lightest border border-red-light text-red-dark px-4 py-3 rounded relative" role="alert">
                <p><strong class="font-bold">Внимание! Модератор отклонил Ваше подтверждение регистрации</strong></p>
                <p>Причина:</p>
                <p class="block sm:inline">{{user.cause}}</p>
            </div>
        </card>

        <card class="flex items-center justify-center">
            <div class="p-3 w-full" v-if="user && !user.confirmed">
                <div class="mb-4">
                    <div class="bg-orange-lightest border-l-4 border-orange text-orange-dark p-4" role="alert">
                        <p class="font-bold">Осталось немного :)</p>
                        <p>Для завершения регистрации загрузите копию Ваших документов формата .jpeg весом не более 5 МБ</p>
                    </div>
                    <div class="flex mt-4 justify-end">
                        <label class="w-64 bg-grey hover:bg-grey-dark text-grey-darkest font-normal py-2 px-4 rounded flex flex-wrap cursor-pointer items-center"
                               for="archive"
                               ref="fileLabel"
                               @dragenter.prevent="dragenterButton"
                               @dragover.prevent="dragenterButton"
                               @dragleave.prevent="dragleaveButton"
                               @drop.prevent="dropButton"
                        >
                            <span class="lg:w-1/4 inline-block align-middle text-sm" v-if="!load"><i class="fas fa-download"></i></span>
                            <span class="lg:w-3/4 inline-block align-middle text-sm pl-4" v-if="!load">Загрузить документ</span>
                            <loader v-if="load" width="30"></loader>
                            <input class="hidden" id="archive" type="file" @change="uploadArchive" >
                        </label>
                    </div>
                </div>
                <div v-if="user && user.files && user.files.length > 0">
                    <ul class="list-reset">
                        <li v-for="file in user.files">
                            <div class="relative text-center justify-center py-3">
                                <span class="absolute pin-t pin-r p-3 text-white
                                bg-red-dark hover:bg-red-darker cursor-pointer rounded-full text-base remove-file"
                                      @click.prevent="showModal(file)"
                                >
                                    <i class="fas fa-times"></i>
                                </span>
                                <ImageModal
                                    :src="`${root}/${file.path}${file.name}`"
                                    :alt="file.old_name"
                                />
                                <p class="text-center">{{file.old_name}}</p>
                            </div>
                        </li>
                    </ul>
                    <div class="flex mt-3 px-4 py-3 justify-end">
                        <button class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-4 rounded"
                        @click.prevent="goTransition(
                        TRANSITION_AUTH_CONFIRM,
                        'auth',
                        'Поздравляем! Вы успешно подтвердили свою регистрацию')"
                        >
                            Завершить регистрацию
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="user && user.confirmed">
                <p class="text-base py-4">Вы успешно подтвердили свою регистрацию!</p>
            </div>
        </card>
        <transition name="fade">
            <Modal
                ref="modal"
                :title="'Удаление файла'"
                :question="`Вы действительно хотите удалить файл ${selectedFile ? selectedFile.old_name : ''}?`"
                :working="working"
                :active="openModal"
                :cancelButtonText="'Нет'"
                :confirmButtonText="'Удалить файл'"
                @confirm="confirm"
                @close="closeModal"
            />
        </transition>
    </loading-view>
</template>

<script>
    import axios from 'axios';
    import mainMixin from '../mixin';
    import ImageModal from '../../../../Userslist/resources/js/components/ImageModal';

export default {
    props: [
        'maincard',
    ],

    mixins: [mainMixin],

    data(){
        return {
            load: false,

            user: null,

            root: window.location.origin,
            openModal: false,
            selectedFile: null,

            loading: true
        }
    },

    methods: {
        /**
         * Обработчик, который сробатывает, когда мышка с перетаскиваемым файлом нависли над элементом
         * */
        dragenterButton(){
            let label = this.$refs.fileLabel;

            if(label) {
                // меняем фоновый цвет с серого на темно серый
                label.classList.remove('bg-grey');
                label.classList.add('bg-grey-dark');
            }
        },
        /**
         * Обработчик, сробатывающий, когда мышь выходит за пределы родителя
         * */
        dragleaveButton(){
            let label = this.$refs.fileLabel;
            // меняем фоновый цвет с темно серого на серый
            if(label) {
                label.classList.remove('bg-grey-dark');
                label.classList.add('bg-grey');
            }
        },
        /**
         * Обработчик, сробатывающий, когда пользователь отпускает левую кнопку мыши над элементом
         * Тем самым отправляя перетаскиваемый файл в обработчик
         * @param {MouseEvent} e
         */
        dropButton(e){
            let label = this.$refs.fileLabel;

            if(label) {
                let file = e.dataTransfer.files[0];
                // показываем loader на кнопке
                this.load = true;
                // показываем overlay
                // скрываем ... в оверлее
                // показываем прогресс загрузки
                this.hideDotsShowProgress();
                /**
                 * @type FormData
                 **/
                let fd = new FormData();

                fd.append('file', file);
                fd.append('_token', this.csrf_token);
                fd.append('type', 'archive');

                this.upload(fd).then(() => {
                    // меняем фоновый цвет с темно серого на серый
                    this.dragleaveButton();
                });
            }
        },
        /**
         * Обработчик события change, отвечающий за загрузку архива
         * @param e
         */
        uploadArchive(e){
            // показываем loader на кнопке
            this.load = true;
            // показываем overlay
            // скрываем ... в оверлее
            // показываем прогресс загрузки
            this.hideDotsShowProgress();
            /**
             * @type FormData
             **/
            let fd = new FormData();

            fd.append('file', e.target.files[0]);
            fd.append('_token', this.csrf_token);
            fd.append('type', 'archive');

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
             * Внутри устанавливаем заголовок для успешной загрузки архивов и файлов
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
                `/user/${this.user.id}/file_uploading`,
                formData,
                config
            ).then(({data}) => {
                if(data.access){
                    if(data.success) {
                        /**
                         * @type {Object}
                         */
                        this.user = data.user;
                        /**
                         * @type {Array}
                         */
                        this.updateTransitions(this.user);

                    } else {
                        toastr.error(data.error.message);
                    }
                    // скрываем лоадер в кнопке загрузки архива
                    this.load = false;
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

        showModal(file){
            this.selectedFile = file;

            this.openModal = true;
        },

        closeModal(){
            this.selectedFile = null;

            this.openModal = false;
        },

        confirm(){
            let selectedFile = this.selectedFile;

            this.closeModal();

            axios.post(`/user/${this.user.id}/file/${selectedFile.id}/remove`).then(({data}) => {
                if(data.success) {
                    /**
                     * @type {object}
                     * */
                    this.user = data.user;
                    /**
                     * @type {Array}
                     */
                    this.updateTransitions(this.user);

                    toastr.success(`Файл ${selectedFile.old_name} успешно удален.`);
                } else {
                    toastr.error(`Произошла ошибка при удалении файла. Файл не был удален.`);
                }
            }).catch(() => {
                toastr.error('Произошла ошибка при удалении файла. Файл не был удален.');
            })
        },

        goTransition(transition, workflow, message){
            if(this.transitions.includes(transition)) {
                axios.post('/transition', {
                    _token: this.csrf_token,
                    transition,
                    workflow
                }).then(({data}) => {
                    if(data.success) {
                        toastr.success(message);
                    }
                    /**
                     * @type {object}
                     * */
                    this.user = data.user;
                    /**
                     * @type {Array}
                     */
                    this.updateTransitions(this.user);
                })
            }
        }
    },

    mounted() {
        this.root = window.location.origin;

        axios.get(`/user-data`).then(({data}) => {
            /**
             * @type {object}
             * */
            this.user = data.user;
            /**
             * @type {array}
             */
            this.updateTransitions(this.user);

            this.loading = false;
        });
    },

    computed: {
        showDots(){
            return Nova.mainStore.getters.showDots;
        },

        showProgress(){
            return Nova.mainStore.getters.showProgress;
        },

        transitions(){
            return Nova.mainStore.getters.transitions;
        }
    },

    components: {
        ImageModal
    }
}
</script>

<style scoped lang="scss">
    .remove-file {
        line-height: 0;
        border-radius: 50%;
        padding: 6px 8px;
        font-size: 10px;
    }
</style>
