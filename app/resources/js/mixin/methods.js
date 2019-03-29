export default {
    methods: {
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
        }
    }
}
