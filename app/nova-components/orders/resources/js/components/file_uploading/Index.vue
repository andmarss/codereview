<template>
    <div v-if="isSelected" class="relative">

        <!--Шаг №2-->

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
                           @click="canGo('backToEditing', {ignore: true}) ? goTransition('backToEditing') : false">
                            Параметры заказа
                        </a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           :class="`${isFileUploadingPage()
                           ? 'bg-blue hover:bg-blue-dark'
                           : 'bg-grey-light hover:bg-grey-dark'} flex text-white font-bold py-4 px-4 no-underline text-xs`">
                            Загрузка файлов
                        </a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           @click.prevent="canGo('fileUpload') ? goTransition('fileUpload') : false">
                            Оплата
                        </a>
                    </li>
                    <li class="w-full p-2">
                        <a href="javascript:void(0)"
                           class="flex bg-grey-light hover:bg-grey-dark text-white font-bold py-4 px-4 no-underline text-xs"
                           @click="false">
                            Информация о доставке
                        </a>
                    </li>
                </ul>
            </div>

            <div class="lg:w-3/4 md:w-full sm:w-full mb-8 lg:pl-2 md:pl-0 sm:pl-0">


                <!--Информация по загрузке изображений-->

                <Accordion :items="items" class="w-full rounded overflow-hidden shadow-lg" v-if="items.length > 0">

                    <AccordionItem
                        slot="accordion"
                        slot-scope="{item, selectItem}"
                        :item="item"
                    >
                        <div slot="header" @click="selectItem(item)" v-if="item.show">
                            <div class="border-b border-grey-light p-4 cursor-pointer">
                                <p class="font-bold text-sm">{{item.title}}</p>
                            </div>
                        </div>

                        <div slot="content" v-if="item.active" class="p-4 bg-grey-lightest" >

                            <div v-if="item.role === 'variant-info' || item.role === 'info'">

                                <div class="flex flex-wrap w-full mb-8">

                                    <div v-if="item.role === 'info'">
                                        <div class="w-full mb-8 md:w-full sm:w-full font-normal">
                                            <p class="mb-4">
                                                Создайте папку с названием альбома, его типа, формата, название школы и ваше ФИО
                                            </p>
                                            <p><em>Например "Премиум 21х30, 3 разворота, 25 экз. д/с "Солнышко", Иванов"</em></p>
                                            <p>&nbsp;</p>
                                            <p>
                                                Сохраните макеты архивом, загрузите на любой файлообменник (Yandex.Диск, Google.Диск, Облако.Mail.ru и т.д.) и скопируйте ссылку для скачивания макетов в поле ниже.
                                            </p>
                                        </div>
                                    </div>
                                    <div v-if="item.role === 'variant-info'">
                                        <div class="w-full mb-8 md:w-full sm:w-full font-normal">
                                            <p class="mb-4">
                                                Создайте папку с названием альбома, его типа, формата, название школы и ваше ФИО
                                            </p>
                                            <p><em>Например "Премиум 21х30, 3 разворота, 25 экз. д/с "Солнышко", Иванов"</em></p>
                                            <p>Если разворот общий, вы присылаете 1 файл с названием "1 общий разворот", "обложка общая" и т.д.</p>
                                            <p>Если он индивидуальный, то создаете папку "2 индивидуальный разворот" и файлы внутри него называете "2 разворот_Иванов", "2 разворот_Петров" и т.д.
                                                и так все составляющие альбома.</p>
                                            <p>&nbsp;</p>
                                            <p>
                                                Сохраните макеты архивом, загрузите на любой файлообменник (Yandex.Диск, Google.Диск, Облако.Mail.ru и т.д.) и скопируйте ссылку для скачивания макетов в поле ниже.
                                            </p>
                                        </div>
                                    </div>

                                    <!--Блок с кнопкой загрузки и предупреждением-->

                                    <div class="w-full md:w-full sm:w-full">
                                        <div class="w-full">

                                            <input
                                                    class="mr-2 bg-white py-3 px-2 focus:outline-none
                                                    form-input form-input-bordered shadow-inner"
                                                    style="width: 270px"
                                                    placeholder="Ссылка для скачивания макетов"
                                                    type="text"
                                                    v-model="link"
                                            >
                                            <button @click.prevent="sendLink"
                                                    class="bg-blue hover:bg-blue-dark text-white
                                                    font-bold py-2 px-4 rounded-full pl-4">
                                                Сохранить ссылку
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="item.role === 'layouts' && item.show">

                                <!--Блок загрузки изображений-->

                                <div class="w-full flex flex-wrap mt-4 -mx-2"
                                     v-for="book in item.content"
                                     v-if="item.content.length > 0 && book.files.length > 0">

                                    <heading :level="3" class="flex font-semibold w-full mb-4 mx-2" v-if="book.name">Книга {{upperCaseFirst(book.name)}}</heading>

                                    <div>
                                        <div class="mb-8 mx-2 image-container file-container"
                                             v-for="(file) in book.files"
                                             :style="`width: ${110 * file.sizeRate}px;`"
                                             ref="files"
                                             @dragenter.stop.prevent="dragenterFile($event, file)"
                                             @dragover.stop.prevent="dragenterFile($event, file)"
                                             @dragleave.stop.prevent="dragleaveFile($event, file)"
                                             @drop.stop.prevent="dropFile($event, file)"
                                        >

                                            <div v-bind:class="`${file.url ? 'bg-transparent' : 'bg-grey'}`">
                                                <div class="file-container-overlay"></div>
                                                <div class="relative w-full flex justify-center items-center">

                                                    <label class="bg-blue-dark opacity-75 absolute pin-none w-8 h-8 rounded-full
                                                 flex items-center justify-center top-50-percent left-50-percent file-container__label"
                                                           v-bind:class="file.url ? '' : 'mt-12'"
                                                           :for="file.id"
                                                    ><svg class="fill-current w-4 h-4 text-white " xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/></svg>
                                                        <input class="hidden" :id="file.id" type="file" :ref="file.id" :name="file.name" @change="uploadFile($event, file)" >
                                                    </label>

                                                    <img
                                                        :src="file.url"
                                                        :alt="file.title"
                                                        v-show="file.url"
                                                        :style="`width: ${110 * file.sizeRate}px; height: 110px;`"
                                                    />

                                                </div>
                                                <p class="text-center">{{file.title}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </AccordionItem>

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
    import userOrdersMixin from '../../mixin';
    import Accordion from '../accordion/Index';
    import AccordionItem from '../accordion/Item';

    export default {
        name: "Index",

        props: ['id'],

        mixins: [userOrdersMixin],

        data(){
            return {
                books: [],

                calculator: {},

                archiveLoaded: false,
                archiveLoadedErrors: false,
                load: false,

                root: window.location.origin,

                individualPagesNum: 0,
                filesLoaded: 0,

                items: [],

                forzacNahzacInstructionsTypes: ['Фотокнига Стандарт', 'Выпускной альбом Стандарт'],
                instructionsShow: false
            }
        },

        methods: {
            isFileUploadingPage(){
                return window.location.pathname.match(/printphotobook\/file_uploading\/\d+/g);
            },

            isUrlMatchToRoute(url){
                return url.match(/printphotobook\/file_uploading\/\d+/g);
            },
            /**
             * Обработчик, который сробатывает, когда мышка с перетаскиваемым файлом нависли над элементом
             * @param {MouseEvent} e
             * @param {object} file
             * */
            dragenterFile(e, file){
                // получаем элемент кнопки (label)
                let label = document.querySelector(`[for="${file.id}"]`);
                // его родитель - div
                let parent = label.closest('.file-container');
                // оверлей, который будет показываться, пока мышка нависает над элементом
                let overlay = parent.querySelector('.file-container-overlay');
                // показываем оверлей
                overlay.style.display = 'block';
            },
            /**
             * Обработчик, сробатывающий, когда мышь выходит за пределы родителя
             * @param {MouseEvent} e
             * @param {object} file
             * */
            dragleaveFile(e, file){
                // получаем элемент кнопки (label)
                let label = document.querySelector(`[for="${file.id}"]`);
                // его родитель - div.file-container
                let parent = label.closest('.file-container');
                // Получаем координаты родителя
                let {left, top, right, bottom} = this.getElementCoordinates(parent);
                // до тех пор, пока мышка находится над элементом - оверлей не исчезнет
                // это условие нужно для того, что бы не было сробатывания dragleaveFile на дочерних элементах
                // и не было мигания оверлея
                // если курсор левее левого края элемента .file-container
                // или правее правого края
                // или выше верхнего края
                // или ниже нижнего края - оверлей будет скрыт
                let condition = e.clientX < left || e.clientX > right || e.clientY < top || e.clientY > bottom;

                if(!condition) return;

                let overlay = parent.querySelector('.file-container-overlay');
                // если мышка все таки вышла за пределы родителя - скрываем оверлей
                overlay.style.display = '';
            },
            /**
             * Обработчик, сробатывающий, когда пользователь отпускает левую кнопку мыши над элементом
             * Тем самым отправляя перетаскиваемый файл в обработчик
             * @param {MouseEvent} e
             * @param {object} file
             * */
            dropFile(e, file){
                this.needUpdateBooks = false;
                /**
                 * Если файл передан - отправляем его
                 **/
                if(file) {
                    let sendingFile = e.dataTransfer.files[0];
                    // показываем overlay
                    // скрываем ... в оверлее
                    // показываем прогресс загрузки
                    this.hideDotsShowProgress();

                    /**
                     * @type FormData
                     **/
                    let fd = new FormData();

                    fd.append('file', sendingFile);
                    fd.append('_token', this.csrf_token);
                    fd.append('type', 'file');
                    fd.append('name', file.name);

                    this.upload(fd).then(() => {
                        let label = document.querySelector(`[for="${file.id}"]`);
                        let parent = label.closest('.file-container');
                        let overlay = parent.querySelector('.file-container-overlay');
                        overlay.style.display = '';
                    });
                }
            },
            /**
             * Функция для загрузки изображений
             * @param {MouseEvent} e
             * @param {object} sendingFile
             */
            uploadFile(e, sendingFile){
                this.needUpdateBooks = false;
                /**
                 * Если файл передан - отправляем его
                 **/
                if(sendingFile) {
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
                    fd.append('type', 'file');
                    fd.append('name', sendingFile.name);

                    this.upload(fd).then(() => {
                        // Сбрасываем значение input'а
                        // Что бы можно было повторно загрузить тот же файл
                        e.target.value = '';
                    });
                }
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
                    `/printphotobook/file_uploading/${this.id}`,
                    formData,
                    config
                ).then(({data}) => {
                    if(data.access){
                        /**
                         * @type {*|number}
                         */
                        this.individualPagesNum = data.individualPagesNum;
                        /**
                         * @type {*|number}
                         */
                        this.filesLoaded = data.filesLoaded;
                        /**
                         * @type {array}
                         */
                        this.updateErrors(data, data => {
                            if(data.errors && data.errors.length > 0) {
                                // Если ошибки есть
                                // размещаем компонент ошибок
                                // над компонентом кнопок
                                this.placeErrorsComponent();
                            }
                        });
                        /**
                         * Обновляем список книг, что бы подгрузить соответствующие изменения
                         * @type {array}
                         */
                        this.updateBooks(data);
                        /**
                         * @type {Object}
                         */
                        this.updateButtons(data);
                        /**
                         * @type number
                         **/
                        this.setAmount(data);
                        /**
                         * @type {Array}
                         */
                        this.updateTransitions(data);
                        /**
                         * обновляем аккоредон
                         */
                        this.updateAccordionItems(data);
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
            /**
             * Возвращает точные данные по координатам переданного элемента
             * @param {HTMLElement} element
             * */
            getElementCoordinates(element) {
                let box = element.getBoundingClientRect();

                let body = document.body;
                let docEl = document.documentElement;
                // Считаем прокрутку страницы
                let scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
                let scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;
                // Смещение относительно левого верхнего угла
                let clientTop = docEl.clientTop || body.clientTop || 0;
                let clientLeft = docEl.clientLeft || body.clientLeft || 0;
                // Добавляем прокрутку к координатам окна, и вычитаем смещение
                let top = box.top + scrollTop - clientTop;
                let left = box.left + scrollLeft - clientLeft;
                let right = left + element.offsetWidth;
                let bottom = top + element.offsetHeight;

                return {left, top, right, bottom};
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
             * Показывает уведомление об успешной загрузке архива
             * через 5 сек. исчезает
             */
            showLoadArchiveSuccess(){
                this.archiveLoaded = true;

                setTimeout(() => {
                    this.archiveLoaded = false;
                }, 5000)
            },
            /**
             * Показывает уведомление об ошибках при загрузке архива
             * через 5 сек. исчезает
             */
            showLoadArchiveError(){
                this.archiveLoadedErrors = true;

                setTimeout(() => {
                    this.archiveLoadedErrors = false;
                }, 5000)
            },
            /**
             * Обновляет книги
             * @param data
             **/
            updateBooks(data){
                if(data.books && data.books.length > 0) {
                    this.books = data.books.map((book, index) => {
                        if(book.files.length > 0) {
                            book.files = book.files.map(file => {
                                file.id = `img_${file.name}`;
                                // Если статус === STATUS_UPLOADED (см. Models/DealFile.php:40)
                                // То список нужно обновить через 10 сек.
                                if(file.status === 1){
                                    this.needUpdateBooks = true;
                                }

                                return file;
                            });
                        }

                        book.number = (index+1);

                        return book;
                    });
                } else {
                    this.books = [];
                }
                // Если список книг нужно обновить - обновляем его
                if(this.needUpdateBooks) {
                    // функция, которая будет вызываться каждый раз после обновления списка книг
                    let after = function () {
                        // получаем второй элемент аккордеона
                        let item = this.items[1];
                        // обновленные книги
                        let books = this.books;
                        // присваеваем элементу список книг
                        item.content = books;
                        // должен ли быть виден элемент
                        item.show = (this.individualPagesNum === 0 || this.filesLoaded > 0);
                        // есть ли у книг файлы
                        let booksHasFiles = books.some(book => {
                            return book.files && book.files.length > 0;
                        });
                        // если ни у одной книги файлов нет - скрываем элемент
                        if(!booksHasFiles) {
                            item.show = false;
                        }
                        // присваиваем обновленный элемент аккордеона
                        this.items[1] = item;
                    };
                    // привязываем её к компоненту file_uploading
                    this.setUpdateBooksTimeout(after.bind(this));
                }
            },
            /**
             * Обновляет состояние кнопок
             * @param data
             **/
            updateButtons(data){
                if(data.buttons) {
                    Nova.dealStore.dispatch('updateBackwardButton', null);
                    Nova.dealStore.dispatch('updateDeleteButton', null);
                    Nova.dealStore.dispatch('updateForwardButton', null);

                    Object.keys(data.buttons).forEach(key => {
                        let button = {};

                        switch (key){
                            case 'backToEditing':
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
                            case 'fileUpload':
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
                let individualPagesNum = this.individualPagesNum;
                let filesLoaded = this.filesLoaded;
                let deal = this.deal;
                let loadedUrl = !!(deal.params.files && deal.params.files.link && deal.params.files.link.length > 0);

                this.items = [];

                this.items.push(
                    this.addAccordeonItem(data, {
                        title: (individualPagesNum > 0 && filesLoaded === 0)
                            ? 'Ссылка для скачивания макетов книги'
                            : 'Вариант 1. Ссылка для скачивания макетов книги',
                        role: individualPagesNum > 0
                            ? 'variant-info'
                            : 'info',
                        content: '',
                        active: loadedUrl
                            ? true
                            : oldItems.length
                            ? oldItems[0].active
                            : individualPagesNum === 0 || filesLoaded > 0
                                ? false
                                : individualPagesNum > 0 && filesLoaded === 0,
                        callback(data, item){
                            item.show = true;

                            return item;
                        }
                    })
                );

                this.items.push(
                    this.addAccordeonItem(data, {
                        title: `Вариант 2. Загрузить макеты ${data.single ? `страниц` : `разворотов`}`,
                        role: 'layouts',
                        active: oldItems.length
                            ? oldItems[1].active
                            : individualPagesNum === 0 || filesLoaded > 0,
                        callback(data, item){
                            item.show = individualPagesNum === 0 || filesLoaded > 0;

                            if(data.books && data.books.length) {
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
                            } else { // если книги отсутствуют - скрываем элемент
                                item.show = false;
                            }

                            return item;
                        }
                    })
                );
            },
            /**
             * Функция, запускаемая при инициализации страницы
             */
            setFileUploadingPageData(){
                this.needUpdateBooks = false;

                axios.get(`/api/photobook/file_uploading/${this.id}`).then(({data}) => {
                    // Если у ползователя есть доступ к данной сделке
                    // Работаем дальше
                    if(data.access) {
                        // Если ссылка не совпадает с маршрутом
                        // Меняем состояние сделки на соответствующее маршруту
                        if(!this.isUrlMatchToRoute(data.url)) {
                            this.goTransition('backToEditing');
                        }
                        /**
                         * @type {object}
                         * */
                        this.deal = data.deal;
                        /**
                         * @type {boolean}
                         * */
                        this.urlLoaded = !!(this.deal.params.files && this.deal.params.files.link && this.deal.params.files.link.length > 0);
                        /**
                         * @type {*|number}
                         */
                        this.individualPagesNum = data.individualPagesNum;
                        /**
                         * @type {*|number}
                         */
                        this.filesLoaded = data.filesLoaded;
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
                         * @type {array}
                         * */
                        this.updateBooks(data);
                        /**
                         * @type {Object}
                         */
                        this.updateButtons(data);
                        /**
                         * @type {Array}
                         */
                        this.updateTransitions(data);
                        /**
                         * @type {number}
                         */
                        this.setAmount(data);
                        /**
                         * @type {string}
                         */
                        this.type = data.type;
                        /**
                         * @type {string|null}
                         */
                        this.updateClientPlace(data);
                        /**
                         * обновляем аккоредон
                         */
                        this.updateAccordionItems(data);
                        // если тип фотокниги Фотокнига Стандарт или Выпускной альбом Стандарт
                        // показываем инструкции для форзацев и нахзацев
                        if(this.forzacNahzacInstructionsTypes.includes(data.type)) {
                            this.instructionsShow = true;
                        }
                        // если была сохранена ссылка
                        // передаем её в input
                        if(this.urlLoaded) {
                            this.link = this.deal.params.files.link;
                        }

                    } else { // Если доступа нет - возвращаем на главную страницу ЛК
                        this.goBack();
                    }
                });
            },
            /**
             * Показывает или скрывает оверлей
             * @param show
             */
            showOverlay(show){
                Nova.dealStore.dispatch('updateShowOverlay', show);
            },
            /**
             * Устанавливает контент для оверлея
             * @param {string} content
             */
            setContentOverlay(content){
                Nova.dealStore.dispatch('updateOverlayContent', content);
            },
            /**
             * Показывает или скрывает ... в оверлее
             * @param {boolean} show
             * */
            showOrHideDots(show){
                Nova.dealStore.dispatch('updateShowDots', show);
            },
            /**
             * Устанавливает контент для прогресса
             * @param {string} content
             */
            setContentProgress(content){
                Nova.dealStore.dispatch('updateProgressContent', content);
            },
            /**
             * Показывает или скрывает прогресс загрузки в оверлее
             * @param {boolean} show
             */
            showOrHideProgress(show){
                Nova.dealStore.dispatch('updateShowProgress', show);
            },
            /**
             *
             */
            sendLink(){
                if(!this.link.length) {
                    toastr.error('Поле не должно быть пустым'); return;
                }

                axios.post(`/printphotobook/file_uploading_link/${this.id}`, {
                    _token: this.csrf_token,
                    link: this.link
                }).then(({data}) => {
                    if(data.access) {
                        // Если ссылка не совпадает с маршрутом
                        // Меняем ссылку на соответствующую текущему состоянию
                        if(!this.isUrlMatchToRoute(data.url)) {
                            this.goTo(
                                this.getRoute(data.url)
                            );
                        }
                        /**
                         * @type {*|number}
                         */
                        this.individualPagesNum = data.individualPagesNum;
                        /**
                         * @type {*|number}
                         */
                        this.filesLoaded = data.filesLoaded;
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
                         * @type {array}
                         * */
                        this.updateBooks(data);
                        /**
                         * @type {Object}
                         */
                        this.updateButtons(data);
                        /**
                         * @type {Array}
                         */
                        this.updateTransitions(data);
                        /**
                         * @type {number}
                         */
                        this.setAmount(data);
                        /**
                         * @type {string|null}
                         */
                        this.updateClientPlace(data);
                        /**
                         * обновляем аккоредон
                         */
                        this.updateAccordionItems(data);
                    } else {
                        this.goBack();
                    }
                })
            }
        },

        mounted(){
            if(this.isFileUploadingPage()) {
                Nova.dealStore.dispatch('changeSelected', true);

                this.setFileUploadingPageData();
            }
        },

        computed: {
            showDots(){
                return Nova.dealStore.getters.showDots;
            },

            showProgress(){
                return Nova.dealStore.getters.showProgress;
            }
        },

        components: {
            Errors,
            Buttons,
            Accordion,
            AccordionItem
        }
    }
</script>

<style scoped lang="scss">
    .image-container {
        float: left;
        height: 110px;
    }

    .file-container {
        position: relative;

        .file-container-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 100;
            background-color: rgba(0,0,0,.5);
        }

        .file-container__label {
            z-index: 101;
        }
    }

    #buttons {
        z-index: 110;
    }

    .shadow-inner {
        &:focus {
            box-shadow: inset 0 0 6px rgba(0,0,0,.2);
            outline: none;
        }
    }
</style>
