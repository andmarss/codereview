import Vuex from 'vuex';
import store from './store';

Nova.booting((Vue, router) => {
    Vue.component('Table', require('./components/Table'));

    Vue.component('Pagination', require('./components/Pagination'));

    Vue.component('ImageModal', require('./components/ImageModal'));

    router.addRoutes([
        {
            name: 'users',
            path: '/users',
            component: require('./components/Tool'),
        },
        {
            name: 'view-user',
            path: '/user/:id',
            component: require('./components/User'),
            props: route => {
                return {
                    id: route.params.id,
                }
            }
        }
    ]);

    Vue.use(Vuex);

    Nova.usersStore = new Vuex.Store(store);
});
