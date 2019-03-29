import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import storeObj from './store';
import routes from './routes';
import axios from 'axios';
import DropdownTrigger from '../../nova/resources/js/components/DropdownTrigger';
import DropdownMenu from '../../nova/resources/js/components/DropdownMenu';
import Dropdown from '../../nova/resources/js/components/Dropdown';
import $ from 'jquery';

window.jQuery = $;
window.$ = $;

Vue.$http = axios;

Object.defineProperty(Vue.prototype, '$http', {
    get(){
        return axios;
    }
});

Vue.use(Vuex);

let store = new Vuex.Store(storeObj);

Vue.use(VueRouter);

let router = new VueRouter({
    routes,
    mode: 'history'
});

import './components.js';

window.onload = () => {
    window.custom = new Vue({
        el: '#admin',
        store,
        router
    });
};
