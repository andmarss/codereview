Nova.booting((Vue, router) => {
    Vue.component('TextField', require('./components/TextField/TextField'));

    router.addRoutes([
        {
            name: 'profile',
            path: '/profile',
            component: require('./components/Tool'),
        },
    ])
});
