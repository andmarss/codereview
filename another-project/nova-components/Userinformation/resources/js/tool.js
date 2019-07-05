Nova.booting((Vue, router) => {
    Vue.component('TextArea', require('./components/TextArea'));

    Vue.component('Rate', require('./components/Rate'));

    router.addRoutes([
        {
            name: 'user-information',
            path: '/information/specialist/:id',
            component: require('./components/Tool'),
            props: route => {
                return {
                    id: route.params.id,
                }
            }
        },
        {
            name: 'users-information',
            path: '/information/specialists',
            component: require('./components/UsersList')
        }
    ])
});
