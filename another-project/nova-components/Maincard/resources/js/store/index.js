export default {
    state: {
        transitions: [],
        verified: false,
        // overlay
        showOverlay: false,
        overlayContent: '',
        showDots: true,
        showProgress: false,
        progressContent: '',

    },
    mutations: {
        /**
         * устанавливает, верифицирован пользователь или нет
         * @param state
         * @param type
         * @param verified
         */
        setVerified(state, {type, verified}){
            state[type] = verified;
        },
        /**
         * Обновляет текущее состояние пользователя
         * @param state
         * @param type
         * @param transitions
         */
        setTransitions(state, {type, transitions}){
            state[type] = transitions;
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
        },
    },
    actions: {
        /**
         * Вызывает setVerified
         * @param commit
         * @param verified
         * @return {Promise<void>}
         */
        async updateVerified({commit}, verified){
            commit('setVerified', {type: 'verified', verified});
        },
        /**
         * Вызывает setTransition
         * @param commit
         * @param transition
         * @return {Promise<void>}
         */
        async updateTransitions({commit}, transitions){
            commit('setTransitions', {type: 'transitions', transitions});
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
        async updateProgressContent({commit}, content) {
            commit('setProgressContent', {type: 'progressContent', content});
        }
    },
    getters: {
        /**
         * Верифицирован пользователь или нет
         * @param store
         * @return {*}
         */
        verified(store){
            return store.verified;
        },
        /**
         * Возвращает массив transition'ов
         * @param store
         * @return {*}
         */
        transitions(store){
            return store.transitions;
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
