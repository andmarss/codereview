export default {
    state: {
        usersPage: 1
    },
    mutations: {
        /**
         * Устанавливает номер страницы таблицы
         * @param state
         * @param type
         * @param page
         */
        setUsersPage(state, {type, page}){
            state[type] = page;
        }
    },
    actions: {
        /**
         * Вызывает setUsersPage
         * @param commit
         * @param page
         * @return {Promise<void>}
         */
        async updateUsersPage({commit}, page){
            commit('setUsersPage', {type: 'usersPage', page});
        }
    },
    getters: {
        /**
         * возвращает номер страницы для таблицы пользователей
         * @param store
         * @return {default.getters.usersPage|(function(*))|default.computed.usersPage|number|usersPage}
         */
        usersPage(store){
            return store.usersPage;
        }
    }
}
