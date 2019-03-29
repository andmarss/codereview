export default {
    state: {
        // таблицы в админке
        dealsPage: 1,
        usersPage: 1,
        paymentsPage: 1,
        //баланс
        balance: 0,
        // overlay
        showOverlay: false,
        overlayContent: '',
        showDots: true,
        showProgress: false,
        progressContent: '',
    },
    mutations: {
        /**
         * Номер страницы таблицы сделок
         * @param state
         * @param type
         * @param page
         */
        setDealsPage(state, {type, page}){
            state[type] = page;
        },
        /**
         * Номер страницы таблицы оплат пользователя
         * @param state
         * @param type
         * @param page
         */
        setPaymentsPage(state, {type, page}){
            state[type] = page;
        },
        /**
         * Устанавливает номер страницы таблицы
         * @param state
         * @param type
         * @param page
         */
        setUsersPage(state, {type, page}){
            state[type] = page;
        },
        /**
         * Изменят баланс пользователя
         * @param state
         * @param type
         * @param balance
         */
        changeBalance(state, {type, balance}){
            state[type] = balance;
        },
        /**
         * Показывать ли overlay
         * @param state
         * @param type
         * @param show
         */
        setShowOverlay(state, {type, show}){
            state[type] = show;
        },
        /**
         * Текстовый контент оверлея
         * @param state
         * @param type
         * @param content
         */
        setOverlayContent(state, {type, content}){
            state[type] = content;
        },
        /**
         * Показывать ли ... в процессе загрузки
         * @param state
         * @param type
         * @param show
         */
        setShowDots(state, {type, show}){
            state[type] = show;
        },
        /**
         * Показывать ли текстовый прогресс загрузки
         * @param state
         * @param type
         * @param show
         */
        setShowProgress(state, {type, show}){
            state[type] = show;
        },
        /**
         * Текстовый контент прогресса
         * @param state
         * @param type
         * @param content
         */
        setProgressContent(state, {type, content}){
            state[type] = content;
        }
    },
    actions: {
        /**
         * Вызывает setDealsPage
         * @param commit
         * @param content
         * @return {Promise<void>}
         */
        async updateDealsPage({commit}, page){
            commit('setDealsPage', {type: 'dealsPage', page});
        },
        /**
         * Вызывает setPaymentsPage
         * @param commit
         * @param content
         * @return {Promise<void>}
         */
        async updatePaymentsPage({commit}, page){
            commit('setPaymentsPage', {type: 'paymentsPage', page});
        },
        /**
         * Вызывает setUsersPage
         * @param commit
         * @param page
         * @return {Promise<void>}
         */
        async updateUsersPage({commit}, page){
            commit('setUsersPage', {type: 'usersPage', page});
        },
        /**
         * Вызывает ф-ю changeBalance
         * @param commit
         * @param balance
         * @return {Promise<void>}
         */
        async updateBalance({commit}, balance){
            commit('changeBalance', {type: 'balance', balance});
        },
        /**
         * Вызывает setShowOverlay
         * @param commit
         * @param show
         * @return {Promise<void>}
         */
        async updateShowOverlay({commit}, show){
            commit('setShowOverlay', {type: 'showOverlay', show});
        },
        /**
         * Вызывает setOverlayContent
         * @param commit
         * @param content
         * @return {Promise<void>}
         */
        async updateOverlayContent({commit}, content){
            commit('setOverlayContent', {type: 'overlayContent', content});
        },
        /**
         * Вызывает setShowDots
         * @param commit
         * @param content
         * @return {Promise<void>}
         */
        async updateShowDots({commit}, show){
            commit('setShowDots', {type: 'showDots', show});
        },
        /**
         * Вызывает setShowProgress
         * @param commit
         * @param show
         * @return {Promise<void>}
         */
        async updateShowProgress({commit}, show){
            commit('setShowProgress', {type: 'showProgress', show});
        },
        /**
         * Вызывает setProgressContent
         * @param commit
         * @param content
         * @return {Promise<void>}
         */
        async updateProgressContent({commit}, content){
            commit('setProgressContent', {type: 'progressContent', content});
        }
    },
    getters: {
        /**
         * возвращает номер страницы для таблицы сделок
         * @param store
         * @return {default.getters.dealsPage|(function(*))|default.computed.dealsPage|number|dealsPage}
         */
        dealsPage(store){
            return store.dealsPage;
        },
        /**
         * возвращает номер страницы для таблицы пользователей
         * @param store
         * @return {default.getters.usersPage|(function(*))|default.computed.usersPage|number|usersPage}
         */
        usersPage(store){
            return store.usersPage;
        },
        /**
         * возвращает номер страницы для таблицы оплат пользователя
         * @param store
         * @return {default.getters.usersPage|(function(*))|default.computed.usersPage|number|usersPage}
         */
        paymentsPage(store){
            return store.paymentsPage;
        },
        /**
         * Возвращает баланс пользователя
         * @param store
         * @return {default.getters.balance|(function(*))|number|*|default.computed.balance|balance}
         */
        balance(store){
            return store.balance;
        },
        /**
         * Возвращает булево значение отображения оверлея
         * @param store
         * @return {default.getters.showOverlay|(function(*))|boolean}
         */
        showOverlay(store){
            return store.showOverlay;
        },
        /**
         * Возвращает текстовое значение контента в оверлее
         * @param store
         * @return {default.getters.overlayContent|(function(*))|string}
         */
        overlayContent(store){
            return store.overlayContent;
        },
        /**
         * Возвращает булево значение отображения ... в оверлее
         * @param store
         * @return {default.getters.showDots|(function(*))|boolean|default.computed.showDots}
         */
        showDots(store){
            return store.showDots;
        },
        /**
         * Возвращает булево значение отображения прогресса загрузки в оверлее
         * @param store
         * @return {default.getters.showProgress|(function(*))|boolean|default.computed.showProgress}
         */
        showProgress(store){
            return store.showProgress;
        },
        /**
         * Возвращает текстовое значение контента прогресса загрузки в оверлее
         * @param store
         * @return {default.getters.progressContent|(function(*))|string|default.computed.progressContent}
         */
        progressContent(store){
            return store.progressContent;
        }
    }
}
