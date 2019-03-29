import axios from 'axios';
import _ from 'lodash';

export default {
    /**
     * возвращает пользователя на главную страницу ЛК
     */
    goBack(){
        Nova.dealStore.dispatch('changeSelected', false);

        this.$router.push({name: 'dashboard'});
    },

    /**
     * Может ли пользователь перейти на следующий шаг
     * Параметр ignore служит для игнорирования наличия ошибок
     * Т.к. ошибки не для всех транзишнов обязательны, что бы туда перейти
     * @param transition
     * @param ignore
     * @return {boolean}
     */
    canGo(transition, {ignore = false} = {}){
        return ignore ?
            this.transitions.includes(transition) :
            this.errors.length === 0 && this.transitions.includes(transition);
    },

    /**
     * редиректит пользователя на переданный маршрут, если текущее состояние того позволяет
     * @param route
     */
    goTo(route){
        this.$router.push({name: route, params: {id: this.id}});
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
     * Функия переводит сделку в указанное состояние
     * После чего редиректит пользователя на возвращаемую с сервера ссылку
     * @param transition
     * @param alert
     */
    goTransition(transition, {alert = null} = {}){

        if(transition === 'delete') {
            this.working = true;
        }

        if(alert && alert.type && alert.text) {
            Nova.dealStore.dispatch('updateAlertType', alert.type);
            Nova.dealStore.dispatch('updateAlertContent', alert.text);
        }

        if(this.transitions.includes(transition)) {
            axios.post(`/deal/transition/${this.id}`, {
                _token: this.csrf_token,
                transition
            }).then(({data}) => {
                if(data.access) {
                    if(window.location.pathname !== data.url) {
                        this.goTo(
                            this.getRoute(data.url)
                        );
                    }

                    if(this.alertType && this.alertText) {
                        toastr[this.alertType](this.alertText);

                        Nova.dealStore.dispatch('resetAlertType');
                        Nova.dealStore.dispatch('resetAlertContent');
                    }

                    this.working = false;
                }

                this.working = false;
            });
        } else {
            axios.get(`/api/photobook/info/${this.id}`).then(({data}) => {
                if(data.access) {
                    this.goTo(
                        this.getRoute( data.url )
                    );
                } else {
                    this.goBack();
                }

                this.working = false;
            })
        }
    },

    /**
     * Возвращает строковое значение типа данных
     * Отличается от typeof более точным указанием типа
     * getType([]) => 'array'
     * typeof [] => 'object'
     * getType(new Promise(function(){}, function(){})) => 'promise'
     * typeof (new Promise(function(){}, function(){})) => 'object'
     * @param type
     * @return {string}
     */
    getType(type){
        return Object.prototype.toString.call(type).slice(8).slice(0,-1).toLowerCase();
    },
    /**
     * Проверяет, есть ли ключ key в объекте object
     * @param object
     * @param key
     * @return {boolean}
     */
    has(object, key){
        return _.has(object, key);
    },
    /**
     * Размещает компонент ошибок над компонентом кнопок
     */
    placeErrorsComponent(){
        setTimeout(() => {
            this.buttonsEl = document.querySelector('#buttons');
            this.errorsEl = document.querySelector('#errors');

            if(this.errorsEl && this.buttonsEl) {
                this.errorsEl.style.marginBottom = `${this.buttonsEl.offsetHeight}px`;
            }
        })
    },
    /**
     * Запускает таймер, который каждые 10 секунд отправляет запросы на сервер
     * И обновляет список разворотов
     * @param {function} then
     */
    setUpdateBooksTimeout(then){
        // Указываем, что по умолчанию обновление списка книг не нужно
        this.needUpdateBooks = false;

        axios.get(`/get-books/${this.id}`).then(({data}) => {
            if(data.access) {
                if(data.books.length > 0) {
                    this.books = data.books.map(book => {
                        if (book.files.length > 0) {
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

                        return book;
                    });
                } else {
                    this.books = [];
                }

                // если передана функция
                // вызываем её
                if(then && this.getType(then) === 'function') {
                    then();
                }

                // Если требуется обновление списка книг
                // Обновляем его
                if(this.needUpdateBooks) {
                    // Очищаем старый timeout
                    this.clearBooksTimeout();

                    this.timeout = setTimeout(() => {
                        this.setUpdateBooksTimeout(then);
                    }, 10000);
                } else { // Иначе - просто очищаем старый таймаут
                    this.clearBooksTimeout();
                }
            }
        });
    },
    /**
     * Очищает ссылку на Timeout,
     * после чего устанавливает timeout равный null
     */
    clearBooksTimeout(){
        if(this.timeout){
            clearTimeout(this.timeout);
        }

        this.timeout = null;
    },

    /**
     * Обновляет ошибки
     * @param data
     * @param then {function|null}
     **/
    updateErrors(data, then = null){
        if(data.errors.length > 0) {
            let errors = [];

            data.errors.forEach((error, i) => {
                errors.push({
                    id: i+1,
                    value: error,
                    active: true
                });
            });

            Nova.dealStore.dispatch('updateErrors', errors);

        } else {
            Nova.dealStore.dispatch('updateErrors', []);
        }

        if(then && this.getType(then) === 'function') {
            then(data);
        }
    },
    /**
     * Обновляет состояние транзишнов
     * @param data
     */
    updateTransitions(data){
        if(data.transitions && data.transitions.length > 0) {
            Nova.dealStore.dispatch('updateTransitions', data.transitions);
        } else {
            Nova.dealStore.dispatch('updateTransitions', []);
        }
    },

    /**
     * Устанавливает сумму заказа без скидки
     * сумму со скидкой
     * и размер самой скидки
     * @param data
     */
    setAmount(data){
        if(data.deal && this.getType(data.deal.src_cost)  !== 'undefined' || this.getType(data.deal.src_cost)  !== 'null') {
            /**
             * сумма заказа без скидки
             * @var total {number}
             **/
            this.src_cost = data.deal.src_cost;
        }

        if(data.deal && this.getType(data.deal.cost)  !== 'undefined' || this.getType(data.deal.cost)  !== 'null') {
            /**
             * сумма со скидкой
             * @var total {number}
             **/
            this.cost = data.deal.cost;
        }

        if(data.deal && this.getType(data.deal.discount)  !== 'undefined' || this.getType(data.deal.discount)  !== 'null') {
            /**
             * размер скидки
             * @var total {number}
             **/
            this.discount = data.deal.discount;
        }
    },
    /**
     * Возвращает элемент для аккордеона
     * @param data
     */
    convertDealInfoItem(data){
        let item = {};

        item.title = 'Информация о заказе';
        item.content = [];
        item.tag = 'p';
        item.active = false;

        Object.keys(data.info).forEach(key => {
            let content = {};

            content[key] = data.info[key];

            item.content.push(content);
        });

        return item;
    },
    /**
     * Возвращает элемент для аккордеона
     * @param data
     */
    convertDealLayoutsItem(data){
        let item = {};

        item.title = 'Макеты';
        item.content = [];
        item.tag = 'div';
        item.active = false;

        data.books.forEach((book, index) => {
            if(book.files && book.files.length > 0) {
                book.files = book.files.map(file => {
                    file.id = `img_${file.name}`;

                    return file;
                });
            }
            // номер книги
            book.number = (index+1);

            item.content.push(book);
        });

        return item;
    },
    /**
     * Функция-помощник для кастомного добавления элементов для аккордеона
     * @param data
     * @param props
     */
    addAccordeonItem(data, props = {}){
        let item = {};

        item.title = props.title ? props.title : 'Макеты';
        item.content = props.content !== undefined ? props.content : [];
        item.role = props.role ? props.role : 'div';
        item.active = props.active ? props.active : false;

        if(props.callback && this.getType(props.callback) === 'function') {
            item = props.callback(data, item);
        }

        return item;
    },
    /**
     * @param data
     */
    updateClientPlace(data){
        /**
         * @type {string|null}
         */
        this.client_place = data.client_place;

        if(this.client_place === null) {
            this.goTo( {route: this.getRoute( data.url ), id: this.id} );
        }
    },
    /**
     *
     * @param {string} stringOrNumber
     * @return {string}
     */
    upperCaseFirst(stringOrNumber){
        if(stringOrNumber && this.getType(stringOrNumber) === 'string') {
            return `${stringOrNumber.slice(0,1).toUpperCase()}${stringOrNumber.slice(1)}`;
        } else if (stringOrNumber && this.getType(stringOrNumber) === 'number') {
            return stringOrNumber;
        }
    }
}
