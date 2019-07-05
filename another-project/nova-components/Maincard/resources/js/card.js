import Vuex from 'vuex';
import store from './store';

Nova.booting((Vue, router) => {
    Vue.component('overlay', require('./components/overlay/Index'));

    Vue.component('Modal', require('./components/modal/Index'));

    Vue.component('maincard', require('./components/Card'));

    Vue.use(Vuex);

    Nova.mainStore = new Vuex.Store(store);
});
