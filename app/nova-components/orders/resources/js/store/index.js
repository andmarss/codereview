export default {
    state: {
        selected: false,
        transitions: [],
        errors: [],
        // Кнопки
        forwardButton: null,
        backwardButton: null,
        deleteButton: null,
        // overlay
        showOverlay: false,
        overlayContent: '',
        showDots: true,
        showProgress: false,
        progressContent: '',
        // alert
        alertType: '',
        alertText: ''

    },
    mutations: {
        setSelected(state, {type, selected}){
            state[type] = selected;
        },
        /**
         * Обновляет массив transition'ов
         * @param state
         * @param type
         * @param transitions
         */
        setTransition(state, {type, transitions}){
            state[type] = transitions;
        },
        /**
         * Обновляет массив ошибок
         * @param state
         * @param type
         * @param errors
         */
        setErrors(state, {type, errors}){
            state[type] = errors;
        },
        /**
         * Устанавливает кнопку перехода на следующий шаг
         * @param state
         * @param type
         * @param button
         */
        setForwardButton(state, {type, button}){
            state[type] = button;
        },
        /**
         * Устанавливает кнопку перехода на предыдущий шаг
         * @param state
         * @param type
         * @param button
         */
        setBackwardButton(state, {type, button}){
            state[type] = button;
        },
        /**
         * Устанавливает кнопку удаления сделки
         * @param state
         * @param type
         * @param button
         */
        setDeleteButton(state, {type, button}){
            state[type] = button;
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
        /**
         * Тип алерта
         * @param state
         * @param type
         * @param data
         */
        setAlertType(state, {type, data}){
            state[type] = data;
        },
        /**
         * Текстовый контент алерта
         * @param state
         * @param type
         * @param content
         */
        setAlertContent(state, {type, content}){
            state[type] = content;
        },
    },
    actions: {
        /**
         * Вызывает setSelected для указания, что пользователь выбрал
         * создание или редактирование сделки
         * @param commit
         * @param selected
         * @return {Promise<void>}
         */
        async changeSelected({commit}, selected){
            commit('setSelected', {type: 'selected', selected});
        },
        /**
         * Вызывает setTransition
         * @param commit
         * @param transitions
         * @return {Promise<void>}
         */
        async updateTransitions({commit}, transitions){
            commit('setTransition', {type: 'transitions', transitions});
        },
        /**
         * Вызывает setErrors
         * @param commit
         * @param errors
         * @return {Promise<void>}
         */
        async updateErrors({commit}, errors){
            commit('setErrors', {type: 'errors', errors});
        },
        /**
         * вызывает setForwardButton
         * @param commit
         * @param button
         * @return {Promise<void>}
         */
        async updateForwardButton({commit}, button){
            commit('setForwardButton', {type: 'forwardButton', button});
        },
        /**
         * Вызывает setBackwardButton
         * @param commit
         * @param button
         * @return {Promise<void>}
         */
        async updateBackwardButton({commit}, button){
            commit('setBackwardButton', {type: 'backwardButton', button});
        },
        /**
         * Вызывает setDeleteButton
         * @param commit
         * @param button
         * @return {Promise<void>}
         */
        async updateDeleteButton({commit}, button){
            commit('setDeleteButton', {type: 'deleteButton', button});
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
        },
        /**
         * Вызывает setAlertType
         * @param commit
         * @param data
         * @return {Promise<void>}
         */
        async updateAlertType({commit}, data){
            commit('setAlertType', {type: 'alertType', data});
        },
        /**
         * Вызывает setAlertContent
         * @param commit
         * @param content
         * @return {Promise<void>}
         */
        async updateAlertContent({commit}, content){
            commit('setAlertContent', {type: 'alertText', content});
        },
        /**
         * сбрасывает значение alertType
         * @param commit
         * @return {Promise<void>}
         */
        async resetAlertType({commit}){
            commit('setAlertType', {type: 'alertType', data: ''});
        },
        /**
         * сбрасывает значение alertText
         * @param commit
         * @return {Promise<void>}
         */
        async resetAlertContent({commit}){
            commit('setAlertContent', {type: 'alertText', content: ''});
        },
    },
    getters: {
        /**
         * Вовзращает текущее состояние заказа
         * @param store
         * @return {*}
         */
        isSelected(store){
            return store.selected;
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
         * Возвращает массив ошибок
         * @param store
         * @return {*}
         */
        errors(store){
            return store.errors;
        },
        /**
         * Возвращает кнопку перехода на след. состояние
         * @param store
         * @return {default.getters.forwardButton|(function(*))|null|default.computed.forwardButton|forwardButton}
         */
        forwardButton(store){
            return store.forwardButton;
        },
        /**
         * Возвращает кнопку перехода на пред. состояние
         * @param store
         * @return {default.getters.backwardButton|(function(*))|null|default.computed.backwardButton|backwardButton}
         */
        backwardButton(store){
            return store.backwardButton;
        },
        /**
         * Возвращает кнопку удаления заказа
         * @param store
         * @return {default.getters.deleteButton|(function(*))|null|default.computed.deleteButton|deleteButton}
         */
        deleteButton(store){
            return store.deleteButton;
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
        },
        /**
         * Возвращает тип алерта
         * @param store
         * @return {default.getters.alertType|(function(*))|string}
         */
        alertType(store){
            return store.alertType;
        },
        /**
         * Возвращает текстовый контент алерта
         * @param store
         * @return {default.getters.alertText|(function(*))|string}
         */
        alertText(store){
            return store.alertText;
        }
    }
}
