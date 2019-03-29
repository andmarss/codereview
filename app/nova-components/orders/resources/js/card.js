import Vuex from 'vuex';
import store from './store';
import VueMask from 'v-mask';

Nova.booting((Vue, router) => {
    Vue.use(VueMask);

    Vue.component('userorders', require('./components/Card'));

    Vue.component('CustomConfirmModal', require('./components/modal/Index'));

    Vue.component('overlay', require('./components/overlay/Index'));

    router.addRoutes([
        {
            name: 'editing',
            path: '/printphotobook/editing/:id',
            props: route => {
                return {
                    id: route.params.id,
                }
            },
            component: require('./components/editing/Index')
        },
        {
            name: 'file-upload',
            path: '/printphotobook/file_uploading/:id',
            props: route => {
                return {
                    id: route.params.id,
                }
            },
            component: require('./components/file_uploading/Index')
        },
        {
            name: 'pay',
            path: '/printphotobook/paying/:id',
            props: route => {
                return {
                    id: route.params.id,
                }
            },
            component: require('./components/paying/Index')
        },
        {
            name: 'delivery',
            path: '/printphotobook/delivery_data_editing/:id',
            props: route => {
                return {
                    id: route.params.id,
                }
            },
            component: require('./components/delivery_data_editing/Index')
        },
        {
            name: 'info',
            path: '/printphotobook/info/:id',
            props: route => {
                return {
                    id: route.params.id,
                }
            },
            component: require('./components/info/Index')
        }
    ]);

    Vue.use(Vuex);
    // Добавляем хранилище состояния для компонентов, относящихся к Userorders
    Nova.dealStore = new Vuex.Store(store);
});
