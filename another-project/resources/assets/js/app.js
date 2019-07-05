import Vue from 'vue';
import axios from 'axios';

require('./bootstrap');

Object.defineProperty(Vue.prototype, '$http', {
    get(){
        return axios;
    }
});

import './components.js';

window.onload = () => {
    const app = new Vue({
        el: '#app'
    });
};
