import axios from 'axios';

export default {
    /**
     * редиректит пользователя на переданный маршрут, если текущее состояние того позволяет
     * @param route
     * @param id
     */
    goTo({route, id}){
        if(id){
            this.$router.push({name: route, params: {id}});
        } else {
            this.$router.push({name: route});
        }
    },
    /**
     * Возвращает имя маршрута, соответствующего ссылке
     * Если ссылка не совпадает - в любом случае вернет маршрут dashboard
     * Который ведет на главную страницу
     * @param url
     * @return {string}
     */
    getRoute(url){
        if(url.match(/^\/users\/$/)) {
            return 'users';
        } else {
            return 'dashboard';
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
     * Возвращает пользователя на страницу списка пользователей
     */
    goBack(){
        this.$router.push({name: 'users'});
    },
    /**
     * Возвращает пользователя на главную страницу
     */
    goDashboard(){
        this.$router.push({name: 'dashboard'});
    },

    goUsers(){
        this.goTo({route: 'users-information'});
    }
}
